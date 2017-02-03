<?php
/**
 * Created by PhpStorm.
 * User: dnkol
 * Date: 21.01.2017
 * Time: 15:55
 */

require_once(__DIR__ . "/init.php");

$disk = new \Yandex\Disk\YandexDisk('AQAAAAAPLi2rAAK8M41DfJDf_0a1o2bk_5JOCG0');


$file = fopen('/home/bitrix/www/upload/tmp/test.jpg', 'a');

$dir = $disk->getFile('/test.jpg', $file);

fclose($file);

print_r($dir);