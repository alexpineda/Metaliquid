<?php


class DiagramsClassTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	function TemplatesShouldBeAppliedToDiagrams()
	{
		$parsexml = new MetaLiquid_Parse_XML();
		$diag = new MetaLiquid_Diagrams();
		
		$diag->dir = "tests/demoxml/";
		$diag->templatesfile = "templates.xml";
		$diag->template_attr = "template";
		
		$results = $diag->FromXMLFiles($parsexml);
		
		$this->assertEquals("correct.com",  (string)$results[0]['url']);
		$this->assertEquals("gotme", (string)$results[0]->mychild);
		$this->assertEquals("correct.com", (string)$results[0]->deeper->deepest['url']);
		$this->assertEquals("deepest", (string)$results[0]->deeper->deepest);
	}
	

}