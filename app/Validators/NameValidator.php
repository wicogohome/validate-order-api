<?php

namespace App\Validators;

use App\Exceptions\ValidatorException;

class NameValidator implements ValidatorInterface
{
    public function validate(array $data): bool
    {
        if (! isset($data['name']) || ! is_string($data['name'])) {
            throw new ValidatorException('Name is required and must be a string');
        }

        $name = $data['name'];

        $this->validateAllEngChars($name);
        $this->validateCapitalization($name);

        return true;
    }

    private function validateAllEngChars(string $name): void
    {
        if (! ctype_alpha($name)) {
            throw new ValidatorException('Name contains non-English characters');
        }
    }

    private function validateCapitalization(string $name): void
    {
        if ($name !== ucfirst($name)) {
            throw new ValidatorException('Name is not capitalized');
        }
    }
}
