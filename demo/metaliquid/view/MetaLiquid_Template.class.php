<?php

/**
 * Description of MetaLiquid_Template
 *
 * @author ALex Pineda <alexpineda86@gmail.com>
 */
class MetaLiquid_Template {
    private $html;

    function __construct($file)
    {
        global $Get;
        if (!empty($file)){
            $filename = $Get->Config()->TemplatePath . $file;
            if (file_exists($filename)){
                $this->html =  htmlspecialchars(file_get_contents($filename));
            }else{
                $this->html = htmlspecialchars( $file);
            }
        }
    }

    function injectChild($name, $child)
    {
        $this->html = str_replace("%" . strtoupper(trim($name)) . "%", $child, $this->html);
    }

    function getHTML()
    {
        return htmlspecialchars_decode($this->html);
    }
}
?>
