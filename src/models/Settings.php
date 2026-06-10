<?php

namespace furbo\craftschedule\models;

use craft\base\Model;

class Settings extends Model
{
    public string $phpPath = '';

    public function rules(): array
    {
        return [
            ['phpPath', 'string'],
        ];
    }
}
