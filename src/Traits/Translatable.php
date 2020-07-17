<?php

namespace Kolirt\Translations\Traits;

use Illuminate\Database\Eloquent\Builder;
use Kolirt\Translations\Models\Translation;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Translatable
{

    private $translationsToSave = [];

    public static function bootTranslatable()
    {
        $class = new self;

        if (!empty($class->translatable) && config('translations.active', true) && !config('translations.disableGlobalScope', false)) {
            static::addGlobalScope('translatable', function (Builder $builder) use ($class) {
                $builder->addSelect(\DB::raw($class->getTable() . ".*"));

                foreach ($class->translatable as $column => $type) {
                    $builder->addSelect($class->generateQuery($class, $column, $type));
                }
            });
        }
    }

    public function scopeTranslatable($builder)
    {
        $builder->addSelect(\DB::raw($this->getTable() . ".*"));

        foreach ($this->translatable as $column => $type) {
            $builder->addSelect($this->generateQuery($this, $column, $type));
        }
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $attributes = $this->fillTranslations($attributes);
        return parent::fill($attributes);
    }

    /**
     * Prepare attributes before fill.
     *
     * @param array $attributes
     * @return array
     */
    public function fillTranslations(array $attributes)
    {
        foreach ($attributes as $column => $attribute) {
            if (isset($this->translatable[$column]) || in_array($column, $this->translatable ?? [])) {
                if (is_array($attribute)) {
                    foreach ((is_array($attribute) ? $attribute : []) as $lang => $value) {
                        if (in_array($lang, config('translations.locales', []))) {
                            $this->translationsToSave[$column][$lang] = $value;
                        }
                    }

                    $attributes[$column] = $attribute[config('app.fallback_locale')] ?? $this->translation($column, config('app.fallback_locale'));
                } else if (is_string($attribute)) {
                    foreach (config('translations.locales', []) as $lang) {
                        $this->translationsToSave[$column][$lang] = $attribute;
                    }

                    $attributes[$column] = $attribute;
                }
            }
        }

        return $attributes;
    }

    /**
     * Save the model to the database.
     *
     * @param array $options
     * @return bool
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        $result = parent::save($options);
        if ($result) {
            $this->saveTranslations($result);
        }
        return $result;
    }

    /**
     * Save translations.
     *
     * @throws \Exception
     */
    public function saveTranslations()
    {
        if (!empty($this->translationsToSave)) {
            try {
                \DB::beginTransaction();

                $translationsToSave = $this->translationsToSave;
                $this->translationsToSave = [];

                foreach ($translationsToSave as $column => $translations) {
                    foreach ($translations as $lang => $translation) {
                        $translation_model = Translation::firstOrNew([
                            'lang'             => $lang,
                            'key'              => $column,
                            'translation_id'   => $this->{$this->getKeyName()},
                            'translation_type' => $this->getTable()
                        ]);

                        $type = $this->translatable[$column] ?? Translation::COLUMN_TYPE[0];
                        $translation_model->$type = $translation;

                        $translation_model->save();
                    }

                    $this->setAttribute($column, $translations[app()->getLocale()] ?? null);
                }

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
        }
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        $translationsToDelete = $this->translations_all();

        $delete = true;
        if ($this->isSoftDeletes()) {
            if (!$this->trashed())
                $delete = false;
        }

        $result = parent::delete();

        if ($delete && $result) {
            $translationsToDelete->delete();
        }

        return $result;
    }

    /**
     * Get all translations by column name.
     *
     * @param $column
     * @return array|null
     */
    public function translations($column)
    {
        if (isset($this->translatable[$column]) || in_array($column, $this->translatable ?? [])) {
            $type = $this->translatable[$column] ?? Translation::COLUMN_TYPE[0];

            $result = [];
            $value = $this->translations_all->where('key', $column);

            foreach (config('translations.locales', []) as $lang) {
                $result[$lang] = $value->where('lang', $lang)->first()->{$type} ?? null;
            }

            return $result;
        }

        return null;
    }

    /**
     * Get translation for current language by column name.
     *
     * @param $column
     * @param null $lang
     * @return null
     */
    public function translation($column, $lang = null)
    {
        if (is_null($lang)) {
            $lang = app()->getLocale();
        }

        $type = $this->translatable[$column] ?? Translation::COLUMN_TYPE[0];

        $value = $this->translations_all->where('key', $column)->where('lang', $lang)->first();

        return $value->{$type} ?? null;
    }

    /**
     * Relation to Translation model.
     *
     * @return mixed
     */
    public function translations_all()
    {
        return $this->hasMany(Translation::class, 'translation_id', $this->getKeyName())
            ->where('translation_type', $this->getTable());
    }

    /**
     * Generate a query for select translation.
     *
     * @param $class
     * @param $column
     * @param $type
     * @return mixed
     */
    private function generateQuery($class, $column, $type)
    {
        if (is_int($column)) {
            $column = $type;
            $type = Translation::COLUMN_TYPE[0];
        }

        return \DB::raw("(SELECT `" . $type . "` FROM `translations` WHERE `translation_id`=`" . $class->getTable() . "`.`" . $class->getKeyName() . "` AND `translation_type`='" . $class->getTable() . "' AND `lang`='" . app()->getLocale() . "' AND `key`='" . $column . "') as `" . $column . "`");
    }

    /**
     * Check use soft delete.
     *
     * @return bool
     */
    public function isSoftDeletes()
    {
        return in_array(SoftDeletes::class, class_uses($this));
    }

}
