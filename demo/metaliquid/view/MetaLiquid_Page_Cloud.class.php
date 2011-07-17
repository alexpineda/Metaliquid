<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class MetaLiquid_Page_Cloud extends MetaLiquid_AbstractPage
{
	private $data;
	protected $templateSource;

	function __construct($templateSource , &$data ){
		$this->data = $data;
		$this->templateSource = $templateSource;
	}

    function Process()
    {
        global $Get;
		$out = "<div id='metaliquid_cloud'>";

		foreach ($this->data['threads'] as $thread){
			$out .= "<a href=\"" . $Get->Config()->BaseURL .
					$Get->Config()->ForumLinkURL . $thread->getForumLinkID() ;
			$out .= "\">" . $thread->getTitle() . "</a>";

		}

		$out .= "</div>";

        //string template source
        $this->templateSource = $out;
        return $this;
    }

}

?>
