<?php
/**
 * 
 *@author Alex Pineda <alexpineda86@gmail.com>
 */

abstract class MetaLiquid_AbstractPage
{
	protected $templateSource;
    protected $template = null;
    protected $children = array();

    function __construct( $templateSource){
        if ($templateSource){
            $this->templateSource = $templateSource;
        }
	}

    function AddChild($name, $child)
    {
        $this->children[$name] = $child;
    }

	function Display()
    {
        global $Get;
        
        if ($this->templateSource){
            $this->template =  $Get->Template($this->templateSource);
        }

        $output = "";
        foreach ($this->children as $name => $child){
            if ($child instanceof  MetaLiquid_AbstractPage) {
                $child = $child->Process()->Display();
            }
            $this->template->injectChild($name, $child);
        }

        //var_dump($this->template);
        return $this->template->getHTML();
    }

    abstract function Process();
}
?>