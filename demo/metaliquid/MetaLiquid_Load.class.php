<?php

class MetaLiquid_Load
{
	function DiagramDefinitions($definition)
	{
		return new MetaLiquid_DiagramDefinitions($definition);
	}
	
	function DiagramDefinition($definition)
	{
		return new MetaLiquid_DiagramDefinition($definition);
	}
}