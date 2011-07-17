<?php

class Get_PaginationTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	function Test1()
	{
		$x = new SimpleXMLElement("<r><id>1</id></r>");
		$i = new MetaLiquid_InheritXML($x);
		
		$this->assertEquals(1, $i->id);
				
	}	
	
	/**
	 * @test
	 */
	function Test2()
	{
		$x = new SimpleXMLElement("<r><level1><level2>2</level2></level1><level3>hi</level3></r>");
		$i = new MetaLiquid_InheritXML($x);
		
		$this->assertTrue($i->level1 instanceof SimpleXMLElement);
				
	}
}