Загрузка файлов
===============

Класс `Upload` предназначен для простой загрузки файлов и изображений.

Для самого простого использования:
	
	<?php
	$filename = d()->Upload->save();   //в переменной $filename будет готовый адрес
	print $filename; // "/storage/076e3caed758a1c18c91a0e9cae3368f.jpg";

Система по умолчанию не даст загрузить опасные файлы.
	
В этом случае будет взят первый файл из присланных (в большинстве случаев он, вероятно, и будет одним).

Если используется несколько полей выбора файлов, то для того, что указать, какой именно файл был прислан (например, для `<input type="file" name="myfile">`), укажите:

	$filename = d()->Upload('myfile')->save();

Для того, чтобы разрешить загружать только изображения, укажите:

	$filename = d()->Upload('myfile')->only_images->save();


**Примечание**: для того, чтобы использовать загрузку файлов, необходимо правильно указать `enctype` формы.

Самый простой способ сделать это - указать параметр `upload`:

	{{form 'contactform_send', 'upload'=>true}}
	
Подробное описание
------------------

Вот пример использования:

	<?php
	$upload = d()->Upload('file_field_name');		
	var_dump($upload->exists); //true или false
	var_dump($upload->name); //оригинальное имя
	var_dump($upload->md5); //md5-хеш от содержимого
	var_dump($upload->extension); //расширение файла
	var_dump($upload->ext); //тоже самое
	var_dump($upload->tmp_name); //tmp_name (путь на сервере)
	var_dump($upload->save()); //Сохраняет и возвращает путь
	var_dump($upload->new_name); //Сохранённый путь к файлу

Если нам не нужно сохранять файл в папке storage (например, просто отправить по почте или обработать excel-файл для импорта):

	<?php
	$upload = d()->Upload;
	if($upload->exists){
		$content = file_get_content($upload->tmp_name);
	}

### `save` или `move`

`save` и `move` - псевдонимы одной и той же функции. Функция принимает два необязательных аргумента - папку, в которую необходимо сохранить файл, и имя файла.

По умолчанию используется папка `storage`, если указать, к примеру, `users`, то сохранение произойдёт в папке `storage/users`.

Второй аргумент можно использовать для того, чтобы скрыть расширение (например, чтобы закрыть доступ к файлам из внешней сети).

В большинстве случаев второй аргумент указывать не нужно.

Примеры:

	$file = d()->Upload->save();
	$document = d()->Upload->allow_files('pdf,doc,xls,xlsx,docx,zip,rar,odt')->move();
	$user = d()->User->new;
	$user->avatar = d()->Upload->only_images->move('users');
	$file = d()->Upload->move('secretfiles','file_876');
	$file = d()->Upload('mp3file')->allow_files('mp3')->save('sounds');

Если файла не было прислано, или было неверное (запрещённое) расширение файла, или иные проблемы, то функция вернёт пустую строку и ничего не сохранит.


### `md5`

`d()->Upload->md5` возвраещет md5 от содержимого загруженного файла.

### `extension`

`d()->Upload->extension` возвраещет расширение загруженного файла.

### `allow_files`

`d()->Upload->allow_files` задаёт список разрешённых расширений. Например:

	d()->Upload->allow_files('doc,docx,rtf,txt,xls,xlsx');

### todo: `allow_images`
### todo: `upload_dir`
### todo: `exists`
### todo: `name`
### todo: `new_name`
### todo: `tmp_name`
