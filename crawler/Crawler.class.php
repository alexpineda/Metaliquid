<?php
/**
 * @author Alex Pineda <alex@brainyweb.ca>
 * @package MetaLiquid
 */

    class Crawler
    {
        /**
         * @var array
         */
        private $defaults;
        /**
         * @var Logger
         */
        private $logger;

        public function __construct($defaults, Logger $logger)
        {
            $this->setDefaults($defaults);
            $this->logger = $logger;
        }

        /**
         * @param array $subjectCrawlerInstructions
         */
        public function GetWebData($subjectCrawlerInstructions)
        {
            $subjectName = "{$subjectCrawlerInstructions['title']} ({$subjectCrawlerInstructions['subjectId']})";
            $this->logger->Write( "Get web data for $subjectName" );
            
            $pagination = isset($subjectCrawlerInstructions['pagination']) ? $subjectCrawlerInstructions['pagination'] : null;

            /**
             * The pagination object contains a max pages limit, so we generate an array of urls upto that limit.
             * This does not mean that we make it all the way to those pages when parsing.
             */
            $pageUrls = $this->generatePaginationURLS( $pagination, $this->defaults['rootUrl'] . $subjectCrawlerInstructions['url'] );
            $this->logger->Write( count( $pageUrls ) . " page urls generated" );
            $rows = array();
            $rowCount = 0;
            /**
             * No side effects, just for logging information
             */
            $pageCount = 0;

            /**
             * Open up every url and add to the rows array with the parsed data 
             */
            foreach ($pageUrls as $url)
                {

                $dom = new DOMDocument();
                $dom->loadHTMLFile( $url );
                /**
                 * Any rows parsed will be added to the $rows array (passed byref)
                 * Result will be either the number of rows parsed or -1 for hitting max num of rows (set in config)
                */
                $this->logger->Write( "Parsing $url with $rowCount rows already parsed" );
                $result = $this->parseXmlRows( $dom, $subjectCrawlerInstructions['rowToParse'], $rows, $rowCount );
                $pageCount ++;
                    
                //flagged for hitting maxrows
                if ($result == -1)
                    {
                    $this->logger->Write( "Maxed out rows" );
                    break;
                    }
                else
                    {
                    $rowCount += $result;
                    $this->logger->Write( "$result rows parsed for $url" );
                    }
                }

            $this->logger->Write( "$pageCount pages parsed, and " . count( $rows ) . " rows read for $subjectName" );

            /**
             * Compose the final object. I don't know if I like this in here however for the time being it's fitting.
             */
            $subjectJson = array();
            $subjectJson['id'] = $subjectCrawlerInstructions['subjectId'];
            $subjectJson['title'] = $subjectCrawlerInstructions['title'];
            $subjectJson['sortDeterminants'] = isset($subjectCrawlerInstructions['sortDeterminants']) ? $subjectCrawlerInstructions['sortDeterminants'] : array();
            $subjectJson['collection'] = $rows;
            return $subjectJson;
        }

        /**
         * @param \DOMDocument $dom The page we are parsing
         * @param array $instructions These are the rowsToParse elements
         * @param array $rows running Collection of result data
         * @param int $rowCount Number of rows collected
         * @return int Either a tally of the rows parsed, or -1 if hit max rows
         */
        private function parseXmlRows( DOMDocument $dom, $instructions, &$rows, $rowCount )
        {
            $xpath = new DOMXPath($dom);
            $startAtRow = isset($instructions['startAtRow']) ? $instructions['startAtRow'] : $this->defaults['startAtRow'];
            $rowKey = isset($instructions['rowKey']) ? $instructions['rowKey'] : $this->defaults['rowKey'];
            $rowsTraversed = 0;
            $maxRows = isset($instructions['maxRows']) ? $instructions['maxRows'] : $this->defaults['maxRows'];
            
            /**
             * Traverse as many rows as we can in the page until an xpath query for a row column fails
             */
            while (true){
                /**
                 * We are in a new row, so reset the column data for this row
                 */
                $colData = array();
                /**
                 * Each row is tested against a set of 'columns' containing one or more xpath queries
                 */
                foreach ($instructions['colsToParse'] as $colIns) {
                    foreach ($colIns['element']['xpath'] as $xpathindex => $elXpath){
                        /**
                         * Generate the xpath query from one of the colsToParse element -> xpath element member, and
                         * replace the row key with the $rowNum we are currently at
                         */
                        $finalQuery = str_replace( $rowKey , $rowsTraversed + $startAtRow, $elXpath);
                        $list = $xpath->query($finalQuery);

                        /**
                         * If the query returns zero, find out if there are any more queries we can try or if this col was optional,
                         * if not, bail. This is considered EOF.
                         */
                        if ($list->length == 0 )
                            {
                            if (!isset($colIns['optional']) && !isset($colIns['element']['xpath'][$xpathindex + 1]))
                                {
                                return $rowsTraversed;
                                }
                            }
                        /**
                         * The query was successful, now collect the text data
                         */
                        else
                            {
                            $item = $list->item(0);
                            /**
                             * If the query demands the use of the attribute get that content, otherwise the element text content
                             */
                            if (isset($colIns['element']['useAttribute']))
                                {
                                 $item =  $item->getAttribute($colIns['element']['useAttribute']);
                                }
                            else
                                {
                                 $item =  trim($item->textContent);
                                }
                            /**
                             * Break out of the xpath query loop, we don't care for any redundancy.
                             * The first (and possibly only) xpath succeeded.
                             */
                            break;
                            }
                    }
                    /**
                     * Add the text data using the column id as key
                     */
                    $colData[$colIns['id']] = $item;//append row to this column (id eg. name)
                }
                /**
                 * Add a new row using the collected $colData object
                 */
                $rows[] = $colData;
                /**
                 * A counter for the number of rows iterated through
                 */
                $rowsTraversed++;
                if ($rowCount + $rowsTraversed == $maxRows) return -1;//flag if we hit max rows requested
            }
            return $rowsTraversed;
        }

        private function generatePaginationURLS($pagination, $rootUrl)
        {
            if (isset($pagination)){
                $pageUrls = array();
                $maxPages = isset($pagination['maxPages']) ? $pagination['maxPages'] : $this->defaults['maxPages'];
                $startAtPage = isset($pagination['startAtPage']) ? $pagination['startAtPage'] : $this->defaults['startAtPage'];
                $pageKey  = isset($pagination['pageKey']) ? $pagination['pageKey'] : $this->defaults['pageKey'];
                for ($i = $startAtPage; $i < $maxPages + 1; $i++) {
                    $pageUrls[] = $rootUrl . str_replace( $pageKey, $i, $pagination['url'] );
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
