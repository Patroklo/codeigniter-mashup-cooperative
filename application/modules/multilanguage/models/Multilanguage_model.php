<?php
class Multilanguage_model extends base_model{

    public $tableName 				= 'multilanguage_data';
    public $language_table 			= 'multilanguage_languages';
    public $language_fields_table 	= 'multilanguage_fields';
	public $portafolioid			= NULL;
	public $main_language			= NULL;

    public $ses_language_tag = 'multilanguage_selected';

    public $language_list;
    public $language_fields;

    function __construct()
    {
        parent::__construct();

		if(is_null($this->language_fields))
		{
	        $query = beep_from($this->language_fields_table)->select('field, '.$this->language_fields_table.'.*')->get();

	        $this->language_fields = $query->group_result('array');
		}
    }

    /*
     * ================================================================
     *
     *     INITIALIZATION DATA
     *
     * ================================================================
     */

	public function initialize($portafolioid = FALSE)
	{
		if($portafolioid === FALSE)
		{
			$portafolioid = $this->auth->get_portafolio_id();
		}

		$this->portafolioid = $portafolioid;

		$query = beep_from($this->language_table)->where('portafolioid', $this->portafolioid)->
												   select('id, '.$this->language_table.'.*')->
												   order_by('language', 'ASC')->
												   get();


        if($query->num_rows() == 0)
        {
            $this->language_list = array();
        }
        else
        {
            $this->language_list = $query->group_result('array');
        }

		foreach($this->language_list as $l)
		{
			if($l['main'] == 1)
			{
				$this->main_language = $l['id'];
			}
		}

		if(is_null($this->main_language) and !empty($this->language_list))
		{
			$first_language = reset($this->language_list);
			$this->main_language = $first_language['id'];
		}
	}

	function is_initialized($portafolioid = FALSE)
	{
		if($portafolioid === FALSE)
		{
			$portafolioid = $this->auth->get_portafolio_id();
		}

		if($this->portafolioid == $portafolioid)
		{
			return TRUE;
		}

		return FALSE;
	}


	function get_portafolio()
	{
		if(is_null($this->portafolioid))
		{
			throw new Exception("No se ha inicializado la clase de multilenguaje.", 1);
		}

		return $this->portafolioid;
	}

    /*
     * ================================================================
     *
     *      SESSION DATA
     *          -> set_active_language pone el lenguaje que queramos activar en la session
     *          -> get_active_language -> pues eso
     *
     * ================================================================
     */

    function set_active_language($id)
    {
        if(!array_key_exists($id, $this->language_list))
        {
        	//ponemos el lenguaje principal
            $id = $this->main_language;
        }

		//$data = array($this->ses_language_tag => array($this->get_portafolio() => $id));
		$lenguajes = $this->session->userdata($this->ses_language_tag);

		if(is_array($lenguajes))
		{
			$lenguajes[$this->get_portafolio()] = $id;
		}
		else
		{
			$lenguajes = array($this->get_portafolio() => $id);
		}

        $this->session->set_userdata($this->ses_language_tag, $lenguajes);
    }

    function get_active_language()
    {
        $retorno = $this->session->userdata($this->ses_language_tag);
		
        if($retorno === FALSE or is_null($retorno) or !array_key_exists($this->get_portafolio(), $retorno) || !array_key_exists($retorno[$this->get_portafolio()], $this->language_list))
        {
            $retorno = $this->main_language;
            $this->set_active_language($this->main_language);
			return $retorno;
        }

        return $retorno[$this->get_portafolio()];
    }

    /*
     * ================================================================
     *
     *     URI de LENGUAJE
     *
     * ================================================================
     */

	function check_uri($uri)
	{
		$check_lan = FALSE;

		foreach($this->language_list as $l)
		{
			if($l['uri'] == $uri)
			{
				$check_lan = $l['id'];
			}
		}


		if($check_lan === FALSE)
		{
			return FALSE;
		}
		else{
			$this->set_active_language($check_lan);

			return TRUE;
		}
	}

	function get_active_language_uri()
	{
		$id = $this->get_active_language();

		return $this->language_list[$id]['uri'];

	}

	function get_public_languages()
	{
		$retorno = array();

		foreach($this->language_list as $l)
		{
			if($l['mostrar_publico'] == '1')
			{
				$retorno[] = $l;
			}
		}

		return $retorno;

	}


    /*
     * ================================================================
     *
     *     GETTERS PRIVADOS
     *
     * ================================================================
     */


    private function get_field_id($field)
    {
        if(!array_key_exists($field, $this->language_fields))
        {
            throw new Exception("Error, no existe el campo seleccionado en la lista de campos multiidioma.", 1);
        }
        else {
            return $this->language_fields[$field]['id'];
        }
    }

    private function field_with_alternatives($field)
    {
        if($this->language_fields[$field]['with_alternatives'] == '1')
        {
            return TRUE;
        }

        return FALSE;
    }

    private function get_field_data($id, $field, $multilanguage = FALSE)
    {
        $field_id = $this->get_field_id($field);


        $where_arr = array('inner_id' => $id, 'field_id' => $field_id);

        if($multilanguage === FALSE)
        {
            $where_arr['language_id'] =$this->get_active_language();
        }

        if($this->get_global() == FALSE)
        {
            $semilla = $this->me();
        }
        else
        {
            $semilla = $this->get_global();
        }

        $query = $semilla->where($where_arr)->select('id, text_data')->get();

        if($query->num_rows() == 0)
        {
            return FALSE;
        }

        if($multilanguage === FALSE)
        {
            $row = $query->row();

            return $row;
        }
        else {
            return $query->result('array');
        }

    }

    private function get_field_multidata($fields, $multilanguage = FALSE)
    {
        if(!is_array($fields))
        {
            throw new Exception("Error, el campo de la función multidata debe ser un array.", 1);
        }

        if($this->get_global() == FALSE)
        {
            $semilla = $this->me();
        }
        else
        {
            $semilla = $this->get_global();
        }

        $primero = TRUE;

        $language_id = $this->get_active_language();

        $retorno = array();

        foreach($fields as $f)
        {
        	$array_busqueda = array();
			$busqueda_in = FALSE;

            if(!array_key_exists('inner_id', $f))
            {
                $f['inner_id'] = $this->get_portafolio();
            }

			if(is_array($f['inner_id']))
			{
				$busqueda_in = TRUE;
			}
			else
			{
				$array_busqueda['inner_id'] = $f['inner_id'];
			}

            $field_id = $this->get_field_id($f['field']);

            $array_busqueda['field_id'] = $field_id;

            if($multilanguage == FALSE)
            {
                $array_busqueda['language_id'] = $language_id;
            }

			//TODO permitir que inner_id sea un array
			if(!is_array($f['inner_id']))
			{
				$retorno[$f['field']][$f['inner_id']] = FALSE;
			}
			else
			{
				foreach($f['inner_id'] as $inner_id)
				{
					$retorno[$f['field']][$inner_id] = FALSE;
				}
			}

            if($primero === TRUE)
            {
                $primero = FALSE;
                $semilla = $semilla->open_bracket()->where($array_busqueda);

                if($busqueda_in == TRUE)
				{
					$semilla = $semilla->where_in('inner_id', $f['inner_id']);
				}
				$semilla = $semilla->close_bracket();
            }
            else
            {
                $semilla = $semilla->open_bracket('OR')->where($array_busqueda);
				if($busqueda_in == TRUE)
				{
					$semilla = $semilla->where_in('inner_id', $f['inner_id']);
				}
				$semilla = $semilla->close_bracket();
            }
        }

        $query = $semilla->join($this->language_fields_table, $this->language_fields_table.'.id = '.$this->tableName.'.field_id')->
                           select($this->tableName.'.*, '.$this->language_fields_table.'.field')->
                           get();

        if($multilanguage == FALSE)
        {
            foreach ($query->result('array') as $value)
            {
                $retorno[$value['field']][$value['inner_id']] = $value;
            }
        }
        else
        {
            foreach ($query->result('array') as $value)
            {
                $retorno[$value['field']][$value['inner_id']][$value['language_id']] = $value;
            }
        }

        return $retorno;
    }

    private function get_field_multilanguage_multidata($fields)
    {
        if(!is_array($fields))
        {
            throw new Exception("Error, el campo de la función multidata debe ser un array.", 1);
        }


        if($this->get_global() == FALSE)
        {
            $semilla = $this->me();
        }
        else
        {
            $semilla = $this->get_global();
        }


        $primero = TRUE;


        $language_id = $this->get_active_language();

        $retorno = array();

        foreach($fields as $f)
        {
            if(!array_key_exists('inner_id', $f))
            {
                $f['inner_id'] = $this->get_portafolio();
            }

            $sub_language_id = ((!array_key_exists('language_id', $f))?$language_id:$f['language_id']);

            $retorno[$sub_language_id][$f['field']][$f['inner_id']] = FALSE;

            $field_id = $this->get_field_id($f['field']);

            $array_busqueda = array('inner_id' => $f['inner_id'], 'field_id' => $field_id, 'language_id' => $sub_language_id);

            if($primero === TRUE)
            {
                $primero = FALSE;
                $semilla = $semilla->open_bracket()->where($array_busqueda)->close_bracket();
            }
            else
            {
                $semilla = $semilla->open_bracket('OR')->where($array_busqueda)->close_bracket();
            }
        }

        $query = $semilla->join($this->language_fields_table, $this->language_fields_table.'.id = '.$this->tableName.'.field_id')->
                           select($this->tableName.'.*, '.$this->language_fields_table.'.field')->
                           get();

        foreach ($query->result('array') as $value) {
            $retorno[$value['language_id']][$value['field']][$value['inner_id']] = $value;
        }

        return $retorno;
    }



     /*
     * ================================================================
     *
     *     GETTERS DE ADMINISTRACIÓN // NO USAR EN PÁGINAS PÚBLICAS
     *
     * ================================================================
     */


    function get_data($field, $id = FALSE)
    {

		if($id === FALSE)
		{
			$id = $this->auth->get_portafolio_id();
		}

        $retorno = $this->get_field_data($id, $field);
        if($retorno == FALSE)
        {
            return '';
        }

        return $retorno->text_data;
    }

    //entrada array('inner_id', 'field')
    function get_multidata($fields)
    {
        $retorno = $this->get_field_multidata($fields);

        foreach($retorno as &$r)
        {
            foreach($r as &$l)
            {
                if($l === FALSE)
                {
                    $l = '';
                }
                else
                {
                    $l = $l['text_data'];
                }
            }
        }

        return $retorno;

    }

    function get_multidata_multilanguage($fields)
    {

        $retorno = $this->get_field_multilanguage_multidata($fields);

        foreach($retorno as &$languages)
        {
            foreach($languages as &$fields)
            {
                foreach($fields as &$data)
                {
                    if($data == FALSE)
                    {
                        $data = '';
                    }
                    else
                    {
                        $data = $data['text_data'];
                    }
                }
            }
        }

        return $retorno;
    }

    /*
     * ================================================================
     *
     *      SETTERS
     *
     * ================================================================
     */

    function set_data($field, $data, $id = FALSE, $language_id = FALSE)
    {

		if($id === FALSE)
		{
			$id = $this->auth->get_portafolio_id();
		}

        $field_id = $this->get_field_id($field);

        $campo = $this->get_field_data($id, $field);

        if($language_id == FALSE)
        {
            $language_id = $this->get_active_language();
        }
        elseif(!array_key_exists($language_id, $this->language_list))
        {
            throw new Exception("Se está intentando dar de alga un lenguaje inexistente para este usuario.", 1);

        }

        $semilla = $this->me()->values(array('inner_id' => $id, 'field_id' => $field_id, 'text_data' => $data, 'language_id' => $language_id));

        if($campo === FALSE)
        {
           return $semilla->insert();
        }
        else{
            $semilla->where(array('inner_id' => $id, 'field_id' => $field_id))->update();
            return $campo->id;
        }
    }

	//entrada array('inner_id', 'field', 'text_data', 'language_id')
    function set_multidata($data)
    {
        if(!is_array($data))
        {
            throw new Exception("Error, el campo de la función multidata debe ser un array.", 1);
        }
        // 1 - creamos un array con todos los campos que queremos modificar para hacer una búsqueda en todos los idiomas que haya
        //     de esos campos y así saber si hay que hacer insert o update
        // 2 - formamos un array con los datos pasados en $data para poder trabajar luego cno ellos en el update o insert
        foreach($data as &$d)
        {
            if(!array_key_exists('inner_id', $d))
            {
                $d['inner_id'] = $this->get_portafolio();
            }
            $fields[] = array('inner_id' => $d['inner_id'], 'field' => $d['field'], 'language_id' => $d['language_id']);

            $save_data[$d['language_id']][$d['field']][$d['inner_id']] = $d['text_data'];
        }

        // 3 - obtenemos los datos multilenguaje, si están en FALSe es que no habrá nada en la tabla
        $datos = $this->get_field_multilanguage_multidata($fields);

         //hacemos insert o update recorriendo todos los campos
        foreach($datos as $lang_id => $campos)
        {
            foreach($campos as $name_campo =>  $campo)
            {

                $key_campo = $this->get_field_id($name_campo);

                foreach($campo as $key_id => $d)
                {
                    if($d === FALSE)
                    {
                        $this->me()->values(array('inner_id' => $key_id, 'field_id' => $key_campo, 'language_id' => $lang_id, 'text_data' => $save_data[$lang_id][$name_campo][$key_id]))->insert();
                    }
                    else
                    {
                        $this->me()->values(array('text_data' => $save_data[$lang_id][$name_campo][$key_id]))->where(array('inner_id' => $key_id, 'language_id' => $lang_id, 'field_id' => $key_campo))->update();
                    }
                }
            }
        }


    }

	function delete_multidata($id, $type)
	{
		$query = beep_from($this->language_fields_table)->where('tipo', $type)->get();

		if($query->num_rows() > 0)
		{
			foreach($query->result() as $r)
			{
				$ids[] = $r->id;
			}

			$this->me()->where('inner_id', $id)->where_in('field_id', $ids)->delete();
		}


		beep_from($this->language_table)->where('portafolioid', $id)->delete();
	}

	function delete_data($id, $field)
	{
		$field_id = $this->get_field_id($field);

		$this->me()->where('inner_id', $id)->where('field_id', $field_id)->delete();
	}

	function language_list($id = FALSE)
	{
		if($id == FALSE)
		{
			return $this->language_list;
		}
		else
		{
			if(array_key_exists($id, $this->language_list))
			{
				return $this->language_list[$id];
			}
			else
			{
				return FALSE;
			}
		}
	}


	function make_language_main($lang_id)
	{

		$query = beep_from($this->language_table)->where('id', $lang_id)->where('portafolioid', $this->auth->get_portafolio_id())->get();

		if($query->num_rows() == 0)
		{
			return FALSE;
		}

		beep_from($this->language_table)->where('portafolioid', $this->auth->get_portafolio_id())->values(array('main' => 0))->update();
		beep_from($this->language_table)->where('id', $lang_id)->values(array('main' => 1))->update();


	}


    function language_admin_list()
    {

        $retorno = array();

        foreach($this->language_list as $l)
        {
            if($l['mostrar_admin'] == 1)
            {
                $retorno[$l['id']] = $l;
            }
        }

        return $retorno;

	}

	function num_languages()
	{
		return count($this->language_list);
	}

    function language_public_list()
    {

        $retorno = array();

        foreach($this->language_list as $l)
        {
            if($l['mostrar_publico'] == 1 and $l['mostrar_admin'] != 0)
            {
                $retorno[$l['id']] = $l;
            }
        }

        return $retorno;

    }


    /*
     * ================================================================
     *
     *      CRUD
     *
     * ================================================================
     */

	function insert_language($datos)
	{
		$insert = array(
						'language'			=> $datos['language'],
						'portafolioid'		=> ((array_key_exists('portafolioid', $datos))?$datos['portafolioid']:$this->auth->get_portafolio_id()),
						'main'				=> '0',
						'mostrar_admin'		=> ((array_key_exists('mostrar_admin', $datos))?$datos['mostrar_admin']:0),
						'mostrar_publico'	=> ((array_key_exists('mostrar_publico', $datos))?$datos['mostrar_publico']:0),
						'uri'				=> $datos['uri'],
						'main'				=> ((array_key_exists('main', $datos))?$datos['main']:0)
						);

		if($insert['mostrar_admin'] == 0)
		{
			$insert['mostrar_publico'] = 0;
		}

		$id = beep_from($this->language_table)->values($insert)->insert();



		return $id;
	}

	function update_language($datos)
	{


		$update = array(
						'language'			=> $datos['language'],
                        'mostrar_admin'     => ((array_key_exists('mostrar_admin', $datos))?$datos['mostrar_admin']:0),
                        'mostrar_publico'   => ((array_key_exists('mostrar_publico', $datos))?$datos['mostrar_publico']:0),
						'uri'				=> $datos['uri']
						);

		if($update['mostrar_admin'] == 0)
		{
			$update['mostrar_publico'] = 0;
		}

		beep_from($this->language_table)->values($update)->where(array('id' => $datos['id'], 'portafolioid' => $this->get_portafolio()))->update();
	}


	function delete_language($id)
	{
		//si se está borrando el último lenguaje que le queda al usuario, no lo permitirá

		$query = beep_from($this->language_table)->where(array('portafolioid' => $this->get_portafolio()))->get();

		if($query->num_rows() == 1)
		{
			return FALSE;
		}

		//mimimimimimi soy jerrun y soy un pesado y como soy un vago en lugar de arreglar mis errores
		//hago que joseba cambie las cosas para que no falle mi código, mimimimimi

		$query = beep_from($this->language_table)->where(array('id' => $id, 'portafolioid' => $this->get_portafolio()))->get();

		if($query->is_empty())
		{
			return FALSE;
		}

		$row = $query->row();

		if($row->main == 1)
		{
			return FALSE;
		}

		beep_from($this->language_table)->where(array('id' => $id, 'portafolioid' => $this->get_portafolio()))->delete();
		beep_from($this->tableName)->where('language_id',$id)->delete();
	}


	public function rules_insert()
	{
		return array(array('field' => 'language', 'label' => 'Lenguaje', 'rules' => 'trim|min_length[3]|required|max_length[50]|callback_check_only_language|xss_clean'),
					 array('field' => 'mostrar_admin',   'label' => "Visible en admin", 'rules' => 'trim|integer|xss_clean'),
					 array('field' => 'mostrar_publico', 'label' => 'Visible públicamente', 'rules' => 'trim|integer|xss_clean'),
					 array('field' => 'uri', 'label' => 'Uri del lenguaje', 'rules' => 'trim|required|min_length[1]|max_length[3]|strtolower|alpha|callback_check_only_uri|xss_clean'),
					 array('field' => 'home', 'label' => 'Home', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'gallery', 'label' => 'Galería', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'about', 'label' => 'Acerca de', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'contact', 'label' => 'Contacto', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'contact_name', 'label' => 'Nombre', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'contact_email', 'label' => 'Email', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'contact_phone', 'label' => 'Teléfono', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'contact_message', 'label' => 'Mensaje', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'contact_send_button', 'label' => 'Botón de enviar', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'contact_message_ok', 'label' => 'Mensaje de éxito', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'access_button', 'label' => 'Botón de acceso', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					 array('field' => 'access_error', 'label' => 'Contraseña erronea', 'rules' => 'trim|min_length[3]|required|max_length[255]|xss_clean'),
					);
   }



	public function check_only_language($lang_name, $lang_id = FALSE)
	{
		if($lang_id == FALSE)
		{
			$query = beep_from($this->language_table)->where('portafolioid', $this->get_portafolio())->where('language', $lang_name)->get();
		}
		else
		{
			$query = beep_from($this->language_table)->where('portafolioid', $this->get_portafolio())->where('language', $lang_name)->where('id != ', $lang_id)->get();
		}

		if($query->num_rows() == 0)
		{
			return TRUE;
		}
		return FALSE;
	}

	public function check_only_uri($uri, $lang_id = FALSE)
	{
		if($lang_id == FALSE)
		{
			$query = beep_from($this->language_table)->where('portafolioid', $this->get_portafolio())->where('uri', $uri)->get();
		}
		else
		{
			$query = beep_from($this->language_table)->where('portafolioid', $this->get_portafolio())->where('uri', $uri)->where('id != ', $lang_id)->get();
		}

		if($query->num_rows() == 0)
		{
			return TRUE;
		}
		return FALSE;
	}



    /*
     * ================================================================
     *
     *      ESTA SECCIÓN DEMUESTRA QUE LA VIDA ES MIERDA Y HAY QUE
     *      LIARSE PARA HACER CUALQUIER GILIPOLLEZ LO INDECIBLE
     *
     * ================================================================
     */


    // 1 intenta sacar el texto del lenguaje activo
    // 2 si no existe, intenta sacar el texto del lenguaje principal
    // 3 si no existe saca el primer texto que haya.
    function get_public_data($field, $id = FALSE)
    {
        if($id === FALSE)
        {
            $id = $this->auth->get_portafolio_id();
        }

        $retorno = $this->get_field_data($id, $field, TRUE);

        $language_id =$this->get_active_language();

        $alternative = $this->field_with_alternatives($field);

        if($retorno == FALSE)
        {
            return '';
        }

        if($alternative == FALSE and !array_key_exists($language_id, $retorno))
        {
            return '';
        }
        elseif(array_key_exists($language_id, $retorno))
        {
            return $retorno[$language_id]['text_data'];
        }
        elseif(!is_null($this->main_language) && array_key_exists($this->main_language, $retorno))
        {
            return $retorno[$this->main_language]['text_data'];
        }
        else
        {
           $primero = reset($retorno);
           return $primero['text_data'];
        }

    }

    // 1 intenta sacar el texto del lenguaje activo
    // 2 si no existe, intenta sacar el texto del lenguaje principal
    // 3 si no existe saca el primer texto que haya.
    //entrada array('inner_id', 'field')
    function get_public_multidata($fields)
    {
        $retorno = $this->get_field_multidata($fields, TRUE);
        $language_id =$this->get_active_language();

        foreach($fields as $field)
        {
            $alternative = $this->field_with_alternatives($field['field']);

            if(!array_key_exists($field['field'], $retorno) || !array_key_exists($field['inner_id'], $retorno[$field['field']]))
            {
                $retorno[$field['field']][$field['inner_id']] = '';
            }
            elseif($retorno[$field['field']][$field['inner_id']] == FALSE)
            {
                $retorno[$field['field']][$field['inner_id']] = '';
            }
            elseif($alternative == FALSE and !array_key_exists($language_id, $retorno[$field['field']][$field['inner_id']]))
            {
                $retorno[$field['field']][$field['inner_id']] = '';
            }
            elseif(array_key_exists($language_id, $retorno[$field['field']][$field['inner_id']]))
            {
                $retorno[$field['field']][$field['inner_id']] = $retorno[$field['field']][$field['inner_id']][$language_id]['text_data'];
            }
            elseif(array_key_exists($this->main_language, $retorno[$field['field']][$field['inner_id']]))
            {
                $retorno[$field['field']][$field['inner_id']] = $retorno[$field['field']][$field['inner_id']][$this->main_language]['text_data'];
            }
            else
            {
                $primero = reset($retorno[$field['field']][$field['inner_id']]);
                $retorno[$field['field']][$field['inner_id']] = $primero['text_data'];
            }
        }
        return $retorno;
    }


}