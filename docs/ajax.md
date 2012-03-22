Использование AJAX
==================

Есть несколько способов эффективно использовать AJAX.
	
Использование при отправке формы
--------------------------------

На серверной строне необходимо припсать несколько необязательных строк:
		
	if(d()->validate('add_news')){
		$news = d()->News->new();
		$news->title = d()->params['title'];
		$news->text = d()->params['text'];
		$news->save;
	
		if(AJAX){
			d()->Ajax->set_html('h1','Текст');
		}
		
		d()->reload();
	}
		
В данном примере `d()->reload()` -- универсальная функция для перезагрузки страницы.

	d()->Ajax->set_html('h1','Текст');
	
Вызывает метод `html()` библиотеки jQuery. 

Вообще, можно вообще ничего не делать, не используя `d()->Ajax`, всё будет работать само.

Форма в клиентской части немного отличается:

	{{form 'add_news', 'ajax'=>true}}
		{{input 'title'}}
		{{input 'text'}}
		<input type="submit" value="СОздать">

	{{/form}}
	

Простейшая валидация
--------------------

	class News extends Controller
	{
		function index()
		{
			if(d()->validate('add_news')){
				$news = d()->News->new;
				$news->title=d()->params['title'];
				$news->save;
				
				d()->Ajax->reload();
				d()->reload();
			}
			
			if(d()->notice()){
				d()->Ajax->set_html('h1',d()->notice());
				d()->reload();
			}
			
			d()->news_list = d()->News;
			print d()->view();
		}
	}
	