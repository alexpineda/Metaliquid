<?php

//@todo testing for empty string might not be the best way :S
//simplexmlelement will return empty string if it has children :S

class Parse_XML_MergeTest extends PHPUnit_Framework_TestCase
{
	private $parse;
	
	function setUp()
	{
		$this->parse = new MetaLiquid_Parse_XML();
	}
	
    
    /**
	 * @test
	 */
    function MergeShouldCopyAttributes()
    {
    	$root = new SimpleXMLElement("<r></r>");
    	$app = new SimpleXMLElement("<r id='sexy'></r>");
    	
    	$this->parse->Merge($root, $app);

    	$this->assertEquals((string)$root['id'], "sexy");
    }
    
	/**
	 * @test
	 */
    function MergeShouldCopyChildrenAttributes()
    {
    	$root = new SimpleXMLElement("<r></r>");
    	$app = new SimpleXMLElement("<r><b id='sexy'></b></r>");
    
    	$this->parse->Merge($root, $app);

    	$this->assertEquals((string)$root->b['id'], "sexy");
    }
    
	/**
	 * @test
	 */
    function MergeShouldCopyChildrenWithTextNodes()
    {
    	$root = new SimpleXMLElement("<r></r>");
    	$app = new SimpleXMLElement("<r><b>hi</b></r>");
    	
    	$this->parse->Merge($root, $app);
		   	
    	$this->assertEquals((string)$root->b, "hi");
    }
    
	/**
	 * @test
	 */
    function MergeShouldCopyChildrenWithChildren()
    {
    	$root = new SimpleXMLElement("<r></r>");
    	$app = new SimpleXMLElement("<r><b><c>hi</c></b></r>");
    	
    	$p = new MetaLiquid_Parse_XML();
    	$p->Merge($root, $app);

    	$this->assertEquals((string)$root->b->c, "hi");
    }
    
	/**
	 * @test
	 */
    function MergeShouldNotCopyBlacklistChildNodes()
    {
    	$root = new SimpleXMLElement("<r></r>");
    	$app = new SimpleXMLElement("<r><b>hi</b></r>");
    	
    	$this->parse->Merge($root, $app, array("b"));
		
    	//have to test this way because testing for null will
    	//encourage simplexmlelement to create empty object instead
    	$this->assertEmpty((string)$root->b);
    }
    
	/**
	 * @test
	 */
    function MergeShouldNotCopyBlacklistAttributes()
    {
    	$root = new SimpleXMLElement("<r></r>");
    	$app = new SimpleXMLElement("<r id='hi'></r>");
    	
    	$this->parse->Merge($root, $app, null, array("id"));
		
    	//have to test this way because testing for null will
    	//encourage simplexmlelement to create empty object instead
    	$this->assertEmpty((string)$root['id']);
    }
    
/**
     * @test
     */
    function MergeOnAttributeShouldCopyNodes()
    {
        $templates = new SimpleXMLElement("<templates><right><r>hi</r></right>" .
        "<wrong>Dont tase me bro!</wrong></templates>");
        $root = new SimpleXMLElement("<r template='right' ></r>");

        $this->parse->MergeOnAttribute($root, $templates, "template");
        
        $this->assertEquals((string)$root->r, "hi" );

    }
    
    /**
     * @test
     * @group target
     */
    function MergeOnAttributeShouldWorkForAnyNodeNeedingATemplate()
    {
        $templates = new SimpleXMLElement("<templates><right><node>hi</node></right>" .
        "<wrong>Dont tase me bro!</wrong></templates>");
        $root = new SimpleXMLElement("<r><item><child template='right' /></item></r>");

        $this->parse->MergeOnAttribute($root, $templates, "template");
               
        $this->assertEquals("hi", (string)$root->item->child->node );

    }
    
	/**
     * @test
     */
    function MergeOnAttributeShouldCopyAttributes()
    {
        $templates = new SimpleXMLElement("<templates><right id='hi'></right>" .
        "<wrong>Dont tase me bro!</wrong></templates>");
        $root = new SimpleXMLElement("<r template='right' ></r>");

        $this->parse->MergeOnAttribute($root, $templates, "template");
        
        $this->assertEquals((string)$root['id'], "hi" );
    }
    
	/**
	 * @test
	 */
    function MergeOnAttributeShouldNotCopyBlacklistChildNodes()
    {
        $templates = new SimpleXMLElement("<templates><right><node>hi</node></right>" .
        "<wrong>Dont tase me bro!</wrong></templates>");
        $root = new SimpleXMLElement("<r><item><child template='right' /></item></r>");

        $this->parse->MergeOnAttribute($root, $templates, "template", array("child"));
        
        $this->assertEmpty((string)$root->item->child->node);
    }
    
    
	/**
	 * @test
	 */
    function MergeOnAttributeShouldNotCopyBlacklistAttributes()
    {
        $templates = new SimpleXMLElement("<templates><right face='funny'></right>" .
        "<wrong>Dont tase me bro!</wrong></templates>");
        $root = new SimpleXMLElement("<r><item template='right' /></r>");

        $this->parse->MergeOnAttribute($root, $templates, "template", array(), array("face"));
        
        $this->assertEmpty((string)$root->item['face']);
    }
    
	/**
	 * @test
	 */
    function CropAttributesShouldRemoveUnwhitelistedAttributes()
    {
    	$root = new SimpleXMLElement("<r black='bye'></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->CropAttributes($root, $rem, array());
    	
 		$this->assertEmpty((string)$root['black']);
 		
    }
    
	/**
	 * @test
	 */
    function CropAttributesShouldRetainWhitelistedAttributes()
    {
    	$root = new SimpleXMLElement("<r white='hi'></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->CropAttributes($root, $rem, array("white"));
    	
 		$this->assertNotEmpty((string)$root['white']);
    }
    
	/**
	 * @test
	 */
    function CropAttributesShouldCopyUnwhitelistedAttributesToRemoved()
    {
    	$root = new SimpleXMLElement("<r black='bye'></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->CropAttributes($root, $rem, array());
    	
 		$this->assertNotEmpty((string)$rem['black']);
 		
    }
   
	/**
	 * @test
	 */
    function CropShouldCopyNestedUnwhitelistedAttributesToRemoved()
    {
    	$root = new SimpleXMLElement("<r><black><black2 attr='hi'></black2></black></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->Crop($root, $rem);
    	
 		$this->assertNotEmpty((string)$rem->black->black2['attr']);
    }
    
    /**
	 * @test
	 */
    function CropShouldRemoveUnwhitelistedNodes()
    {
    	$root = new SimpleXMLElement("<r><black></black></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->Crop($root, $rem);
    	
 		$this->assertEmpty((string)$root->black);
    }
    
    /**
	 * @test
	 */
    function CropShouldKeepWhitelistedNodesUnwhitelistedNodes()
    {
    	$root = new SimpleXMLElement("<r><white><black>bye</black></white></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->Crop($root, $rem, array("white"));
    	
 		$this->assertNotEmpty((string)$root->white->black);
    }
    
    /**
	 * @test
	 */
    function CropShouldKeepWhitelistedNodes()
    {
    	$root = new SimpleXMLElement("<r><white>hi!</white></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->Crop($root, $rem, array("white"));
    	
 		$this->assertNotEmpty((string)$root->white);
    }
    
    /**
	 * @test
	 */
    function CropShouldKeepWhitelistedNodesWhitelistedNodes()
    {
    	$root = new SimpleXMLElement("<r><white><white>hi</white></white></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->Crop($root, $rem, array("white"));
    	
 		$this->assertNotEmpty((string)$root->white->white);
    }
    
    /**
	 * @test
	 */
    function CropShouldCopyUnwhitelistedNodesToRemoved()
    {
    	$root = new SimpleXMLElement("<r><black>hi</black></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->Crop($root, $rem);
    	
 		$this->assertNotEmpty((string)$rem->black);
    }
    
	/**
	 * @test
	 */
    function CropShouldCopyNestedUnwhitelistedNodesToRemoved()
    {
    	$root = new SimpleXMLElement("<r><black><black>hi</black></black></r>");    	
    	$rem = new SimpleXMLElement("<removed></removed>");
    	$this->parse->Crop($root, $rem);
    	
 		$this->assertNotEmpty((string)$rem->black->black);
    }
    
    
    
}

?>
