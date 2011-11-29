Внешние библиотеки
==================

Doit cms умеет использовать библиотеки из внешних источников (Zend Framework2, Symphony2, Imagine и другие).

Рассмотрим пример использования Imagine.

1. Скачиваем Imagine и помещаем содержимое папки lib в папку vendors. Таким образом там появится папка Imagine.

2. Используем.

	function create_preview()
	{
		$imagine = new Imagine\Gd\Imagine();
		$image = $imagine->open('example.png');
		$thumbnail = $image->thumbnail(new Imagine\Image\Box(100, 100));
		$thumbnail->save('example.thumb.png');
	}
	
Таким образом можно использовать библиотеки, которые следуют спецификации [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).

Обратите внимание на то, что для использования такого подхода необходим PHP5.3.