<?php
//@todo test page start getting set to xml value
//simplexmlelement will return empty string if it has children :S

/**
 * 
 * Enter description here ...
 * @author Owner
 * @group ignore
 */
class PaginationClassTest extends PHPUnit_Framework_TestCase
{
	private $p;
	
	/**
	 * @test
	 */
	function PageStartShouldGetsSetToXMLValueIfSet()
	{
		$f = new SimpleXMLElement("<r><page_start>1</page_start></r>");
		$this->p = new MetaLiquid_Pagination($f,"container", "stampline");
		
		$this->assertEquals( 1, $this->p->page_start);
	}
	
	/**
	 * @test
	 */
	function PageStartShouldBeZeroIfXMLValueNotSet()
	{
		$this->p = new MetaLiquid_Pagination(null,"container", "stampline");
		$this->assertEquals(0 , $this->p->page_start);
	}
	
	/**
	 * @test
	 */
	function PaginationValidLoadsReturnsTrueIfPageCanLoad()
	{
		$this->p = new MetaLiquid_Pagination(null,"http://www.", "teamliquid");
		$this->p->url = ".net";
		$this->p->maxpages = 1;
		
		$this->assertTrue($this->p->valid());
	}
	
	/**
	 * @test
	 */
	function PaginationValidReplacesIdentifierWithPageNumber()
	{
		$f = new SimpleXMLElement("<r><identifier>%PAGE%</identifier><page_start>2</page_start></r>");
		$this->p = new MetaLiquid_Pagination($f,"", "");
		$this->p->url = "http://www.teamliquid.net/forum/viewmessage.php?topic_id=192474&amp;currentpage=%PAGE%";
		$this->p->maxpages = 1;
		
		$this->assertTrue($this->p->valid());
	}
	
	/**
	 * @test
	 * 
	 * This particular page has 2 pages then pagination should run out
	 */
	function PaginationStopsAtMax()
	{
		$f = new SimpleXMLElement("<r><identifier>%PAGE%</identifier>" .
		"<page_start>1</page_start><maxpages>5</maxpages></r>");
		$this->p = new MetaLiquid_Pagination($f,"", "");
		$this->p->url = "http://www.teamliquid.net/forum/viewmessage.php?topic_id=411&amp;currentpage=%PAGE%";
		
		$count = 0;
		foreach ($this->p as $key => $page){
			$count ++;
		}
		
		$this->assertEquals(5, $count);
	}
	
}