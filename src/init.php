<?php
/**
 * Created by PhpStorm.
 * User: dnkolosov
 * Date: 20.01.2017
 * Time: 15:21
 */
// Подключаем автозагрузчик классов
spl_autoload_register(function ($class)
{
    $arClass = explode('\\', trim($class, '\\'));

    $className = array_pop($arClass);

    $dir = '';

    if($arClass)
        $dir = implode(DIRECTORY_SEPARATOR, $arClass) . DIRECTORY_SEPARATOR;

    $filePath = __DIR__ . DIRECTORY_SEPARATOR . $dir . $className . '.php';

    if(file_exists($filePath))
        include $filePath;
});
