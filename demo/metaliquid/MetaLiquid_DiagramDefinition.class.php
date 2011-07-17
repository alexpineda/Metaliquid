<?php
class MetaLiquid_DiagramDefinition extends MetaLiquid_InheritXML
{
 	function PaginationUrls($defaults, $baseurl)
 	{
 		$pagination = $this->meta->pagination;
 		$diagram_url = $this->meta->url;
 		
		$urls = array();
		if ((string)$pagination->url){
			$pagination_url = (string)$pagination->url;
			
			$page_num = (int) $defaults->startatpage;
			if ((int)$pagination->startatpage){
				$page_num = (int)$pagination->startatpage;
			}
			
			$maxpages = (int)$defaults->maxpages;
			if ((int)$pagination->maxpages){
				$maxpages = (int)$pagination->maxpages;
			}
			
			$identifier = (string)$defaults->page_identifier;
			if ((string)$pagination->page_identifier){
				$identifier = (string)$pagination->page_identifier;
			}
			
			while ($page_num <= $maxpages){
				$append_url = str_replace($identifier, $page_num, $pagination_url);
				$urls[] = $baseurl . $diagram_url . $append_url;
				$page_num++;
			}
				
		}else{
			$urls[] =  $baseurl . $diagram_url;
		}
		
		return $urls;

 	}   
}