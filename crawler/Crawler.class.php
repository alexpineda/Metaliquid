<?php
/**
 * @author Alex Pineda <alex@brainyweb.ca>
 * @package MetaLiquid
 */
namespace MetaLiquid{
    class Crawler
    {
        private $defaults;
        private $templates;

        public function __construct($defaults)
        {
            $this->setDefaults($defaults);
        }

        public function GetWebData($subjectCrawlerInstructions)
        {

            $pagination = isset($subjectCrawlerInstructions['pagination']) ? $subjectCrawlerInstructions['pagination'] : null;
            //get all page urls for this subject
            $pageUrls = $this->generatePaginationURLS($pagination, $this->defaults['rootUrl'] . $subjectCrawlerInstructions['url']);
            $rows = array();
            $rowCount = 0;
            foreach ($pageUrls as $url) {
                $dom = new \DOMDocument();
                @$dom->loadHTML(file_get_contents($url));//haters gonna hate
                //parse the rows up and make sure we keep track of how many total rows we have read for this subject
                $result = $this->parseWebPageRows($dom, $subjectCrawlerInstructions['rowToParse'], $rows, $rowCount);

                if ($result == -1){//flagged for hitting maxrows
                    break;
                }
                else{
                    $rowCount += $result;
                }
            }

            return $rows;
        }

        private function parseWebPageRows(\DOMDocument $dom, $instructions, &$rows, $rowCount )
        {
            $xpath = new \DOMXPath($dom);
            $rowNum = isset($instructions['startAtRow']) ? $instructions['startAtRow'] : $this->defaults['startAtRow'];
            $rowsTraversed = 0;
            $maxRows = isset($instructions['maxRows']) ? $instructions['maxRows'] : $this->defaults['maxRows'];
            while (true){
                $colData = array();
                /**
                 * Each row is tested against a set of 'columns' containing one or more xpath queries
                 */
                foreach ($instructions['colsToParse'] as $col) {
                    foreach ($col['element']['xpath'] as $xpathindex => $elXpath){
                        $finalQuery = str_replace( $this->defaults['rowKey'] , $rowNum, $elXpath);
                        $list = $xpath->query($finalQuery);
                        if ($list->length == 0 ){//failed query
                            //short circuit if this xpath query is not optional, and there are no more backup queries for this element
                            if (!isset($col['optional']) && !isset($col['element']['xpath'][$xpathindex + 1])){
                                return $rowsTraversed;
                            }
                        }
                        else {
                            $item = $list->item(0);
                            if (isset($col['element']['useAttribute'])){
                                 $item =  $item->getAttribute($col['element']['useAttribute']);
                            }
                            else {
                                 $item =  trim($item->textContent);
                            }
                            break;//short-circuit from querying again with backup queries, we have our result
                        }
                    }
                    $colData[$col['id']] = $item;//append row to this column (id eg. name)
                }
                $rows[] = $colData;
                $rowNum++;//this pages row traversal counter starting at start row (for xpath, html page relevance)
                $rowsTraversed++; //this pages row traversal counter starting at 0
                if ($rowCount + $rowsTraversed == $maxRows) return -1;//flag if we hit max rows requested
            }
            return $rowsTraversed; //how many rows did we successfully traverse in this page?
        }

        private function generatePaginationURLS($pagination, $rootUrl)
        {
            if (isset($pagination)){
                $pageUrls = array();
                $maxPages = isset($pagination['maxPages']) ? $pagination['maxPages'] : $this->defaults['maxPages'];
                $startAtPage = isset($pagination['startAtPage']) ? $pagination['startAtPage'] : $this->defaults['startAtPage'];
                $pageKey  = isset($pagination['pageKey']) ? $pagination['pageKey'] : $this->defaults['pageKey'];
                for ($i = $startAtPage; $i < $maxPages + 1; $i++) {
                    $pageUrls[] = $rootUrl . str_replace($pageKey, $i, $pagination['url']);
                }
            }
            else {
                $pageUrls = array($rootUrl);
            }
            return $pageUrls;
        }

        function setDefaults($defaults)
        {
            $this->defaults = $defaults;
        }

    }
}