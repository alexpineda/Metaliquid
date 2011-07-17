<?php

class PageRowColumnTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	function ValidShouldBeTrueIfXpathFindsElements()
	{
		$dom = new DOMDocument();
		$dom->loadHTML("<html><table>".
			"<tr><td>Hi</td>" . "<td>bye</td></tr>" . 
			"</html>");
		
		$instructions = new SimpleXMLElement("<r><xpath>//tr/td[1]</xpath></r>");
		$column = new MetaLiquid_Pagination_PageRow_Column($dom, $instructions);
		
		$this->assertTrue($column->valid());
	}
	

}