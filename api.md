# API串接文件

## Base URL

`http://localhost`

## Endpoints

### `POST /api/orders`

提供訂單格式 檢查 與 轉換

※可用Postman import[範例檔](/example/Validate-order-api.postman_collection.json)測試

### Headers
- `Content-Type:application/json`  

- `Accept:application/json`

### Parameters

- `id` (required): string，暫定長度不可超過50字。
- `name` (required): string，暫定長度不可超過50字。需為全英文（可包含半形空白），且每個單字首字為大寫。
- `address` (required): array，需包含以下欄位：
  - `city` (required): string，暫定長度不可超過50字。
  - `district` (required): string，暫定長度不可超過50字。
  - `street` (required): string，暫定長度不可超過50字。
- `price` (required): numerice，該欄位會搭配currency進行轉換，轉換成TWD後，金額不可大於2000。
- `currency` (required): string，長度僅可為3。須為TWD或USD


### Response
回傳一個JSON object，結構如下：
- `status`: 狀態編號
- `data`: 轉換後的訂單資料, 包含以下properties:
    - `id`: string
    - `name`: string
    - `address` : string
      - `city`: string
      - `district` : string
      - `street` : string
    - `price` : numerice，若原貨幣為USD，其值會被乘上匯率後返回。
    - `currency` : string，若原貨幣為USD，會連同金額被修改為TWD。
### Example

Request:

```json
POST /api/orders

// data
{
    "id": "A0000001",
    "name": "Melody Holiday Inn",
    "address": {
        "city": "taipei-city",
        "district": "da-an-district",
        "street": "fuxing-south-road"
    },
    "price": 10,
    "currency": "USD"
}
```

Response:

```json
{
    "status": 200,
    "data": {
        {
            "id": "A0000001",
            "name": "Melody Holiday Inn",
            "address": {
                "city": "taipei-city",
                "district": "da-an-district",
                "street": "fuxing-south-road"
            },
            "price": 310, // 經過轉換
            "currency": "TWD" // 經過轉換
        }
    }
}

```

## Errors

本API可能有以下錯誤狀態：

- `422 FormRequest Error`: （FormRequest）輸入資料不符合基本格式，或有缺少。
- `400 Invalid Error`: 輸入的資料不符合指定規則.

### Response

400會回傳一個JSON object，結構如下：

- `status`: 狀態編號
- `message`: 詳細錯誤資訊

### Example
```json
{
    "status": 400,
    "message": "Name contains non-English characters" // name包含非英文字元
}

```

