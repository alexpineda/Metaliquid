<?php
class MetaLiquid_DiagramDefinitions extends MetaLiquid_InheritXML
{
    function FromXMLFiles(&$parsexml)//probably the only real option
    {
		$subjects = array();
		$templates_xml  = simplexml_load_file($this->dir . $this->templatesfile);
		
		$whitelist = array();
		if ((string)$this->whitelist){
			$whitelist = explode(",", (string)$this->whitelist);
		}
		
    	$blacklist = array();
		if ((string)$this->blacklist){
			$blacklist = explode(",", (string)$this->blacklist);
		}
		
		$files = scandir($this->dir);
		
		foreach ($files as $file)
		{
			if (is_file($this->dir . $file)){
				if (!empty($whitelist) && !in_array($file, $whitelist)) {continue;}
				elseif (in_array($file, $blacklist)) {continue;}
				if ($file != $this->templatesfile){
					$xml = simplexml_load_file($this->dir . $file, 'SimpleXMLElement', LIBXML_NOWARNING);		
					$parsexml->MergeOnAttribute($xml, $templates_xml, $this->template_attr);
					$subjects[] = $xml;
				}
			}
		}

		return $subjects;
    }
    
}