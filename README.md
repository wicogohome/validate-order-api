# 資料庫測驗

1.  
```SQL
   SELECT orders.bnb_id, bnbs.name AS bnb_name , SUM(orders.amount) AS may_amount
   FROM orders
   LEFT JOIN bnbs ON orders.bnb_id = bnbs.id
   WHERE 
	   ( DATE(orders.created_at) BETWEEN '2023-05-01' AND '2023-05-31' )  AND
	   ( currency = 'TWD' )
	GROUP BY orders.bnb_id
	ORDER BY may_amount DESC
	LIMIT 10 ;
```
2.
如果是用Laravel ORM去寫，會先確認DB::getQueryLog()的SQL情況，如果與預期不符，像是有N+1就先修正。  
並在調整前記錄執行時間，確保調整有用。

先確認資料表都有主索引，再看看有無需要其他索引，但除非執行速度上必要，不然不會多加。  

如要添加，則會嘗試對orders下複合索引(created_at, currency, bnb_id, amount)，並使用EXPLAIN來確認真的有用。  
因為database可以直接在索引中取得所有所需資料，也可以加速排序，理論上應能提升查詢速度，但代價是會增加更新table的開銷，所以非必要還是不想下索引。

接著調整SQL，移除DATE等function，避免它阻止使用索引。  
看看能不能減少JOIN，我會用LEFT JOIN是希望也能撈出異常資料（沒有bnb卻有order），避免長期忽略有問題的資料，不過若需要最快，那還是會改成INNER JOIN。

也會使用CTE，先對orders處理出符合條件的五月訂單並計算總金額，最後才進行JOIN和排序，如此也能提升SQL的可讀性。

綜合以上的修改結果就會是：
```sql
    WITH may_orders AS (
        SELECT bnb_id, SUM(amount) AS may_amount
        FROM orders
        WHERE 
            created_at >= '2023-05-01' AND 
            created_at < '2023-06-01' AND 
            currency = 'TWD'
        GROUP BY bnb_id
    )
    SELECT may_orders.bnb_id, bnbs.name AS bnb_name, may_orders.may_amount
    FROM may_orders 
    INNER JOIN bnbs ON may_orders.bnb_id = bnbs.id
    ORDER BY may_orders.may_amount DESC
    LIMIT 10;
```

再積極一點就是把常用資料特別組合成一張查詢用的表，或是在離峰時間用排程執行SQL，再Email報表（我想這是要做報表吧？）




# API測驗

## About

這是一個使用Laravel製作，具有提供訂單格式檢查與轉換的 API，詳細請見[API文件](api.md)

## 安裝方法（請確保使用非root）

※請確保使用非root user，或是在安裝後自行修改擁有者(`chown -R (user):(group) validate-order-api`)，以確保php container有足夠權限

※請確保同時 沒有其他使用localhost 80port 的服務

```shell
$ git clone git@github.com:wicogohome/validate-order-api.git

$ cd validate-order-api

# Makefile會幫你處理.env的複製、docker compose up -d、安裝composer依賴、產生env key
$ make init 

# 調整成適當權限
$ chmod -R 775 storage bootstrap/cache

# (optional)後續可直接用以下指令開啟docker compose
$ docker compose -f docker-compose.local.yml up 
```
※docker-compose.yml是sail開發用的，請避免使用


完成後即可對localhost:80施打，詳細請見[API文件](api.md)  

> 為避免啟用不必要的container，本docker compose並不包含mysql。因此直接進入`http://localhost/`，畫面出現mysql連線錯誤是正常的


## CI/CD
[![Laravel](https://github.com/wicogohome/validate-order-api/actions/workflows/laravel.yml/badge.svg?branch=master)](https://github.com/wicogohome/validate-order-api/actions/workflows/laravel.yml)

會自動執行所有測試


## 內容說明

基本架構為使用Service & Repository 和 不完全的Strategy Pattern。  
- OrderService來處理Order相關的邏輯
- CurrencyRepository處理可能的貨幣與匯率資料取得
- Validator處理各欄位驗證
- Transformer處理資料轉換。

### 程式流程概述
1. 首先使用ValidateOrderRequest（FormRequest），來檢查訂單的必要欄位，以及是否為指定型態。
2. 進入OrderController後，使用OrderService的validateAndTransform來處理更細節的驗證邏輯與資料轉換。
3. OrderService注入`$validators`（NameValidator, PriceValidator, CurrencyValidator）和 `$transformers`（CurrencyTransformer）
4. 於validateAndTransform對`$validators`進行loop檢查，並使用`$transformers`逐一轉換資料。
5. 最終回傳轉換後的訂單資料。


### 設計模式
1. Service Pattern & Repository Pattern：用於將業務邏輯與（可能的）資料取得分開。  
   1. Service：  
       Service用於處理Order的驗證與轉換，避免放在Controller導致其肥大，也作為各Validator和Transformer的使用者，可在此處對驗證與轉換項目有基本認知。
   2. Repository：  
       我認為匯率換算和可用貨幣是會隨時間改變的資料，也可能根據要求使用動態匯率，或是改用存於資料庫的指定數字，可用貨幣也可能會隨時間增加。  

       因此，即便當前使用固定匯率與種類，我也先把資料取得抽到CurrencyRepository，若日後需要修改資料或更新取得方式，就可以在不影響業務邏輯的情況下進行，方便管理也提升可擴充性。

2. Strategy Pattern：  
OrderService裡注入了3個Validator，分別處理name, price, currency的驗證，因為更像是直接對資料進行逐個驗證，沒有根據不同情況選擇不同策略，所以較不典型。  
透過建立基本的ValidatorInterface，描述Validator應有的validate()，實際內容則由各Validator實作（=一系列的驗證策略），讓每個子Validator在輸入與輸出上達到一致，也能方便地增加對其他欄位的驗證而不改動主要結構。


### SOLID使用

#### 單一職責
1. 使用OrderService來處理Order相關的邏輯，不使用OrderValidationService這類是為了避免程式過度碎片化。
2. 並用CurrencyRepository處理可能的貨幣與匯率資料取得，也確保各處使用的貨幣與匯率資料一致
3. 每個欄位的驗證和轉換邏輯都在不同的class中，讓各class只專注於特定的驗證和轉換
4. 也拆出CurrencyService專門處理貨幣換算的邏輯，確保算法一致，也讓使用到的驗證和轉換不需要關注如何換算。

#### 開放封閉
OrderService使用Validator的方式為透過$validators和DI注入需要的Validator類別，如要增加對其他欄位的驗證，只需要新增類別繼承ValidatorInterface，並於OrderService注入，即可達成修改，而不需要更動OrderService的邏輯，也無法改動Validator的基本架構，故符合對擴充開放，對修改封閉的原則。

#### 里氏替換
凡繼承ValidatorInterface的子類，都可以互相替換而不影響正確性，因為都是Vailator，且有實作相同的validate()；TransformerInterface同理。


#### 介面隔離
1. 所有Validator都依賴於ValidatorInterface，interface也只包含Validator需要的validate()
2. OrderService也只依賴需要用到的Validator和Transformer，不會有用不到的功能。

#### 依賴反轉

不算有用到，只用了基本的依賴注入，減少在Service或各class中的耦合。
我覺得現階段功能還不需要真的去依賴介面來方便替換，想避免過度設計。


## CheckList

具體要求
- [x] endpoint: POST /api/orders (JSON)
- [x] FormRequest（基本檢查：必要欄位 & 指定型態(未說明，先當作string和numeric)）
- [x] service：處理訂單檢查格式與轉換的功能
- [x] loop：訂單檢查格式與轉換
- [x] Unit test（valid/invaild）：針對 所有情境
- [x] docker


設計原則
- [x] OOP: SOLID
- [x] Design pattern

額外
- [x] lint
- [x] 多餘的docker刪除
- [x] CI/CD & 上Badge
- [x] postman example
- [x] 安裝說明



