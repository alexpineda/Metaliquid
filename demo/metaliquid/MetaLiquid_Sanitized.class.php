<?php


class MetaLiquid_Sanitized
{

	function PathName($str)
	{
		//@todo pregreplace *?.&
		
		if (substr($str, -1) != "/"){
			return $str . "/";
		}
	}
	
	
}