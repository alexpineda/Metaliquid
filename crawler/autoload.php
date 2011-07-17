<?php
function __autoload($className){
    foreach(glob("crawler/*") as $dir){
        foreach (glob("$dir/*.php") as $script){
            //if ($className == substr($script, -strlen($classname) - 10))
            $cnLength = strlen($script) - strlen($dir) - 11;
            $fileClassName =  substr($script, strlen($dir)+1, $cnLength);
            if ($className == $fileClassName){
                require_once $script;
            }
        }
    }
}

 