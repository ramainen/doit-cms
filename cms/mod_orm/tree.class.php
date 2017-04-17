<?php

trait Tree {
	
	function parent()
	{
		$parent_field = to_o($this->_options['table']);
		return $this->$parent_field;
	}
	
	function subtree_ids()
	{
		$parent_field = to_o($this->_options['table']) . '_id';
		$class = get_class();
		$ids = $temp_ids = array_filter($this->fast_all_of('id'));
		do {
			$count = count($ids);
			$temp_ids = d()->{$class}
					->select(DB_FIELD_DEL . 'id' . DB_FIELD_DEL)->order('')
					->where(DB_FIELD_DEL . $parent_field . DB_FIELD_DEL . ' in (?)', $temp_ids)
					->fast_all_of('id');
			$ids = array_filter(array_unique(array_merge($ids, $temp_ids)));
		} while ((count($ids) !== $count) && !empty($temp_ids));
		return $ids;
	}
	
	function subtree()
	{
		$class = get_class();
		return d()->$class->where(DB_FIELD_DEL . 'id' . DB_FIELD_DEL . ' in (?)', $this->subtree_ids);
	}
	
	function suptree_ids()
	{
		$parent_field = to_o($this->_options['table']) . '_id';
		$class = get_class();
		$ids = $temp_ids = $this->fast_all_of('id');
		if (!empty($ids)) {
			do {
				$count = count($ids);
				$temp_ids = d()->{$class}
						->select(DB_FIELD_DEL . $parent_field . DB_FIELD_DEL)->order('')
						->where(DB_FIELD_DEL . 'id' . DB_FIELD_DEL . ' in (?) and ' . DB_FIELD_DEL . $parent_field . DB_FIELD_DEL . ' is not null', $temp_ids)
						->fast_all_of($parent_field);
				$ids = array_unique(array_merge($ids, $temp_ids));
			} while ((count($ids) !== $count) && !empty($temp_ids));
		}
		return $ids;
	}
	
	function suptree() {
		$class = get_class();
		$ids = $this->suptree_ids;
		$result = d()->{$class}->where(DB_FIELD_DEL . 'id' . DB_FIELD_DEL . ' in (?)', $ids);
		if (!empty($ids)) {
			$result->order('field(`id`,' . implode(',', $ids) . ')');
		}
		return $result;
	}

}
