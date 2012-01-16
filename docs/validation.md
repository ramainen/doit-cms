Валидации
=========

Действия (actions) - функции, которые выполняются после обработки форм. Это различные сортировки, поиск, отправка электронной почты, регистрация, создание и реактирование объектов и так далее.

В простейшем случае, для создания такого действия необходимы несколько шагов:

### Форма

Форма для отправки данных может быть свёрстана при помощи помощников.

	<!-- users_registration.html -->
	
	{{form 'users_create_new' }} 

		{{notice}}
	
		Логин {{input 'login'}} <br>
		Пароль {{input 'password', type=>'password'}} <br>
		Подтверждение пароля {{input 'password_confirmation', type=>'password'}} <br>
		Электронная почта {{input 'email'}} <br>
		
		<input type="submit">

	{{/form}}
 
В данном примере: `{{form}}` - генерирует открывающий тег `<form>` с необходимыми скрытыми полями,
`{{notice}}` - вывод сообщений об ошибках, `{{input 'login'}}` - вывод текстового поля для получения значения login.


Форма обрабатывается действием `users_create_new`.
Это означает, что в коде, обрабатывающем вывод данной формы, необходимо вызвать 

	function show_registration_form()
	{
		d()->action('users_create_new');
		print d()->users_registration_tpl();
	}

Также необходимо объявить функцию `users_create_new`, которая будет совершать необходимое действие (в данном
случае - регистрацию пользователя).

В данном примере используются независимые функции, но возможна и реализация при помощи класса, например, `users_controller` с методами `create`, `update`, `check` и так далее.

В любом из *.init.ini файлов (возможно, validators.init.ini) необходимо создать несколько правил для нового действия.

Вот пример реализации:

	[validator.users_create_new.login]
	required.message=Вы не ввели имя пользователя
	unique.message=Такой логин уже занят
	unique.table=users
	
	[validator.users_create_new.password]
	required.message=Вы не ввели пароль
	confirmation.message=Неправильное подтверждение пароля

	[validator.users_create_new]
	function=check_registration
	function=check_another_registration
	
В данном примере переменная `required.message` представляет собой простейший вариант проверки. Для более сложных проверок есть дополнительные функции.

	function check_registration($params)
	{
		// Альтернативная проверка на существование пользователя
		// Не нужно на практике, т.к. есть поле unique
		if(! d()->User->find_by_login($params['login'])->is_empty) {
			d()->add_notice('Имя занято');
		}
		
		if( strlen($params['login'])<4) {
			d()->add_notice('Имя слишком короткое');
			return false;
		}
	}

	function check_another_registration($params)
	{
		if( true ) {
			d()->add_notice('Ошибка просто так, из вредности');
		}	
	}

Если одна из функций вернёт false, то остальные не будут запускаться. Функций может быть любое количество.

Внимание: такого рода функция привязане не к конкретному полю, а ко всей форме, это указано в ini-файле.

Также функции можно привязывать к просто полям, но синтаксис будет несколько другим.

Таким образом, можно код функции `users_create_new` оставить простым:
	
	function users_create_new($params)
	{
		$user=d()->User->new;
		$user->login=$params['login'];
		$user->password=$params['password'];
		$user->save();
		
		header('Location: /');
		exit();
	}
	
	
Другой вариант иполнения, без объявления дополнительной функции выглядит так:

В функции - контроллере, вызывающем форму, вставляется следующая проверка:

	function show_registration_form()
	{
		if(d()->validate('users_create_new')){
			$user=d()->User->new;
			$user->login=d()->params['login'];
			$user->password=d()->params['password'];
			$user->save();

			header('Location: /');
			exit();
		}
		print d()->users_registration_tpl();
	}

В качестве плюса такого подхода - более простой внешний вид, отсуствие лишних функций.

В качестве минуса - невозможность переопределить обработчик действия.

Данный код абсолютно идентичен показанному ранее, кроме одного момента.
Для получения параметров используется переменная `d()->params`.
`d()->params` можно использовать и в функции - действии, игнорируя переменную $params, т.к. данные дублируются.
Т.е. указанную выше функцию `users_create_new` можно вызвать так:

	function users_create_new()
	{
		$user=d()->User->new;
		$user->login=d()->params['login'];
		$user->password=d()->params['password'];
		$user->save();

		header('Location: /');
		exit();
	}

Учитывая функцию `d()->view()`, мы можем переименовать файл с html-шаблоном в `show_registration_form.html`.
В этом случае окончательный вариант может выглядеть так:

	function show_registration_form()
	{
		if(d()->validate('users_create_new')){
			$user=d()->User->new;
			$user->login=d()->params['login'];
			$user->password=d()->params['password'];
			$user->save();

			header('Location: /');
			exit();
		}
		print d()->view();
	}


В качестве првоерочной функции можно указывать свои значения. Например:

	valid_email.message=Некорректный адрес электронной почты

В данном случае вызывается функция `d()->valid_email()`. В качестве параметров принимаются: искомая строка для проверки
и массив с параметрами (в данном случае только один - message).

Например, можно передать следующие параметры

	my_function_check.message=Некорректный адрес электронной почты
	my_function_check.min_length=4
	my_function_check.max_length=20

Вызовется функция `d()->my_function_check()`, в качестве массива параметров будет массив тремя ключами: message,
min_length, max_leght

`valid_email` уже существует, и её можно свободно использовать. Также можно написать свои функции для более сложных
проверок.


Валидации можно использовать напрямую в контроллере. Для этого можно дублировать вызов `validate`. Например:

	if(d()->validate('edit_document_action')){
		if(true){
			d()->add_notice('Проблема');
		}
	}
	
	if(d()->validate('edit_document_action')){
		return 'Всё ОК';
	}
	
	print d()->view();
	
Данный код выполнит `return 'Всё ОК';`, если в первой части не было ошибок валидации. Таких последовательностей может быть сколько угодно.