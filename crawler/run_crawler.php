<?php
/**
 * @author Alex Pineda <alex@brainyweb.ca>
 * @package MetaLiquid
 *
 * Run this script to get the most up to date data (placed into crawler/data) using the instruction set files in crawler/instructions
 */
require_once '../lib/JSON.class.php';
require_once '../lib/metaliquiddb.php';
require_once 'Crawler.class.php';


/**
 * A database connection to store present data, not currently in use but will allow historical data
 */
$db = metaliquiddb();

/**
 * Load configuration
 */
$config = JSON::load('config.json');
$crawler = new MetaLiquid\Crawler($config['defaults']);
$templates = JSON::load($config['instructions']['path'] . $config['instructions']['templatesFile']);

/**
 * Enumerate the instruction set json files
 */
$files = scandir($config['instructions']['path']);
array_shift($files);//get rid of . and ..
array_shift($files);

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
    $subjectCrawlerInstructions = JSON::load($filename);
    JSON::mergeOnTemplate($subjectCrawlerInstructions, $templates);

    /**
     * The core crawling functionality, fills our data array that we can then store as json
     */
    $data = $crawler->GetWebData($subjectCrawlerInstructions);

    /**
     * Save to results to the data file
     */
    $f = fopen($dataFilename, "w");
    fwrite($f,json_encode($data));
    fclose($f);

    if ($db = null){//no db for the moment
        $id = mysql_escape_string($subjectCrawlerInstructions["subjectId"]);
        $title = mysql_escape_string($subjectCrawlerInstructions["title"]);
        $time = time();
        $date = date("Y-m-d");
        $data = serialize($data);
        try {
            $id = $db->insert("INSERT INTO subject VALUES ('$id', 'time', $date, $data, $title);");
        }
        catch (Exception $e) {
            die($e->getMessage());
        }
    }
}



?>

