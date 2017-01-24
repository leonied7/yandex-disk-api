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
array \Yandex\Disk\YandexDisk::getProperties(string $path [, array $props = array() [, string $namespace = 'default']);
```

`$path` - путь на яндекс диске

`$props` - массив запрашиваемых свойств, если массив пустой то вернёт стандартные свойства как при запросе содержимого

`$namespace` - наймспэйс для сохранения свойств, не может быть пустым

**Примеры**

```php
//запросим свойства 'test' и 'getlastmodified' файла 'crontab'
$arProperties = $disk->getProperties('/crontab', ['test']);
//вернёт примерно следующий результат
Array
(
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

### Установка/удаление свойств файла/папки
```php
bool \Yandex\Disk\YandexDisk::setProperties(string $path, array $props = array() [, string $namespace = 'default']);
```

`$path` - путь на яндекс диске

`$props` - массив устанавливаемых свойств

`$namespace` - наймспэйс для установки свойств, не может быть пустым

**Примеры**

```php
//Установим для папки 'Музыка' свойство 'myprop1' и удалим свойство 'myprop2'
$disk->setProperties('/Музыка', ['myprop1' => 'myvalue1', 'myprop2' => false]);
```

### Удаление свойств файла/папки
```php
//Обёртка метода setProperties()
bool \Yandex\Disk\YandexDisk::removeProperties(string $path, string|array $props [, $namespace = 'default'])
```

**Примеры**

```php
//Удалим свойства 'myprop2' и 'myprop' у папки 'Музыка'
$disk->removeProperties('/Музыка', ['myprop2', 'myprop']);
```

```php
//Удалим свойство 'myprop1' у файла 'tetx.txt'
$disk->removeProperties('/tetx.txt', 'myprop1');
```