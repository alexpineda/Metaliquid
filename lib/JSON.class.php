<?php
/**
 * @author Alex Pineda <alex@brainyweb.ca>
 * @package MetaLiquid
 * @subpackage Util
 * Helper class for JSON stuff
 */
 
class JSON {
    static function load($filename)
    {
        return json_decode(file_get_contents($filename), true);
    }

    static function mergeOnTemplate(&$slave, $master)
    {
        if (isset($slave['template'])){
            foreach ($slave['template'] as $key => $masterKey)
                {
                $slave[$key] = $master[$masterKey];
                }
        }
    }
}
