# PHP библиотека к API Яндекс диска
## Введение
Неофициальное PHP SDK для сервиса Яндекс.Диск
## Список изменений
27/12/2018
* полностью переписана логика работы с api
* упращена работы с SDK
* обновлено README
## Требования
* PHP 5.6+
* Расширение php_curl
## Установка
### Composer
```php
composer require leonied7/yandex-disk-api:dev-master
```
пример подключения:
```php
require_once __DIR__ . "/vendor/autoload.php";
```
## Тесты
Запуск тестов из корня библиотеки:
```php
vendor/phpunit/phpunit/phpunit --configuration phpunit.xml
```

## Описание
### Введение
SDK для работы использует [WebDAV API Яднекс Диска](https://tech.yandex.ru/disk/webdav/). Для работы необходим OAuth-токен(например, AQACc1234LDE2f_123UIbouFHzfxxcvDI), который необходимо получить самостоятельно:
* зарегистрировать приложение и самостоятельно получить токен https://oauth.yandex.ru/

OAuth-токен должен иметь разрешённые права "**Яндекс.Диск WebDAV API**"
### Возможности
* Работа с папками на Яндекс.Диске (создание, копирование, перемещение, удаление, публикация и т.д.)
* Работа с файлами на Яндекс.Диске (создание, загрузка, скачивание, копирование, перемещение, удаление, публикация и т.д.)
* Потоковая загрузка и скачивание файлов
* Фрагментное скачивание файлов
### Инициализация
```php
use \Leonied7\Yandex\Disk;
$yandexDisk = new Disk('OAuth-токен');
```
### Использование
* `\Leonied7\Yandex\Disk` - используется для работы с диском, запрашивает основную информацию о диске и клиенте, а так же помогает работать с файлами и папками
* `\Leonied7\Yandex\Disk\Item\File` - используется для работы с файлом
    ```php
    /** @var \Leonied7\Yandex\Disk\Item\File $file */
    $yandexDisk->file('/path/to/file/');
    ```
* `\Leonied7\Yandex\Disk\Item\Directory` - используется для работы с директорией
    ```php
    /** @var \Leonied7\Yandex\Disk\Item\Directory $directory */
    $directory = $yandexDisk->directory('/path/to/directory/');
    ```
### Используемые объекты
* [\Leonied7\Yandex\Disk\Entity\Result](https://github.com/leonied7/yandex-disk-api/wiki/Result) - после выполнения любого запроса к Яндекс.Диску можно получить информацию о результате

* \Leonied7\Yandex\Disk\Entity\Collection
    * [\Leonied7\Yandex\Disk\Collection\PropertyCollection](https://github.com/leonied7/yandex-disk-api/wiki/Property-Collection) - коллекция свойств
    * [\Leonied7\Yandex\Disk\Collection\PropertyFail](https://github.com/leonied7/yandex-disk-api/wiki/Property-Fail-Collection) - коллекция ошибочных свойств свойств
* [\Leonied7\Yandex\Disk\Model\Property](https://github.com/leonied7/yandex-disk-api/wiki/Property)
    * [\Leonied7\Yandex\Disk\Property\Immutable](https://github.com/leonied7/yandex-disk-api/wiki/Immutable-Property) - неизменяемое свойство
    * [\Leonied7\Yandex\Disk\Property\Mutable](https://github.com/leonied7/yandex-disk-api/wiki/Mutable-Property) - изменяемое свойство
    
* \Leonied7\Yandex\Disk\Item\Item
    * [\Leonied7\Yandex\Disk\Item\File](https://github.com/leonied7/yandex-disk-api/wiki/File-Item) - объект файла
    * [\Leonied7\Yandex\Disk\Item\Directory](https://github.com/leonied7/yandex-disk-api/wiki/Directory-Item) - объект директории
* [\Leonied7\Yandex\Disk\Model\Decorator](https://github.com/leonied7/yandex-disk-api/wiki/Decorator-Model)
    * \Leonied7\Yandex\Disk\Decorator\CurrentElement - возвращает данные о элементе с входных путём  
    * \Leonied7\Yandex\Disk\Decorator\CurrentElementCollection - возвращает данные о коллекции элемента с входных путём  
    * \Leonied7\Yandex\Disk\Decorator\CurrentElementCollectionItem - возвращает данные о свойстве коллекции элемента с входных путём
    * \Leonied7\Yandex\Disk\Decorator\CurrentElementCollectionItemValue - возвращает значение свойства коллекции элемента с входных путём
    * \Leonied7\Yandex\Disk\Decorator\CurrentElementFailCollection - возвращает массив ошибочных коллекций элемента с входных путём
    * \Leonied7\Yandex\Disk\Decorator\ExplodeData - возвращает разбитую строку на массив типа "ключ => значение"
* [\Leonied7\Yandex\Disk\Model\Stream](https://github.com/leonied7/yandex-disk-api/wiki/Stream-Model)
    * \Leonied7\Yandex\Disk\Stream\File - осуществляет работу с потоком файла, используется для записи/чтения файла
## Использование
### [Запрос информации о пользователе](https://tech.yandex.ru/disk/doc/dg/reference/userinfo-docpage/)
```php
$info = $yandexDisk->getInfo();
//вернёт примерно следующий результат
Array
(
    [uid] => xxxxxxxxx
    [login] => login
    [fio] => fio
    [firstname] => firstname
    [lastname] => lastname
    [upload_concurrency] => 5
    [datasync_db_prefix] => 
    [is_b2b] => false
)
```
### [Запрос свободного/занятого места](https://tech.yandex.ru/disk/doc/dg/reference/space-request-docpage/)
```php
/** @var \Leonied7\Yandex\Disk\Collection\PropertyCollection $spaceCollection */
$spaceCollection = $yandexDisk->spaceInfo();
//поиск в коллекции свойство с имененем 'quota-available-bytes'
/** @var \Leonied7\Yandex\Disk\Property\Immutable $available */
$available = $spaceCollection->find('quota-available-bytes');
echo $available->getValue(); //свободное места

/** @var \Leonied7\Yandex\Disk\Property\Immutable $used */
$used = $spaceCollection->find('quota-used-bytes');
echo $used->getValue(); //занятое места
```

### [Загрузка файла (Применимо только для файлов)](https://tech.yandex.ru/disk/doc/dg/reference/put-docpage/)
```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->upload(new Disk\Stream\File('/path/to/local/file', Disk\Stream\File::MODE_READ)); //bool
```

### [Скачивание файла (Применимо только для файлов)](https://tech.yandex.ru/disk/doc/dg/reference/get-docpage/)
SDK поддерживает скачивание файлов несколькими способами:
1. Потоковое скачивание

    ```php
    /** @var Disk\Item\File $file */
    $file = $yandexDisk->file('/path/to/file/');
    $file->download(new Disk\Stream\File('/path/to/local/file', Disk\Stream\File::MODE_WRITE)); //bool
    ```
2. Потоковое скачивание частями

    ```php
    /** @var Disk\Item\File $file */
    $file = $yandexDisk->file('/path/to/file/');
    //скачивание первых 5 байт
    $file->download(new Disk\Stream\File('/path/to/local/file', Disk\Stream\File::MODE_WRITE), 0, 5); //bool
    //скачивание с 6 байта до конца 
    $file->download(new Disk\Stream\File('/path/to/local/file', Disk\Stream\File::MODE_WRITE_APPEND), 6); //bool
    ```
3. Скачивание без потока

    ```php
    /** @var Disk\Item\File $file */
    $file = $yandexDisk->file('/path/to/file/');
    $file->download(); //bool
    // получение последнего результата запроса
    $result = Disk\Collection\ResultList::getInstance()->getLast();
    file_put_contents('/path/to/local/file', $result->getActualResult());
    ```
    
### [Получение превью картинок (Применимо только для файлов)](https://tech.yandex.ru/disk/doc/dg/reference/preview-docpage/)
Первым параметром передаётся размер превью, может быть применён любой из документации
```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->getPreview('S', new Disk\Stream\File('/path/to/local/file/', Disk\Stream\File::MODE_WRITE));
```
> Превью может быть получена потоком, либо без потока

### [Создание директории (Применимо только для директорий)](https://tech.yandex.ru/disk/doc/dg/reference/mkcol-docpage/)
```php
/** @var Disk\Item\Directory $directory */
$directory = $yandexDisk->directory('/path/to/directory/');
$directory->create(); // bool
```

### [Получение содержимого директории (Применимо только для директорий)](https://tech.yandex.ru/disk/doc/dg/reference/contains-request-docpage/)
```php
/** @var Disk\Item\Directory $directory */
$directory = $yandexDisk->directory('/path/to/directory/');
/** @var Disk\Item\Item[] $arChild */
$arChild = $directory->getChildren();
/** @var Disk\Item\Item $child */
foreach ($arChild as $child) {
    if ($child->isDirectory()) {
        /** @var Disk\Item\Directory $directory */
        $directory = $child;
        //работа с директорией
    } else {
        /** @var Disk\Item\File $file */
        $file = $child;
        //работа с файлом
    }
}
```

Так же первым параметром можно передать объект типа [\Leonied7\Yandex\Disk\Collection\PropertyCollection](https://github.com/leonied7/yandex-disk-api/wiki/Property-Collection) для получения свойств для всех элементов.

Так же 2 и 3 параметром можно указать `offset(смещение)` и `amount(количество)` - для получение только необходимого диапозона элементов.

### [Проверка существования элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/property-request-docpage/)
**Пример написан для файла, но метод так же применим для директории**
```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->has(); // bool
```
> Так как для проверки существования используется метод запроса свойств, то по умолчанию Яндекс.Диск отдаёт свойства. 
При вызове метода `has()` можно передать объект типа [\Leonied7\Yandex\Disk\Collection\PropertyCollection](https://github.com/leonied7/yandex-disk-api/wiki/Property-Collection). 

Пример:
```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$collection = new Disk\Collection\PropertyCollection();
$collection
    ->add('getcontenttype', Disk\Model\Property::IMMUTABLE_NAMESPACES['dav']) //запрос типа содержимого
    ->add('displayname', Disk\Model\Property::IMMUTABLE_NAMESPACES['dav']) //запрос имени содержимого
    ->add('myprop', 'mynamespace'); //полученис своего свойства
$file->has($collection); // bool
```
> Если объект не передаётся, то выбираются все доступные свойства автоматически.

**Получить пришедшие свойства можно следущим образом:**
```php
/** @var Disk\Collection\PropertyCollection $collection */
$collection = $file->getProperties();
```
или
```php
/** @var Disk\Collection\PropertyCollection $collection */
$collection1 = Disk\Collection\ResultList::getInstance()->getLast()->getResult();
```
> Результат будет хранить только успешно полученные свойства.

Для получения ошибочных свойств
```php
/** @var Disk\Collection\PropertyFail[] $failCollections */
$failCollections = Disk\Collection\ResultList::getInstance()->getLast()->getDecorateResult(new Disk\Decorator\CurrentElementFailCollection($file->getPath()));
foreach ($failCollections as $failCollection) {
    $failCollection->getStatus(); //получение статуса ответа от Яндекс.Диска для коллекции
    //так же можно применять такие же методы что и для Disk\Property\Immutable
}
```

### [Копирование элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/copy-docpage/)
**Пример написан для файла, но метод так же применим для директории**
```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->copy('/path/to/copy/'); // bool
```
> По стандарту если файл уже существует по назначения, то он будет перезаписан. 
Для запрета перезаписи, необходимо передать вторым параметром `false`

### [Перемещение элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/move-docpage/)
**Пример написан для файла, но метод так же применим для директории**
```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->move('/path/to/move/'); // bool
```
> По стандарту если файл уже существует по назначения, то он будет перезаписан. 
Для запрета перезаписи, необходимо передать вторым параметром `false`

### [Удаление элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/delete-docpage/)
**Пример написан для файла, но метод так же применим для директории**
```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->delete(); // bool
```

### [Загрузка свойств элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/property-request-docpage/)
**Пример написан для файла, но метод так же применим для директории**

```php
/** @var \Yandex\Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
//создаём коллекцию и добавляем в неё 3 свойства
$propertyCollection = new \Yandex\Disk\Collection\Property();
$propertyCollection
    ->add('myprop', 'mynamespace')
    ->add('propmy', 'mynamespace')
    ->add('propprop', 'mynamespace');
    
/** @var \Yandex\Disk\Collection\Property $loadCollection */
$loadCollection = $file->loadProperties($propertyCollection);
/** @var \Yandex\Disk\Collection\Property $property */
foreach ($loadCollection as $property) {
    // работаем со свойствами
}
```
> Ранее успешно загруженные свойства можно получить с помощью `$file->getProperties();`

> Результат будет хранить только успешно полученные свойства.

Для получения ошибочных свойств
```php
/** @var \Yandex\Disk\Collection\PropertyFail[] $convertedResult */
$failCollections = $file->getLastResult()->getDecorateResult(new \Yandex\Disk\Decorator\CurrentElementFailCollection($file->getPath()));
foreach ($failCollections as $failCollection) {
    $failCollection->getStatus() //получение статуса ответа от Яндекс.Диска
}
```
> полное описание ошибочный коллекций [\Leonied7\Yandex\Disk\Collection\PropertyFail](https://github.com/leonied7/yandex-disk-api/wiki/Property-Fail-Collection)
### Получение существующих свойств (Применимо для файла/директории)
**Пример написан для файла, но метод так же применим для директории**
```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
/** @var Disk\Collection\PropertyCollection $propertyCollection */
$propertyCollection = $file->getExistProperties();
```
> **Внимание!!!** свойства приходят без значений и не могут быть получены через `$file->getProperties();`

### [Изменение свойства элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/proppatch-docpage/)
**Пример написан для файла, но метод так же применим для директории**

Есть два способа изменения свойств у элемента:
1. Изменение переданных свойств

    Добавляем свойства `myprop` и `propmy` с namespace `mynamespace` значения `foo` и `bar` соответственно. Удаляем свойство `propprop`
    ```php
    /** @var Disk\Item\File $file */
    $file = $yandexDisk->file('/path/to/file/');
    $propertyCollection = new Disk\Collection\PropertyCollection();
    $propertyCollection
        ->add('myprop', 'mynamespace', 'foo')
        ->add('propmy', 'mynamespace', 'bar')
        ->add('propprop', 'mynamespace');
    
    $file->changeProperties($propertyCollection); // bool
    ```
2. Сохранение заранее полученных свойств
    
    > Неименяемые свойства не сохраняются
    
    Загружаем свойства `myprop`, `propmy`, `propprop`, `quota-available-bytes`
    ```php
    /** @var Disk\Item\File $file */
    $file = $yandexDisk->file('/path/to/file/');
    $propertyCollection = new Disk\Collection\PropertyCollection();
    $propertyCollection
        ->add('myprop', 'mynamespace')
        ->add('propmy', 'mynamespace')
        ->add('quota-available-bytes', Disk\Model\Property::IMMUTABLE_NAMESPACES['dav'])
        ->add('propprop', 'mynamespace');
    
    /** @var Disk\Collection\PropertyCollection $loadCollection */
    $loadCollection = $file->loadProperties($propertyCollection);
    ```
    
    В загруженной коллекции есть свойства двух видов, изменяемые и неименяемые
    > Свойства приходят неизменяемыми для встроенных свойств Яндекс.Диска. Например `quota-available-bytes` будет неизменяемым
    
    Для получения только изменяемых свойств коллекции
    ```php
    /** @var Disk\Property\Mutable $property */
    foreach ($loadCollection->getChangeable() as $property) {
        $property->setValue('baz'); //устанавливаем новое значение
    }
    ```
    > Так же можно узнать можно ли изменять свойтво через метод у свойства `canChanged()`
    ```php
    // добавляем новое свойство
    $loadCollection->add('newprop', 'mynamespace', 'bar');
    // добавляем неизменяемое свойств (свойство не будет сохранятся)
    $loadCollection->add('immutable', Disk\Model\Property::IMMUTABLE_NAMESPACES['dav'], 'immut');
    ```
    
    После этого сохраняем измененные значения
    ```php
    $file->saveProperties();
    ```
    
### [Публикация элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/)
**Пример написан для файла, но метод так же применим для директории**

```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->startPublish(); // bool
//получение публичной ссылки
Disk\Collection\ResultList::getInstance()->getLast()->getResult(); // string
```

### [Закрытие публикации элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/)
**Пример написан для файла, но метод так же применим для директории**

```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->stopPublish(); // bool
```

### [Проверка публикации элемента (Применимо для файла/директории)](https://tech.yandex.ru/disk/doc/dg/reference/publish-docpage/)
**Пример написан для файла, но метод так же применим для директории**

```php
/** @var Disk\Item\File $file */
$file = $yandexDisk->file('/path/to/file/');
$file->checkPublish(); // bool
//получение публичной ссылки
Disk\Collection\ResultList::getInstance()->getLast()->getResult(); // string
```