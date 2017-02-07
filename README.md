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
bool|array \Yandex\Disk\YandexDisk::setProperties(string $path, array $props = array() [, string $namespace = 'default']);
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

`$path` - путь на яндекс диске

`$props` - массив удаляемых свойств или имя свойства для удаления

`$namespace` - наймспэйс для установки свойств, не может быть пустым

**Примеры**

```php
//Удалим свойства 'myprop2' и 'myprop' у папки 'Музыка'
$disk->removeProperties('/Музыка', ['myprop2', 'myprop']);
```

```php
//Удалим свойство 'myprop1' у файла 'tetx.txt'
$disk->removeProperties('/tetx.txt', 'myprop1');
```

### Публикация файла/папки
```php
//Обёртка метода setProperties()
bool|string \Yandex\Disk\YandexDisk::startPublish(string $path);
```

`$path` - путь на яндекс диске

**Примеры**

```php
//если папка/файл найден, вернёт ссылку, иначе false
if($url = $disk->startPublish('/Музыка'))
    print_r($url);
```

### Снятие публикации файла/папки
```php
//Обёртка метода removeProperties()
bool \Yandex\Disk\YandexDisk::stopPublish(string $path);
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
bool|string \Yandex\Disk\YandexDisk::checkPublish(string $path);
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
bool|string \Yandex\Disk\YandexDisk::getLogin();
```

**Примеры**

```php
//запрос логина
$login = $disk->getLogin();
echo $login;
```

### Получение превью картинки
```php
bool|mixed \Yandex\Disk\YandexDisk::getPreviewImage(string $path[[, string $size = 'XXXS',] bool|resource $stream = false]);
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
bool \Yandex\Disk\YandexDisk::getFile(string $path, resource $stream[[, bool|int $from = false], bool|int $to = false]);
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
bool \Yandex\Disk\YandexDisk::putFile(string $path, resource $stream);
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
bool \Yandex\Disk\YandexDisk::createDir(string $path);
```

`$path` - путь на яндекс диске

**Примеры**

```php
//создадим папку test2 в корне
$disk->createDir('/test2');
```

### Копирование файла/папки
```php
bool \Yandex\Disk\YandexDisk::copy(string $path, string $destination[, bool $overwrite = true]);
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
bool \Yandex\Disk\YandexDisk::move(string $path, string $destination[, bool $overwrite = true]);
```

`$path` - путь на яндекс диске от куда копировать

`$destination` - путь на яндекс диске куда копировать

`$overwrite` - перезапись

**Примеры**

```php
//перенесём и переименнуем `test1/test.jpg` в 'test2/file.jpg'
$disk->move('test1/test.jpg', 'test2/file.jpg');
```