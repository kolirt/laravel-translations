<?php

namespace Kolirt\Translations\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'lang',
        'key',

        'string',
        'text',
        'mediumText',
        'longText',
        'smallInteger',
        'tinyInteger',
        'integer',
        'mediumInteger',
        'bigInteger',
        'decimal',
        'boolean',
        'date',
        'dateTime',
        'timestamp',

        'translation_id',
        'translation_type'
    ];

    const COLUMN_TYPE = [
        'string',
        'text',
        'mediumText',
        'longText',
        'smallInteger',
        'tinyInteger',
        'integer',
        'mediumInteger',
        'bigInteger',
        'decimal',
        'boolean',
        'date',
        'dateTime',
        'timestamp'
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('translations.connection'));
        parent::__construct($attributes);
    }

}
