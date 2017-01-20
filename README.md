# PHP библиотека к API Яндекс диска

## Использование

```
//Подключаем автозагрузчик классов
require_once(__DIR__ . "/init.php");

$disk = new \Yandex\Disk\YandexDisk(TOKEN);

$dir = $disk->directoryContents('/backup/');
```