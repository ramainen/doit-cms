<?php



class Params implements ArrayAccess 
{
	function offsetGet( $index )
	{
		return Validator::create($_POST[$index], $index);
	}

	function offsetExists($offset) {
		return isset($_POST[$offset]);
	}
	
	function offsetSet($offset, $value) {
        return false;
    }
	function offsetUnset($offset ) {
        return false;
    }

    public function __call($name,$params)
    {
    	return call_user_func_array('Validator::'.$name, $params); 
    }
    public function __get($name)
    {
    	return call_user_func('Validator::'.$name); 
    }

}

 