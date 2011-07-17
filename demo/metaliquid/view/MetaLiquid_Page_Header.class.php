<?php

class MetaLiquid_Page_Header extends MetaLiquid_AbstractPage
{

	function __construct( $templateSource ){
        if (!$templateSource) {$templateSource = 'header.html';}
        parent::__construct($templateSource);
	}

    function Process()
    {
        return $this;
    }
}