<?php

class MetaLiquid_User
{

	function __construct()
	{
		session_start();
	}
	
	function FirstView()
	{
		return !isset($_SESSION['set']);
	}

	function FavoriteView()
	{
		if (isset($_SESSION['vf'])){
			return $_SESSION['vf'];
		}
	}

	function SelectedView()
	{
		if (isset($_REQUEST['vs'])){
			return $_REQUEST['vs'];
		}
	}

	function __destruct()
	{
		$_SESSION['set'] == true;
	}
}