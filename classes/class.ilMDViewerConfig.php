<?php

/**
 * Class ilMDViewerConfig
 * @author Fabian Schmid <fabian@sr.solutions>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilMDViewerConfig extends ActiveRecord
{
    private const TABLE_NAME = 'md_tme_config';
    public const KEY_IDS_OF_AUTHORIZED_ROLES = "ids_of_authorized_roles";
    public const KEY_MD_BLOCKS_FILTER_ACTIVE = 'md_blocks_filter_active';

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    protected static array $cache = [];
    protected static array $cache_loaded = [];
    protected bool $ar_safe_read = false;

    /**
     * @return mixed
     */
    public static function getConfigValue(string $name = null)
    {
        if (!isset(self::$cache_loaded[$name])) {
            $obj = self::find($name);
            self::$cache[$name] = $obj instanceof \ActiveRecord ? $obj->getValue() : null;
            self::$cache_loaded[$name] = true;
        }

        return self::$cache[$name];
    }

    /**
     * @param mixed $value
     */
    public static function setConfigValue(string $name, $value): void
    {
        /** @var ilMDViewerConfig $obj */
        $obj = self::findOrGetInstance($name);
        $obj->setValue($value);
        if (self::where(['name' => $name])->hasSets()) {
            $obj->update();
        } else {
            $obj->create();
        }
    }

    public static function remove(string $name): void
    {
        /**
         * @var ilMDViewerConfig $obj
         */
        $obj = self::find($name);
        $obj->delete();
    }

    /**
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           250
     */
    protected ?string $name = '';
    /**
     * @var mixed
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           2048
     */
    protected $value;

    public function wakeUp($field_name, $field_value)
    {
        if ($field_value === null || $field_value === 'null') {
            return null;
        }
        switch ($field_name) {
            case "value":
                return json_decode($field_value, true, 512, JSON_THROW_ON_ERROR);
            default:
                return $field_value;
        }
    }

    /**
     * @param $field_name
     */
    public function sleep($field_name)
    {
        switch ($field_name) {
            case "value":
                return json_encode($this->value, JSON_THROW_ON_ERROR);
            default:
                return null;
        }
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value === 'null' ? null : $this->value;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
