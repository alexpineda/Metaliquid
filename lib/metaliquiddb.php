<?php
/**
 * Created by JetBrains PhpStorm.
 * User: TSB
 * Date: 29/05/11
 * Time: 12:21 PM
 * To change this template use File | Settings | File Templates.
 */
require_once '../lib/mysqldatabase.php';
require_once '../lib/mysqlresultset.php';

function metaliquiddb()
{
    $db = MySqlDatabase::getInstance();
    try {
        $db->connect('localhost','root','','metaliquid');
    }
    catch (Exception $e)
    {
        error_log($e->getMessage());
        return null;
    }
    return $db;
}

