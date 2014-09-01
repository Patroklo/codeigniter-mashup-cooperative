<?php

$CI =& get_instance();

$CI->load->_include_class('core/Model');

class Base_model extends CI_Model {

	public $carga = FALSE;

	public $tableName = FALSE;

	public $object = FALSE;


	/**
	 * ========================================================================================================
	 * ========================================================================================================
	 *              CRUD FUNCTIONS
	 * ========================================================================================================
	 * ========================================================================================================
	 */

	function insert($data) {
		$cols = $this -> get_columns();

		//comprobamos que las entradas de data correspondan con entradas de la bbdd
		$this -> check_cols(array_keys($data));

		return beep_from($this -> tableName) -> values($data) -> insert();
	}

	function update($data) {
		if ($this -> carga == FALSE) {
			return FALSE;
		}

		$this -> check_cols(array_keys($data));

		return beep_from($this -> tableName) -> values($data) -> where('id', $this -> carga -> id) -> update();

	}

	function delete() {
		if ($this -> carga == FALSE) {
			return FALSE;
		}

		beep_from($this -> tableName) -> where('id', $this -> carga -> id) -> delete();

		return TRUE;
	}

	/**
	 * ========================================================================================================
	 * ========================================================================================================
	 *              CARGA FUNCTIONS
	 * ========================================================================================================
	 * ========================================================================================================
	 */

	function carga($data, $object = TRUE) {

		if (!is_array($data)) {
			$data = array('id' => $data);
		}
		$query = beep_from($this -> tableName) -> where($data) -> limit(1) -> get();
		if ($query -> num_rows() > 0) {
			$query -> result();
			if ($object == FALSE) {
				$this -> carga = $query -> row();
			} 
			elseif($object === TRUE)
			{
				$this -> carga = $query -> row(0, $this -> object);
			}
			else {
				$this -> carga = $query -> row(0, $object);
			}
		} else {
			$this -> carga = FALSE;
		}

		return $this -> carga;
	}

	function descarga() {
		$this -> carga = FALSE;
	}
	
	function lista($object = TRUE)
	{
		if($object === TRUE)
		{
			return beep($this->object);
		}
		else
		{
			beep_from($this->tableName);
		}
	}

	function me() {
		return beep_from($this -> tableName);
	}

	//retorna la row cargada con formato del objeto del model elegido
	function _to_object($object) {
		if ($this -> carga == FALSE) {
			return FALSE;
		}

		//comprobamos si es std object u otro tipo. Si no es std object o array dará error
		if (is_object($this -> carga) and get_class($this -> carga) == 'stdClass') {
			load_object($object);

			$data = (array)$this -> carga;

			$this -> carga = new $object();

			$this -> carga -> fill_object($data);

			return $this -> carga;

		} elseif (is_array($this -> carga)) {

			$data = $this -> carga;

			$this -> carga = new $object();
			$this -> carga -> fill_object($data);
			return $this -> carga;
		}

		throw new Exception("No es del tipo stdClass o un array", 1);

	}

	function get_info() {
		$filtro_global = $this -> get_global();

		if ($filtro_global == FALSE) {
			$filtro_global = Beep_from($this -> tableName);
		}

		return $filtro_global -> select($this -> tableName . '.*');
	}

	/**
	 * ========================================================================================================
	 * ========================================================================================================
	 *              GLOBAL QUERY FUNCTIONS
	 * ========================================================================================================
	 * ========================================================================================================
	 */

	function get_global() {
		return $this -> correcaminos -> get_global($this -> tableName);
	}

	function set_global($query) {
		$this -> correcaminos -> set_global($query, $this -> tableName);
	}

	function delete_global() {
		$this -> correcaminos -> delete_global($this -> tableName);
	}

	/**
	 * ========================================================================================================
	 * ========================================================================================================
	 *              PRIVATE FUNCTIONS
	 * ========================================================================================================
	 * ========================================================================================================
	 */

	private function check_cols($keys) {
		foreach ($keys as $k) {
			if (!array_key_exists($k, $cols)) {
				throw new Exception("No se puede insertar el dato, ya que el nombre de las columnas difiere con la base de datos.");
			}
		}
		return TRUE;
	}

	private function get_columns() {
		$sub_col_list = get_class_data($this -> object) -> get_columns();

		$col_list = array();

		foreach ($sub_col_list as $col) {
			if (!array_key_exists('main', $col) or (array_key_exists('main', $col) && $col['main'] == FALSE)) {
				$col_list[$col['field_name']] = $col['field_name'];
			}
		}
	}

}
