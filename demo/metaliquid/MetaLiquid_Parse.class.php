<?php


class MetaLiquid_Parse
{

	function ReplaceRow( $val)
	{
		global $Get;

		/*
		 *@todo change this to a general function using instructions definitions
		 */
		return str_replace("%ROW%", $val, $this->str);
	}

    function IsURL($url)
    {
        /**
         *@todo find an honest implementation
         */
        $scheme_whitelist = array(
            "http", "https", "ftp"
        );

        return in_array(parse_url($url, PHP_URL_SCHEME), $scheme_whitelist);

    }
	

}