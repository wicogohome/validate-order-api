<?php

namespace App\Transformers;

interface TransformerInterface
{
    public function transform(array $data): array;
}
