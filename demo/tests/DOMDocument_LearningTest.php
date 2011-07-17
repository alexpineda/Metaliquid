<?php


class DOMDocument_LearningTest extends PHPUnit_Framework_TestCase
{
	
	function setUp(){
		$this->dom = new DOMDocument();
		$this->dom->loadHTMLFile("teamliquid.html");
		$this->xpath = new DOMXPath($this->dom);
	}

	/**
	 * @test
	 * It's returning 2 a tags why?
	 */
	public function 
	EvaluateAElementFirstNodeIsADOMElement()
	{	
		$i = 2;
		$result = $this->xpath->evaluate("//table[@class='solid']/tr[$i]/td[2]/a[1]");
		
		$this->assertEquals(1, $result->length);
		$e = $result->item(0);
		
		$this->assertEquals("a", $e->tagName);
		$this->assertEquals("[Interview] MorroW: \"We need new maps\"", $e->textContent);
		$href = $e->getAttribute("href");
		$this->assertEquals("/forum/viewmessage.php?topic_id=192474", $href);	
	}

	/**
	 * @test
	 */
	public function 
	EvaluateTDElementFirstNodeIsADOMElement()
	{
		$i = 2;
		$result = $this->xpath->evaluate("//table[@class='solid']/tr[$i]/td[3]");
		$result = $result->item(0);
		
		$this->assertEquals("Malnor", $result->textContent);
	}
}