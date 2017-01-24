# PHP библиотека к API Яндекс диска

## Использование

### Подключение
```php
//Подключаем автозагрузчик классов
require_once(__DIR__ . "/init.php");

$disk = new \Yandex\Disk\YandexDisk(TOKEN);
```

### Запрос содержимого каталога
```php
array \Yandex\Disk\YandexDisk::directoryContents(string $path [, int $offset = 0, int $amount = null [, bool $thisFolder = false]]);
```

`$path` - путь на яндекс диске

`$offset` - отступ

`$amount` - количество элементов

`$thisFolder` - оставлять запрашиваемую папку в ответе

**Примеры**

```php
//получить содержимое папки 'Музыка'
$dir = $disk->directoryContents('/Музыка');
```

```php
// получим из содержимого папки 'backup' первые 5 элементов  
$dir = $disk->directoryContents('/backup', 0, 5);
```

### Запрос свободного/занятого места
```php
array|string \Yandex\Disk\YandexDisk::spaceInfo(string $info = '');
```

`$info` - запрашиваемые данные. Возможные значения `'available'` / `'used'` / `''`
 
 **Примеры**
 
 ```php
// получим свободное и занятое место на диске
$info = $disk->spaceInfo();
//вернёт примерно следующий результат
Array
(
    [quota-used-bytes] => 160561779981
    [quota-available-bytes] => 949687266035
)
```

```php
//получим только занятое место
$info = $disk->spaceInfo('used');
//вернёт строковое значение
```

### Получение свойств файла/папки
```php
array \Yandex\Disk\YandexDisk::getProperties(string $path [, array $props = array()]);
```

`$path` - путь на яндекс диске

`$props` - массив запрашиваемых свойств, если массив пустой то вернёт стандартные свойства как при запросе содержимого

**Примеры**

```php
//запросим свойства 'test' и 'getlastmodified' файла 'crontab'
$arProperties = $disk->getProperties('/crontab', ['test', 'getlastmodified']);
//вернёт примерно следующий результат
Array
(
    [getlastmodified] => Wed, 09 Sep 2015 10:15:59 GMT
)
//свойство 'test' не найдено у файла, поэтому исключено из результата
```

```php
//запросим стандартные свойства папки 'backup'
$arProperties = $disk->getProperties('/backup');
//вернёт примерно следующий результат
Array
(
    [resourcetype] =>
    [getlastmodified] => Wed, 09 Sep 2015 10:09:45 GMT
    [displayname] => backup
    [creationdate] => 2015-09-09T10:09:45Z
)
```