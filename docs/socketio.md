Socket.IO
=========

Система администрирование поддерживает работу с node.js в качестве прокси для передачи сообщений.

Задача системы одна: инициировать на PHP-сервере событие клиента (действие пользователя 1 реагирует на Пользователя 2 без перезагрузки страницы).

Вся бизнес-логика и хранение данных остаётся на стороне PHP.

Node.js часть не содержит логику, не хранит данные. Поэтому можно использовать, к примеру, один Node.js сервер для нескольких проектов, не опасаясь утечки данных или взлома. Сервер `http://cloud.doit-cms.ru` доступен для использования в проектах. Исходный код сервиса доступен и открыт, его можно запустить на собственном node.js сервере https://github.com/ramainen/doit-socket.

При желании вы можете организовать свой сервер, облако Azure цепляет "из коробки" репозиторий сервера на github.


Для использования необходимо:

### 1. Вставить в шаблон страницы подключение (желательно в теге `<head>`):

	{{socketIO#init 'http://cloud.doit-cms.ru'}}

Возможно использование второго параметра, инициирующего посетителя:

	{{socketIO#init 'http://cloud.doit-cms.ru', md5(session_id())}}

Возможно использование параметров по-умолчанию.

	{{socketIO#init}}

**Внимание!** Для того, чтобы вся эта затея имела смысл, в базе данных пользователей, принимающих сообщения, должны быть сохранены их идентификаторы (по умолчанию `md5(session_id())`).

В качестве альтернативного варианта можно вставить код напрямую:

	<!-- Здесь обычный клиентский socket.io. Его можно брать в любом месте. -->
	<script src="/cms/external/socket.io.js"></script>
	<script>
		var socket = io("http://cloud.doit-cms.ru");
		socket.emit("register", {
			userid: "<?php print md5(session_id()); ?>"
		});
	</script>
	
### 2. Поставить обработчики событий на клиенсткой части:

	<script>
	socket.on('server_event', function (data) {
		console.log('server event');
		console.log(data);
	})
	socket.on('alert', function (data) {
		alert(data);
	})
	</script>
	
### 3. На стороне сервера послать события (зная ID пользователя):

	d()->SocketIO->url='http://cloud.doit-cms.ru';
	d()->SocketIO->emit('75b0390c23ecef9109e66d0b352a1a66','server_event');
	
	d()->SocketIO->url='http://cloud.doit-cms.ru';
	d()->SocketIO->emit('75b0390c23ecef9109e66d0b352a1a66','alert');
	
	//a98166839163afd20a4be56fa3e60d13 - уникальный ключ пользователя (md5(session_id())), указанный при подключении
	d()->SocketIO->emit('a98166839163afd20a4be56fa3e60d13','server_event', array('Вася','пупкин'));
	d()->SocketIO->emit('a98166839163afd20a4be56fa3e60d13','server_event', array('user'=>array(1,2,3)));
	d()->SocketIO->emit('a98166839163afd20a4be56fa3e60d13','alert', '123');

Для тестирования можно описать роутер:

	route('/emit',function(){
		d()->SocketIO->url='http://cloud.doit-cms.ru';
		d()->SocketIO->emit('75b0390c23ecef9109e66d0b352a1a66','server_event','Привет');
	});

На этом всё.

Также доступна передача произвольных параметров, но для этого необходимо передать произвольные GET параметры напрямую на node.js сервер через `file_get_contents`.

