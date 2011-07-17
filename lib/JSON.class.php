<?php
/**
 * @author Alex Pineda <alex@brainyweb.ca>
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
            //manual discovery (for now) of templates to copy over
            if (isset($slave['template']['rowToParse'])){
                $slave['rowToParse'] = $master["{$slave['template']['rowToParse']}"];
            }
            if (isset($slave['template']['pagination'])){
                $slave['pagination'] = $master["{$slave['template']['pagination']}"];
            }
        }
    }
}
