<?php
/**
 * @author Alex Pineda <alex@brainyweb.ca>
 * @package MetaLiquid
 *
 * Run this script to get the most up to date data (placed into crawler/data) using the instruction set files in crawler/instructions
 */
require_once '../lib/JSON.class.php';
require_once '../lib/metaliquiddb.php';
require_once '../lib/Logger.class.php';
require_once 'Crawler.class.php';


/**
 * A database connection to store present data, not currently in use but will allow historical data
 */
$db = metaliquiddb();

/**
 * Load configuration
 */
$config = JSON::load('config.json');

/**
 * Load up the templates file, will be used by many instruction set files
 */
$templates = JSON::load($config['instructions']['path'] . $config['instructions']['templatesFile']);

/**
 * Instantiate the logger, will help us out
 */
$logger = new Logger($config['log-file']);
$logger->Write("Hello - " . date("Y-m-d H:i:s") );
/**
 * Enumerate the instruction set json files
 */
$files = scandir($config['instructions']['path']);
array_shift($files);//get rid of . and ..
array_shift($files);

/**
 * Instantiate the crawler object
 */
$crawler = new Crawler($config['defaults'], $logger);

foreach ($files as $file)
{
    /**
     * Do not treat the template instruction set file as a real instruction set file
     */
    if ($file == $config["instructions"]["templatesFile"]) continue;

    /**
     * Input instruction set file with appropriate path
     */
    $filename = $config["instructions"]["path"] . $file;
    /**
     * Output data file
     */
    $dataFilename = $config["data"]["path"] . $file;

    /**
     * Load the current instruction set and merge any template instruction set with it
     */
    $logger->Write("Loading instructions from $filename, merging any templates");
    $subjectCrawlerInstructions = JSON::load($filename);
    JSON::mergeOnTemplate($subjectCrawlerInstructions, $templates);


    /**
     * The core crawling functionality, fills our data array that we can then store as json
     */
    $logger->Write("Initializing crawling");
    $data = $crawler->GetWebData($subjectCrawlerInstructions);


    
    /**
     * Save to results to the data file
     */
    $logger->Write("Writing data to disk : " + $dataFilename );
    $f = fopen($dataFilename, "w");
    fwrite($f,json_encode($data));
    fclose($f);


    if ($db = null){//no db for the moment, maybe use couchdb since we're storing json already
        $logger->Write("Writing to DB");
        $id = mysql_escape_string($subjectCrawlerInstructions["subjectId"]);
        $title = mysql_escape_string($subjectCrawlerInstructions["title"]);
        $time = time();
        $date = date("Y-m-d");
        $data = serialize($data);
        try
            {
            $id = $db->insert("INSERT INTO subject VALUES ('$id', 'time', $date, $data, $title);");
            }
        catch (Exception $e)
            {
            die($e->getMessage());
            }
    }
}

$logger->Write("Complete");

?>


