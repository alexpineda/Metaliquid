<?php
class MetaLiquid_InheritXML
{

	function __construct($source = null)
	{
		
		if (!is_null($source)) {
			foreach ($source as $prop){
				if ($prop->count() == 0 ){
					$str = (string)$prop;
					if (!empty($str)){
						$name = (string)$prop->getName();
						$this->$name = $str;
					}
				}
				else{
					$name = (string)$prop->getName();
					$this->$name = $prop;
				}
			}
		}
	}
    
}

?>
