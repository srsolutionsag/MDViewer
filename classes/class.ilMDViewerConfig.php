<?php

/**
 * Class ilMDViewerConfig
 * @author Fabian Schmid <fabian@sr.solutions>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilMDViewerConfig extends ActiveRecord
{

    const TABLE_NAME = 'md_tme_config';
    const KEY_IDS_OF_AUTHORIZED_ROLES = "ids_of_authorized_roles";
    const KEY_MD_BLOCKS_FILTER_ACTIVE = 'md_blocks_filter_active';

    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @return string
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @var array
     */
    protected static $cache = array();
    /**
     * @var array
     */
    protected static $cache_loaded = array();
    /**
     * @var bool
     */
    protected $ar_safe_read = false;

    /**
     * @param string $name
     * @return string|mixed
     */
    public static function get($name = null)
    {
        if (!isset(self::$cache_loaded[$name])) {
            /**
             * @var ilMDViewerConfig $obj
             */
            $obj = self::find($name);
            if ($obj === null) {
                self::$cache[$name] = null;
            } else {
                self::$cache[$name] = $obj->getValue();
            }
            self::$cache_loaded[$name] = true;
        }

        return self::$cache[$name];
    }

    /**
     * @param string       $name
     * @param string|mixed $value
     */
    public static function set($name, $value)
    {
        /**
         * @var ilMDViewerConfig $obj
         */
        $obj = self::findOrGetInstance($name);
        $obj->setValue($value);
        if (self::where(array('name' => $name))->hasSets()) {
            $obj->update();
        } else {
            $obj->create();
        }
    }

    /**
     * @param string $name
     */
    public static function remove($name)
    {
        /**
         * @var ilMDViewerConfig $obj
         */
        $obj = self::find($name);
        if ($obj !== null) {
            $obj->delete();
        }
    }

    /**
     * @var string
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           250
     */
    protected $name;
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           2048
     */
    protected $value;

    /**
     * @param $field_name
     * @param $field_value
     * @return mixed
     */
    public function wakeUp($field_name, $field_value)
    {
        switch ($field_name) {
            case "value":
                return json_decode($field_value, true);
                break;
        }

        return null;
    }

    /**
     * @param $field_name
     * @return string
     */
    public function sleep($field_name)
    {
        switch ($field_name) {
            case "value":
                return json_encode($this->value);
                break;
        }

        return null;
    }

    /**
     * @param string|mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string|mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
