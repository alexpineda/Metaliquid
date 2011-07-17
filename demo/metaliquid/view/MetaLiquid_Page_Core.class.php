<?php



class MetaLiquid_Page_Core extends MetaLiquid_AbstractPage
{
	private $threadData;

	function __construct(&$threadData){
        parent::__construct(null);
		$this->threadData = $threadData;
		$this->templateSource = "core.html";
	}

    function Process()
    {
        //global $Get;
        $this->addChild ("pagetitle", "TEST HEADER");
        $this->addChild("header", new MetaLiquid_Page_Header(null));
        
        $this->addChild("cloud", new MetaLiquid_Page_Cloud(null, $this->threadData));

        return $this;
    }
    
}

?>
