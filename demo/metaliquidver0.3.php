<?php

/** 
* @todo Change template matching to attribute!
*/

set_include_path(get_include_path() . PATH_SEPARATOR . "metaliquid/");

include "metaliquid/MetaLiquid_InheritXML.class.php";
include "metaliquid/MetaLiquid_DiagramDefinitions.class.php";
include "metaliquid/MetaLiquid_DiagramDefinition.class.php";
include "metaliquid/MetaLiquid_Parse_XML.class.php";
include "metaliquid/MetaLiquid_Parse.class.php";
include "metaliquid/MetaLiquid_Filters.php";

include "metaliquid/MetaLiquid_Load.class.php";
//include "metaliquid/MetaLiquid_Pagination_PageRow.class.php";
//include "metaliquid/MetaLiquid_Pagination_PageRow_Column.class.php"; 


$parsexml = new MetaLiquid_Parse_XML();
$master = new MetaLiquid_InheritXML(simplexml_load_file('etc/master.xml'));

//are the items outdated?
if ($master->lastupdate < (time() -  ($master->expirydate ) )){

	$load = new MetaLiquid_Load();
	$diagramDefinitions = $load->DiagramDefinitions($master->diagrams_config)->FromXMLFiles($parsexml);
		
	foreach ($diagramDefinitions as $diagram){
		$urls = getURLS($master->defaults, $diagram->meta->pagination, 
						(string)$master->baseurl, (string)$diagram->meta->url);
		
		$diagram = ProcessDiagram($diagram, $master->defaults, $master->baseurl, $urls, $parsexml);
		
	}

	//analyze and index the data according to various mathematical formulas/requirements
	
	//copy the working directory files to the production directory
	
	
}

function getURLS($defaults, $pagination, $baseurl, $diagram_url)
{
	
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
		
		$identifier = (string)$defaults->identifier;
		if ((string)$pagination->identifier){
			$identifier = (string)$pagination->identifier;
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

function ProcessDiagram(SimpleXMLElement $diagram, SimpleXMLElement $defaults, $baseurl, $urls, &$parsexml)
{

	$data = $diagram->addChild('data');
			
	foreach ($urls as $url){
		$dom = new DOMDocument();
		if (!file_exists("data/work/" . $diagram->meta->group . 
					$diagram->meta->subgroup . $diagram->meta->id . ".html")){
			try {
				$f = fopen("data/work/" . $diagram->meta->group . 
					$diagram->meta->subgroup . $diagram->meta->id . ".html", "w");
				fwrite($f, file_get_contents($url));
				fclose($f);
			}
			catch (Exception $e){
				fclose($f);	
			}
		}
		
		if (@$dom->loadHTMLFile("data/work/" . $diagram->meta->group .
		 $diagram->meta->subgroup . $diagram->meta->id . ".html")){
			
			$xpath = new DOMXPath($dom);
			
			$maxrows = (int)$defaults->maxrows;
			
			$row_num = (int)$defaults->startatrow;
			if ((int)$diagram->meta->columns_diagram['startatrow']){
				$row_num = (int)$diagram->meta->columns_diagram['startatrow'];
			}
			$identifier = (string)$defaults->row_identifier;
			if ((string)$diagram->meta->row_diagram['identifier']){
				$identifier = (string)$diagram->meta->row_diagram['identifier'];
			}
			

			$lockNextRow = true;
			while ($lockNextRow){
				//echo "$url\n";
				//@todo custom row names
				$row = $data->addChild('row');		
				foreach ($diagram->meta->columns_diagram->children() as $column_diagram){
					$columnFailed = true;
						foreach ($column_diagram->children() as $instruction){
							//may have many instructions as fall backs
							$xstring = (string)$instruction;
							$xstring = str_replace($identifier, $row_num, $xstring);
							
							//echo "$xstring\n";
				    		$results = $xpath->query($xstring);

				    		if ($results === false || $results->length != 1){
				    			if ($column_diagram['optional'] == 'true'){
				    				//echo "optional failed, next column\n";
				    				$columnFailed = false;
				    				break;
				    			}else{
				    				//echo "instruction failed\n";
				    				continue;
				    			}
				    		}	 
							
				    		$columnFailed = false;
				    		$element = $results->item(0);
							
				    		$colName = $column_diagram['id'];
				    		$colValue = null;
				    		
				    		$filters = $column_diagram['filter'];
				    		if ((string)$filters){
				    			$filters = explode(",", (string)$filters);
				    			foreach ($filters as $filter){
				    				$colValue = isset($colValue) ? 
				    				ApplyFilter($filter, $colValue) : ApplyFilter($filter, $element);
				    			}
				    		}else{
				    			$colValue = trim($element->textContent);	
				    		}
							
							
							//echo "$colName = $colValue\n";
				    		$col = $row->addChild($colName, $colValue );
				    		//@todo document these reserved column_diagram attributes
				    		$parsexml->MergeAttributes($col, $column_diagram, array("id","filter", "optional", "title"));
				    		break;
						}//process instructions
						if ($columnFailed){
							//echo "\ncolumn failed\n";
							unset($row);
							$lockNextRow = false; 
							break;
						}
					}//process diagrams				
				$row_num++;
				//@todo verify this works
				if ($row_num >= $maxrows) {$lockNextRow = false;}
			}//more rows?
		}//url loads?
	}//more urls?
	
	return $diagram;
	/*$dom = new DOMDocument('1.0');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($diagram->saveXML());
	$dom->save("data/work/" . $diagram->meta->group . $diagram->meta->subgroup . 
						      $diagram->meta->id . ".xml");
						      */

}

	function ApplyFilter($filter, $element)
	{

		$filters = new MetaLiquid_Filters();
    	if (is_callable(array($filters, $filter))){
    		return call_user_func(array($filters, $filter), trim($element->textContent) );	
   	    }elseif ($element instanceof DOMElement){
   	    	return $element->getAttribute($filter);
   	    }else{
   	    	return (string)$element;
  		}
	}


/**

if ($Get->User()->FirstView() ) {
	if ($Get->User->FavoriteView()){
		$View->Page($Get->User->FavoriteView());
	}
	else{
		$View->DefaultPage();
	}
}
else{
	$View->Page($Get->User->SelectedView());
}


$view = new MetaLiquid_User_View( 
	$instructions )
);
*/


function vdump($str){
	xdebug_print_function_stack();
	var_dump($str);
}
