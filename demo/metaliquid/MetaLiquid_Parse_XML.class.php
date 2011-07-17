<?php

/**
 * 
 * This class works with SimpleXMLELement objects
 * You can use the single xml object set during the construction
 * or you can pass in any xml object into the functions
 * 
 * 
 * @author Alex Pineda <alexpineda86@gmail.com>
 *
 */

class MetaLiquid_Parse_XML
{
    
	//this is ugly, if theres a better way replace it
	public function 
	ProcessInstruction ($xml, $ins = '')
    {
    	
        $doc = new DOMDocument();
        $doc->loadXML($xml->asXML());
		
        $xpath = new DOMXpath($doc);
        $nodes = $xpath->evaluate("/child::processing-instruction('$ins')");
        $pi = array();
        
        if(empty($nodes)){
            return null;
        }
        else{
          
          foreach ($nodes as $node){
            $pi[] = $this->_piGetPIAsArray($node);
          }
        }
        return $pi;
    }

    private function 
    _piGetPIAsArray($node){
        $arr = array();
        if (isset($node->target)){
            $arr['target'] = $node->target;
            preg_match_all("/(([a-zA-Z]+)\s?=\s?\"([a-zA-Z09_. ]+)\")/", $node->data, $out);

            for ($i = 0; $i < count($out[2]); $i++ ){
                $arr['attr'][$out[2][$i]] = $out[3][$i];
            }
        }

        return $arr;
    }
	
	public function 
	MergeAttributes($root, $append, $attrbl = array())
	{
		foreach ($append->attributes() as $key => $value){
			if (!in_array($key, $attrbl)){
				$root->addAttribute($key, $value);
			}
		}
	}


	public function 
	Merge(SimpleXMLElement $root, SimpleXMLElement  $append,
		  $nodebl = array(), $attrbl = array())
	{			
		
		$this->MergeAttributes($root, $append, $attrbl);

		//merge children
		foreach ($append->children() as $child_append){
			//blacklist short circuit
			if (in_array($child_append->getName(), $nodebl)) { continue; }
		
			//for each appending child, give myself a child and merge it
			if ($child_append->count()){
				$this->Merge( $root->addChild($child_append->getName()), 
								$child_append, $nodebl , $attrbl);
			}else{
				//add child as text node if no children
				$this->AddChild($root, $child_append);
			}
		}
	}	


	/**
	 * A particularly specialized function. Allows xml elements with attributes
	 * of name $attr_key to be merged with elements from $nodes with matching node name
	 * eg. 
	 * $root = <root id="a"></root>
	 * $nodes = <nodes><a>....</a></nodes>
	 * 
	 * if $attr_key = 'id'
	 * $root will then inherit all of a's children, because the id matches the tagname
	 * special note that first level text nodes will be copied (<a> in the example)
	 * 
	 */
	public function 
	MergeOnAttribute(SimpleXMLElement $root, SimpleXMLElement $templates, $template_key,
					$nodesbl = array(), $attrsbl = array() )
	{
		foreach ($templates as $template){
			$this->_MergeOnAttribute($root, $template, $template_key, $nodesbl, $attrsbl);
		}		
	}
	
	public function 
	_MergeOnAttribute(SimpleXMLElement $root, SimpleXMLElement $template, $template_key,
					$nodesbl = array(), $attrsbl = array() )
	{
		if (in_array($root->getName(), $nodesbl)) { return; }
	
		if ((string)$root[$template_key] == $template->getName()){
			$this->Merge($root, $template, $nodesbl, $attrsbl);
		}
		
		foreach ($root as $child){
			$this->_MergeOnAttribute($child, $template, $template_key, $nodesbl, $attrsbl);
		}
	}
	
	public function 
	CropAttributes(SimpleXMLElement $root, SimpleXMLElement $removed, array $whitelist)
	{		
		$remove = array();
		
		foreach ($root->attributes() as $name => $val){
			if (!in_array((string)$name,$whitelist)){
				$removed->addAttribute($name, $val);
				$remove[] = (string)$name;
			}
		}
		
		foreach ($remove as $key){
			unset($root[$key]);
		}
	}
	
	//$root and $removed represent a current level of processing
	public function 
	Crop(SimpleXMLElement $root, SimpleXMLElement $removed, 
				$tags = array(), $attrs = array())
	{		

		if (!is_array($tags)){
			$tags = array ($tags);
		}
		
		$this->CropAttributes($root, $removed, $attrs);
		
		foreach ($root as $child){
			if (in_array($child->getName(), $tags)){
				$this->Crop($child, $removed->addChild($child->getName()), $tags, $attrs);
			}else{
				//'log' the remove child
				$this->AddChild($removed, $child);
				//remove it								
				unset($child);
			}
		}
		
	}		
	
	
	//@todo test
	private function 
	RemoveEmptyNodes(SimpleXMLElement $root , $blacklist = array()){
		global $Parse;
			
		//blacklist short circuit
		if (in_array((string)$root->getName(), $blacklist)) { return; }
		
		foreach ($root->children() as $child){
			
			$this->RemoveEmptyNodes($child, $blacklist);
			
			//@todo check for attributes
			if (trim((string)$child) == "" && $child->count == 0){
				unset($child);
			}
		}
	}
	
	//'deep adding' as opposed to SimpleXML's shallow
	public	function
	AddChild($root, $child, $nodebl = array(), $attrbl = array())
	{
		if ($child->count()){
			$root_child = $root->addChild($child->getName());
			$this->Merge($root_child, $child, $nodebl, $attrbl);
		}else{
			$root_child = $root->addChild($child->getName(), (string)$child);
			$this->MergeAttributes($root_child, $child, $attrbl);
		}	
	}
	
}
?>
