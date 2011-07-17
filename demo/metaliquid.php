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


$parsexml = new MetaLiquid_Parse_XML();
$master = new MetaLiquid_InheritXML(simplexml_load_file('etc/master.xml'));

//are the items outdated?
$load = new MetaLiquid_Load();
$diagramDefinitions = $load->DiagramDefinitions($master->diagram_definitions)->FromXMLFiles($parsexml);
	
foreach ($diagramDefinitions as $diagram){
	$urls = $load->DiagramDefinition($diagram)->PaginationUrls($master->defaults, $master->baseurl);		
	FillDiagram($diagram, $master->defaults, $master->baseurl, $urls, $parsexml);
	$diagram->asXML("data/work/" . $diagram->meta->group . $diagram->meta->subgroup . 
					      $diagram->meta->id . ".xml");
}

	//analyze and index the data according to various mathematical formulas/requirements
	
	//copy the working directory files to the production directory
	
function DownloadTargetPage($file, $url){
	if (!file_exists($file)){
		try {
			$f = fopen($file);
			fwrite($f, file_get_contents($url));
			fclose($f);
			return true;
		}
		catch (Exception $e){
			fclose($f);
			return false;	
		}
	}
	
}

function ExecuteRow($diagram, $row, $row_num, $xpath, $identifier)
{
	global $parsexml;
	
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
				//don't lock next row
				return false;
			}
		}//process diagrams
		
		//lock next row
		return true;		
}

function FillDiagram(SimpleXMLElement $diagram, SimpleXMLElement $defaults, $baseurl, $urls, &$parsexml)
{
	global $master;
	
	$data = $diagram->addChild('data');
			
	foreach ($urls as $url){
		$dom = new DOMDocument();
		//@todo implement proper file functionality
		DownloadTargetPage("data/work/" . $diagram->meta->group . 
					$diagram->meta->subgroup . $diagram->meta->id . ".html", $url);
		
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
				//@todo custom row names?
				$row = $data->addChild('row');		
				$lockNextRow = ExecuteRow($diagram, $row, $row_num, $xpath, $identifier);			
				$row_num++;
				//@todo verify this works
				if ($row_num >= $maxrows) {$lockNextRow = false;}
			}//more rows?
		}//url loads?
	}//more urls?
	
	$diagram->meta->addChild("lastupdate", time());

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
