<?php


class Scaffold extends UniversalHelper
{
	function create_field($table,$field)
	{
		if (substr($field,-3)=='_id') {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` int NULL");
		} elseif (substr($field,0,3)=='is_') {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` tinyint(4) NOT NULL DEFAULT 0");
		} elseif (substr($field,-3)=='_at') {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` datetime NULL");
		} else {
			d()->db->exec("ALTER TABLE `".$table."` ADD COLUMN `$field` text NULL, DEFAULT CHARACTER SET=utf8");
		}
	}
	
	function create_table($table,$one_element="")
	{
		if($one_element==''){
			$result = d()->db->exec("CREATE TABLE `".$table."` (
				`id`  int(11) NOT NULL AUTO_INCREMENT ,
				`url`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`text`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`title`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`template`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`type`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`multi_domain`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`sort`  int(11) NULL DEFAULT NULL ,
				PRIMARY KEY (`id`)
				)
				ENGINE=MyISAM
				DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
				;");
		}else{
			$result = d()->db->exec("CREATE TABLE `".$table."` (
				`id`  int(11) NOT NULL AUTO_INCREMENT ,
				`url`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`text`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`title`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
				`".$one_element."_id`  int(11) NULL DEFAULT NULL ,
				`template`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`type`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
				`multi_domain`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,				
				`sort`  int(11) NULL DEFAULT NULL ,
				PRIMARY KEY (`id`)
				)
				ENGINE=MyISAM
				DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
				;");		
		}
		return $result;
	}
	/* Обновление по схеме данных. Создаются таблицы, не существующие на данный момент, а также создаются некоторые столбцы */
	function update_scheme()
	{
		//1. Получение списка существующих таблиц
		
		
		$tables_data = d()->db->query("SELECT  TABLE_NAME FROM information_schema.tables WHERE table_schema = '".DB_NAME."'")->fetchAll();
		
		
		$tables=array();
		foreach ($tables_data as $key=> $value){
			$tables[$value['TABLE_NAME']]=array();
				$columns = d()->db->query("SELECT * FROM `".$value['TABLE_NAME']."` LIMIT 0");
			 
			$columns_count =  $columns->columnCount();
 
			for($i=0;$i<=$columns_count-1;$i++){
				$column = $columns->getColumnMeta($i);
				$tables[$value['TABLE_NAME']][]=$column['name'];
			}
				 
		
		}
		

		//получение всех возможных таблиц
		$schema_tables=array();
		foreach (d()->schema as $key => $value)
		{
			if(is_numeric($key)){
				foreach ($value as $table){
					$schema_tables[$table]=array();
				}
			}else{
				$table=$key;
				$schema_tables[$table]=array();
			}
		}
		//плучение всех возможных полей
		foreach (d()->schema as $key => $value)
		{
			if(!is_numeric($key)){
				$table=$key;
				$schema_tables[$table]=array();
				foreach($value as $subkey=>$subvalue){
					if(!is_numeric($subkey)){
						if(is_array($subvalue)){
							//Будет записано первое попавшееся значение, ибо нефиг
							$schema_tables[$table][$subkey]=$subvalue[0];
						}else{
							$schema_tables[$table][$subkey]=$subvalue;
						}
					}else{
						if(is_array($subvalue)){
							foreach($subvalue as $element){
								$schema_tables[$table][$element]=true;
							}
						}else{
							//такой ситуации быть не может
						}
					}
				}
			} 
		}

		foreach($schema_tables as $table=>$fields){
			if(!isset($tables[$table])){
				$this->create_table($table);
			}
		}
		
		foreach($schema_tables as $table=>$fields){
			foreach($fields as $field=>$type){
				if(!in_array($field,$tables[$table])){
					//создать поле $field в таблице $table
					$this->create_field($table,$field);
				}
			}
		}
		 
	}
}
