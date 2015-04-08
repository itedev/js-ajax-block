<?php

namespace ITE\Js\AjaxBlock;

/**
 * Class AjaxBlockStorage
 *
 * @package ITE\Js\AjaxBlock
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class AjaxBlockStorage
{
    /**
     * @var array
     */
    protected static $storage;

    /**
     * @param string $blockName
     * @param string $blockContent
     */
    public static function addAjaxBlock($blockName, $blockContent)
    {
        self::$storage[$blockName] = $blockContent;
    }

    /**
     * @param string $blockName
     * @return string|null
     */
    public static function getAjaxBlockContent($blockName)
    {
        return isset(self::$storage[$blockName]) ? self::$storage[$blockName] : null;
    }

    /**
     * @return array
     */
    public static function getStorage()
    {
        return self::$storage;
    }

    /**
     *
     */
    public static function clearStorage()
    {
        self::$storage = array();
    }

    /**
     * @param $blockName
     */
    public static function removeFromStorage($blockName)
    {
        if (isset(self::$storage[$blockName])) {
            unset(self::$storage[$blockName]);
        }
    }
}