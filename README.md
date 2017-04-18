# PHP библиотека к API Яндекс диска

## Установка

### Composer

```php
curl -s https://getcomposer.org/installer | php

composer require leonied7/yandex-disk-api:dev-master
```

## Использование

### Подключение
```php
//Подключаем автозагрузчик
require_once __DIR__ . "/vendor/autoload.php";

$disk = new Yandex\Disk(TOKEN);
```

### Запрос содержимого каталога
```php
array \Yandex\Disk::directoryContents(string $path [, Yandex\Common\PropPool $props = null [, int $offset = 0, int $amount = null [, bool $thisFolder = false]]]);
```

`$path` - путь на яндекс диске

`$props` - объект запрашиваемых свойств, если не указан, то выбираются стандартные свойства

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
$dir = $disk->directoryContents('/backup', null, 0, 5);
```

### Запрос свободного/занятого места
```php
array \Yandex\Disk::spaceInfo();
```
 
 **Примеры**
 
 ```php
// получим свободное и занятое место на диске
$info = $disk->spaceInfo();
//вернёт примерно следующий результат
Array
(
    [used] => 160561779981
    [available] => 949687266035
)
```


### Получение свойств файла/папки
```php
array \Yandex\Disk::getProperties(string $path, Yandex\Common\PropPool $props);
```

`$path` - путь на яндекс диске

`$props` - объект свойств

**Примеры**

```php
//запросим свойства 'displayname', 'creationdate' с дефолтным namespace, 'quota-limit-bytes' с namespace 'urn:yandex:disk:meta' и 'test' с namespace 'test1' для папки '/backup_test/' 
$props = new \Yandex\Common\PropPool(array('displayname', 'creationdate'));

$props->set('quota-limit-bytes', 'urn:yandex:disk:meta')
    ->set('test', 'test1');

print_r($disk->getProperties('/backup_test/', $props));

//результат будет примерно следующий
[found] => Array
(
   [0] => Array
   (
       [name] => quota-limit-bytes
       [value] => 1110249046016
       [namespace] => urn:yandex:disk:meta
   )
   [1] => Array
   (
       [name] => displayname
       [value] => backup_test
       [namespace] => DAV:
   )
   [2] => Array
   (
       [name] => creationdate
       [value] => 2017-04-05T05:39:44Z
       [namespace] => DAV:
   )
)
[notFound] => Array
(
   [0] => Array
   (
       [name] => test
       [value] =>
       [namespace] => test1
   )
)

```

### Установка/удаление свойств файла/папки
```php
array \Yandex\Disk::changeProperties(string $path, Yandex\Common\PropPool $props);
```

`$path` - путь на яндекс диске

`$props` - объект свойств

**Примеры**

```php
//Установим для папки 'backup_test' свойство 'test' и 'ttt' с namespace 'test' и удалим свойство 'test' с namespace 'test1'
$props = new \Yandex\Common\PropPool(array(array('name' => 'test', 'value' => 'test1'), array('name' => 'ttt', 'value' => '123')), 'test');

$props->set('test', 'test1');

print_r($disk->changeProperties('/backup_test/', $props));

//результат
Array
(
   [HTTP/1.1 200 OK] => Array
   (
       [0] => Array
       (
           [name] => test
           [value] =>
           [namespace] => test1
       )
       [1] => Array
       (
           [name] => ttt
           [value] =>
           [namespace] => test
       )
       [2] => Array
       (
           [name] => test
           [value] =>
           [namespace] => test
       )
   )
)
```

```php
если попытаться изменить дефолтное свойства яндекса, то он вернет результат с другим ключом массива, отличным от 'HTTP/1.1 200 OK'

так же значения могут изменяться частично, в таком случае будет несколько массивов с соответствуюшими ключами ошибок
```

### Публикация файла/папки
```php
//Обёртка метода changeProperties()
string \Yandex\Disk::startPublish(string $path);
```

`$path` - путь на яндекс диске

**Примеры**

```php
//если папка/файл найден, вернёт ссылку
if($url = $disk->startPublish('/Музыка'))
    print_r($url);
```

### Снятие публикации файла/папки
```php
//Обёртка метода changeProperties()
bool \Yandex\Disk::stopPublish(string $path);
```

`$path` - путь на яндекс диске

**Примеры**

````php
//при удачном снятии публикации вернёт true, иначе false
$disk->stopPublish('/Музыка');
````

### Проверка публикации файла/папки
```php
//Обёртка метода getProperties()
bool|string \Yandex\Disk::checkPublish(string $path);
```

`$path` - путь на яндекс диске

**Примеры**

```php
//если файл/папка опубликован вернёт ссылку, иначе false
if($url = $disk->checkPublish('/Музыка'))
    print_r($url);
```

### Получение логина пользователя
```php
string \Yandex\Disk::getLogin();
```

**Примеры**

```php
//запрос логина
$login = $disk->getLogin();
echo $login;
```

### Получение превью картинки
```php
bool|mixed \Yandex\Disk::getPreviewImage(string $path[[, string $size = 'XXXS',] bool|resource $stream = false]);
```

`$path` - путь на яндекс диске

`$size` - размер превью

`$stream` - поток открытого файла

**Примеры**

```php
// получение картинки 100х100 возвращает весь результат в переменную
$content = $disk->getPreviewImage('/test.jpg', '100x100');

file_put_contents('/home/upload/tmp/file.jpg', $content);
```

```php
// получение с использованием потока, в этом случае в переменную возвращается true|false
$file = fopen('/home/upload/tmp/test.jpg', 'w');

if($disk->getPreviewImage('/test.jpg', 'XL', $file))
    echo 'успешно';

fclose($file);
```

### Скачивание файла
```php
bool \Yandex\Disk::getFile(string $path, resource $stream[[, bool|int $from = false], bool|int $to = false]);
```

`$path` - путь на яндекс диске

`$stream` - поток файла

`$from` - с какого байта запрашивать данные

`$to` - до какого байта

**Примеры**

```php
//получение файла
$file = fopen('/home/upload/tmp/test.jpg', 'a');

if($disk->getFile('/test.jpg', $file))
    echo 'Это успех!';

fclose($file);
```

```php
//докачивание файла
$localPath = '/home/upload/tmp/Navicat.rar';
$file = fopen($localPath, 'a');

$dir = $disk->getFile('/Navicat.rar', $file, filesize($localPath));

fclose($file);
```

### Загрузка файла
```php
bool \Yandex\Disk::putFile(string $path, resource $stream);
```

`$path` - путь на яндекс диске

`$stream` - поток файла

**Примеры**

```php
//загрузка файла

$file = fopen('/home/upload/tmp/test.jpg', 'r');

if($disk->putFile('/folder/test.jpg', $file))
    echo 'Это успех!';

fclose($file);
```

```php
//загрузка файла, через ssh подключение

//подключаемся по ssh
$connect = ssh2_connect('host', 22);

$authorize = ssh2_auth_password($connect, 'login', 'password');

$auth = ssh2_sftp($connect);

$file = fopen("ssh2.sftp://" . $auth . "/home/upload/tmp/test.jpg", 'r');

if($disk->putFile('/folder/test.jpg', $file))
    echo 'Это успех!';

fclose($file);

//закрываем подключение по ssh
fclose($connect);
```

### Создание каталога
```php
bool \Yandex\Disk::createDir(string $path);
```

`$path` - путь на яндекс диске

**Примеры**

```php
//создадим папку test2 в корне
$disk->createDir('/test2');
```

### Копирование файла/папки
```php
bool \Yandex\Disk::copy(string $path, string $destination[, bool $overwrite = true]);
```

`$path` - путь на яндекс диске от куда копировать

`$destination` - путь на яндекс диске куда копировать

`$overwrite` - перезапись

**Примеры**

```php
//копируем `test1/test.jpg` в 'test2/test.jpg'
$disk->copy('test1/test.jpg', 'test2/test.jpg');
```

### Перемещение/переименование файла/папки
```php
bool \Yandex\Disk::move(string $path, string $destination[, bool $overwrite = true]);
```

`$path` - путь на яндекс диске от куда копировать

`$destination` - путь на яндекс диске куда копировать

`$overwrite` - перезапись

**Примеры**

```php
//перенесём и переименнуем `test1/test.jpg` в 'test2/file.jpg'
$disk->move('test1/test.jpg', 'test2/file.jpg');
```

### Удаление файла/папки
```php
bool \Yandex\Disk::delete(string $path);
```

`$path` - путь на яндекс диске от куда копировать

**Примеры**

```php
//удалим папку `test1` со всем содержимым
$disk->delete('test1');
```