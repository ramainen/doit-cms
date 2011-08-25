DoIt CMS
=============

Простая система администрирования и фреймворк для быстрой и гибкой разработки web-приложений. По многим аспектам не накладывает жёстких рамок.
Позволяет выполнять классические задачи при помощи ООП или при помощи функционального подхода, либо вообще отказавшись от разделения логики представления от логики приложения.
В приоритете стоит простота системы, ясность работы, однозначность каждого шага, прозрачность подхода.

Установка
---------

Установка производится обычным копированием файлов системы в корневую директорию сайта.

### Требования

* PHP 5.2
* MySQL 5 (необязательно)
* Apache 2 (для поддержки перенаправлений в .htaccess)

Для запуска из других скриптов следует вызвать следующий код:
	
	include ('cms/cms.php');
    d()->main();

Именно этот код и находится в файле index.php.

Для работы желательна база данных MySQL, но сама система не использует её. Таким образом, возможны веб-приложения, не обращающиеся к базе данных (например, форма обратной связи).
