<?php

namespace Kolirt\Translations\Traits;

use Kolirt\Translations\Models\Translation;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Translatable
{

    protected $translationsToSave = [];

    public static function bootTranslatable()
    {
        $class = new self;

        if (($class->translatable ?? null) && config('translations.active', true)) {
            static::addGlobalScope('translatable', function($builder) use ($class){
                $builder->addSelect(\DB::raw($class->getTable() . ".*"));

                foreach (($class->translatable ?? []) as $key) {
                    $builder->addSelect($class->generateQuery($class, $key));
                }
            });
        }
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array $attributes
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
            if (in_array($column, $this->translatable ?? [])) {
                foreach (config('translations.locales', []) as $lang) {
                    $this->translationsToSave[$column][$lang] = $attribute[$lang] ?? null;
                }
                $attributes[$column] = $attribute[config('app.fallback_locale')] ?? null;
            }
        }

        return $attributes;
    }

    /**
     * Save the model to the database.
     *
     * @param  array $options
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
                            'lang' => $lang,
                            'key' => $column,
                            'translation_id' => $this->{$this->getKeyName()},
                            'translation_type' => self::class
                        ]);

                        $translation_model->value = $translation;

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
     * @param $key
     * @return array|null
     */
    public function translations($key)
    {
        if (!in_array($key, $this->translatable ?? [])) {
            return null;
        }

        $result = [];
        $value = $this->translations_all->where('key', $key);

        foreach (config('translations.locales', []) as $lang) {
            $result[$lang] = $value->where('lang', $lang)->first()->value ?? null;
        }

        return $result;
    }

    /**
     * Get translation for current language by column name.
     *
     * @param $key
     * @param null $lang
     * @return null
     */
    public function translation($key, $lang = null)
    {
        if (is_null($lang)) {
            $lang = app()->getLocale();
        }

        $value = $this->translations_all->where('key', $key)->where('lang', $lang)->first();

        return $value->value ?? null;
    }

    /**
     * Relation to Translation model.
     *
     * @return mixed
     */
    public function translations_all()
    {
        return $this->hasMany(Translation::class, 'translation_id', 'id')->where('translation_type', self::class);
    }

    /**
     * Generate a query for select translation.
     *
     * @param $class
     * @param $key
     * @return mixed
     */
    private function generateQuery($class, $key)
    {
        return \DB::raw("(SELECT `value` FROM `translations` WHERE `translation_id`=`" . $class->getTable() . "`.`" . $class->getKeyName() . "` AND `translation_type`='" . addcslashes(self::class, '\\') . "' AND `lang`='" . app()->getLocale() . "' AND `key`='" . $key . "') as `" . $key . "`");
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
