Дата и время
============

Для работы с датой в системе существует класс `Date`. На вход конструктор принимает любую дату (unixtime, дату на русском языке, в формате MySQL и так далее). Далее разными способами можно вывести дату на страницу. Ниже расопложены несколько примеров вывода.

	<div>
	@ if(d()->this->published_at) {
		<br>
		Чистая дата: {.published_at}<br>
		Чистая дата по русски: {.published_at|userdate}<br>
		Вариант 2: {.published_at|userdate "d"} |  {.published_at|userdate "m"} |  {.published_at|userdate "Y"}<br>
		Вариант 3: <?= d()->Date(d()->this->published_at)->day?> | <?= date("m",d()->this->published_at_as_date_stamp) ?> | <?= d()->Date(d()->this->published_at)->year?><br>
		Вариант 4: <?= d()->Date(d()->this->published_at)->day?> | <?= d()->Date(d()->this->published_at)->month?> | <?= d()->Date(d()->this->published_at)->year?><br>
		Вариант 5: <?= d()->Date(d()->this->published_at)->day?> | <?= d()->Date(d()->this->published_at)->ru_month?> | <?= d()->Date(d()->this->published_at)->year?><br>
		Вариант 6: <?= d()->Date(d()->this->published_at)->day?> | <?= d()->Date(d()->this->published_at)->ru_month_simple?> | <?= d()->Date(d()->this->published_at)->year?><br>
		Вариант 7: <?= d()->Date(d()->this->published_at)->day?> | <?= d()->Date(d()->this->published_at)->ru_month_mini?> | <?= d()->Date(d()->this->published_at)->year?><br>
		Вариант 8: {this.published_at_as_date_day} | {this.published_at_as_date_ru_month} | <?= d()->Date(d()->this->published_at)->year?><br>
		Вариант 9: {this.published_at_as_date_day} | {this.published_at_as_date_ru_month} | {this.published_at_as_date_year}<br>
		<br>
	@ }
	</div>
	
Примечание: некоторые методы, как `userdate` с параметрами, `*_as_date_year`, `*_as_date_stamp` доступны в системе, только начиная с 16.07.2018 года - необходимо обновиться до новой версии.