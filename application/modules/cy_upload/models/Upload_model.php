<?php

class Upload_model extends base_model{

	
	public $directory = FALSE;
	public $className = FALSE;


	public $classData = array();
	public $objectLoaded = FALSE;


	public $tableName = 'upload_data';

	function __construct($config = array())
	{
		$this->load->library('upload');

		$this->load->library('cy_upload/Cy_uploader', $config);
		
		$this->load->model('cy_upload/Upload_engine');
		
		$this->classData = $this->Upload_engine->get_classData($this->className);
		
	}

	//rules que servirán para cy_uploader y form_validation
	//forma recomendada de mostrarlas
	/*		return array( array('field' => 'files',
	 									   'label' => 'la foto',
	  									   'rules' => 'required|file_size_max['.$this->config->item('max_size_photo').'KB]|file_allowed_type[image]|file_image_maxdim['.$this->config->item('max_width_photo').','.$this->config->item('max_height_photo').']|file_image_mindim['.$this->config->item('min_width_photo').','.$this->config->item('min_height_photo').']|xss_clean')
	 							)
                        );

        si es un multiupload hay que poner las rules como las del upload de codeigniter
        $config['upload_path'] = './uploads/procesar';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = '100';
        $config['max_width'] = '1024';
        $config['max_height'] = '768';
	 */
	 
	public function rules()
	{
		return array();
	}
	
	public function engine_initialization_data()
	{
		return array('directory' 	=> $this->directory,
					 'className'	=> $this->className,
					 'fieldName'	=> $this->get_field_name()
					 );
	}
	
	
	public function get_field_name()
	{
		$field_rules = $this->rules();

		if(array_key_exists('field', $field_rules[0]))
		{
			$field = $field_rules[0]['field'];
		}
		else
		{
			$field = 'userfile';
		}

		return $field;
	}
	

	public function insert($id)
	{
		$this->Upload_engine->initialize($this->engine_initialization_data());
		$insert_data = $this->Upload_engine->insert($id);
		$this->carga = $insert_data;
		
		return $insert_data;
	}


	//atención: borra el anterior fichero cuyo id le pasamos y crea uno nuevo
	//esto sirve sobre todo para evitar el problema de que no se refresque la imagen
	//al hacer el update
	public function update($data = NULL)
	{
		//borramos el viejo fichero y volvemos a crear una nueva entrada
		//para simplificar llamaremos a delete e insert
		if($this->carga === FALSE)
		{
			return FALSE;
		}
		$previous_data = $this->carga;
	
		$this->Upload_engine->initialize($this->engine_initialization_data());
		$insert_data = $this->Upload_engine->insert($this->carga->innerid);
		
		//solo retornará false si no ha habido un upload.
		if($insert_data !== FALSE)
		{
			$this->delete($previous_data->id);
		}

		$this->carga = $insert_data;
		

		return $insert_data;

	}


    //esta función sólo se llamará cuando haya tan sólo la posibilidad de que
    //el objeto al que queremos enlazar el fichero pueda tener tan sólo 1 o 0 ficheros asociados
    //si hay posibilidad de que se puedan asociar más de 1 fichero al objeto
    //NO DEBEREMOS LLAMAR A ESTA FUNCIÓN
    //
    //atención: borra el anterior fichero cuyo id le pasamos y crea uno nuevo
    //esto sirve sobre todo para evitar el problema de que no se refresque la imagen
    //al hacer el update
    public function insert_update($class_id)
    {
        //borramos el viejo fichero y volvemos a crear una nueva entrada
        //para simplificar llamaremos a delete e insert
		
		$this->Upload_engine->initialize($this->engine_initialization_data());
		$field = $this->Upload_engine->get_field_name();

		if($this->Upload_engine->do_upload($field) == FALSE)
		{
			return FALSE;
		}

        $this->Upload_engine->delete_inner_id($class_id);

        return $this->Upload_engine->insert($class_id);
    }
	
	
	
	public function delete($id = FALSE)
	{
		if($id == FALSE and $this->carga == FALSE)
		{
			return FALSE;
		}
		elseif($id == FALSE)
		{
			$id = $this->carga->id;
		}

		$this->Upload_engine->initialize($this->engine_initialization_data());
		
		return $this->Upload_engine->delete($id);
	}



	//modifica el tamaño de la imagen seleccionada por id de tabla
	//el parametro $resize_data debe ser $resize_data = array('type' =>(resize|resize_crop|crop|resize_smaller|resize_crop_smaller|crop_smaller)
	//														  'width'=>x
	//														  'height'=>y)
	public function change_image_size($id, $resize_data)
	{
		$query = beep_from($this->tableName)->where('id', $id)->get();

		if($query->num_rows() == 0)
		{
			return FALSE;
		}

		$row = $query->row(0);

		if(getimagesize($row->file) == FALSE)
		{
			return FALSE;
		}

		$this->load->library('cy_upload/image_moo');
		$nueva_imagen = $this->image_moo->load($row->file);

		if($resize_data['type'] == 'resize')
		{
			$nueva_imagen = $nueva_imagen->resize($resize_data['width'], $resize_data['height']);
		}
		elseif($resize_data['type'] == 'resize_crop')
		{
			$nueva_imagen = $nueva_imagen->resize_crop($resize_data['width'], $resize_data['height']);
		}
		elseif($resize_data['type'] == 'crop')
		{
			$nueva_imagen = $nueva_imagen->crop($resize_data['width'], $resize_data['height']);
		}
		elseif($resize_data['type'] == 'resize_smaller')
		{
			if($nueva_imagen->width > $resize_data['width'] and $nueva_imagen->height > $resize_data['height'])
			{
				$nueva_imagen = $nueva_imagen->resize($resize_data['width'], $resize_data['height']);
			}
		}
		elseif($resize_data['type'] == 'resize_crop_smaller')
		{
			if($nueva_imagen->width > $resize_data['width'] and $nueva_imagen->height > $resize_data['height'])
			{
				$nueva_imagen = $nueva_imagen->resize_crop($resize_data['width'], $resize_data['height']);
			}
		}
		elseif($resize_data['type'] == 'crop_smaller')
		{
			if($nueva_imagen->width > $resize_data['width'] and $nueva_imagen->height > $resize_data['height'])
			{
				$nueva_imagen = $nueva_imagen->crop($resize_data['width'], $resize_data['height']);
			}
		}

		$nueva_imagen->save($new_image,TRUE);
		
		return TRUE;

	}




	//sobreescribimos carga para que sólo se pueda cargar desde cada model de upload
	//archivos que tengan que ver con ese model. De ahí lo de classid
	//por defecto cargará el fichero relacionado con innerid, si se quiere cargar u nfichero
	//específicamente, habrá que hacer un carga con un array('id' => $id)

	function carga($data, $object = FALSE)
	{

		if (!is_array($data)) {
			$data = array('id' => $data);
		}
		$query = beep_from($this -> tableName) -> where($data) -> where(array('classid' => $this->classData['id'])) -> limit(1) -> get();
		if ($query -> num_rows() > 0) {
			$query -> result();
			if ($object == FALSE) {
				$this -> carga = $query -> row(0);
				$this->objectLoaded = FALSE;
			} 
			elseif($object == 'array')
			{
				$this -> carga = $query -> row(0, 'array');
				$this->objectLoaded = FALSE;
			}
			else {
				$this -> carga = $query -> row(0, $this -> object);
				$this->objectLoaded = TRUE;
			}
		} else {
			$this -> carga = FALSE;
		}

		return $this -> carga;
	}

    function retorno_OK()
    {
          return json_encode(array(
                'result' => 'ok',
                'file' => array(
                    'id'    => $this->carga->id,
                    'path'  => base_url().$this->carga->file
                )
            ));
    }

    function retorno_KO()
    {
          return json_encode(array(
                'result'                => 'error',
                'message_validation'    => validation_errors(),
                'file_name'             => $_FILES['files']['name'],
                'message_upload'        => $this->upload->display_errors()
            ));
    }

	//posibles añadidos: cambiar nombre a fichero

	private function copy_image($resize_data = FALSE)
	{
		$this->load->library('cy_upload/image_moo');
		if($this->carga === FALSE)
		{
			return FALSE;
		}

		if($this->objectLoaded == TRUE)
		{
			$image_dir = $this->carga->get_data('file');

			$data = array(
				'classid'		=> $this->carga->get_data('classid'),
				'innerid'		=> $this->carga->get_data('innerid'),
				'upload_date'	=> date("Y-m-d H:i:s"),
				'dir'			=> $this->cy_uploader->get_directory($this->directory),
				'format'		=> $this->carga->get_data('format'),
				'file_size'		=> $this->carga->get_data('file_size'),
			 );

			$id = beep_from($this->tableName)->values($data)->insert();

			$update_data = array(
									'filename' => $id,
									'file'	   => $data['dir'].$id.$this->carga->get_data('format')
								);

			$data['id']			= $id;
			$data['filename'] 	= $update_data['filename'];
			$data['file']		= $update_data['file'];

			beep_from($this->tableName)->values($update_data)->where('id', $id)->update();

			$new_image = $data['file'];
		}
		else
		{
			
			$image_dir = $this->carga->file;

			$data = array(
				'classid'		=> $this->carga->classid,
				'innerid'		=> $this->carga->innerid,
				'upload_date'	=> date("Y-m-d H:i:s"),
				'dir'			=> $this->cy_uploader->get_directory($this->directory),
				'format'		=> $this->carga->format,
				'file_size'		=> $this->carga->file_size,
			 );

			$id = beep_from($this->tableName)->values($data)->insert();

			$update_data = array(
									'filename' => $id,
									'file'	   => $data['dir'].$id.$this->carga->format
								);

			$data['id']			= $id;
			$data['filename'] 	= $update_data['filename'];
			$data['file']		= $update_data['file'];

			beep_from($this->tableName)->values($update_data)->where('id', $id)->update();

			$new_image = $data['file'];

		}


		if($resize_data != FALSE)
		{
			$nueva_imagen = $this->image_moo->load($image_dir);

			$instructions = explode('|',$resize_data['action']);

			foreach($instructions as $inst)
			{
				if($inst == 'resize')
				{
					$nueva_imagen = $nueva_imagen->resize($resize_data['width'], $resize_data['height']);
				}
				elseif($inst == 'resize_crop')
				{
					$nueva_imagen = $nueva_imagen->resize_crop($resize_data['width'], $resize_data['height']);
				}
				elseif($inst == 'crop')
				{
					$nueva_imagen = $nueva_imagen->crop($resize_data['width'], $resize_data['height']);
				}
				elseif($inst == 'resize_smaller')
				{
					if($nueva_imagen->width > $resize_data['width'] and $nueva_imagen->height > $resize_data['height'])
					{
						$nueva_imagen = $nueva_imagen->resize($resize_data['width'], $resize_data['height']);
					}
				}
				elseif($inst == 'resize_crop_smaller')
				{
					if($nueva_imagen->width > $resize_data['width'] and $nueva_imagen->height > $resize_data['height'])
					{
						$nueva_imagen = $nueva_imagen->resize_crop($resize_data['width'], $resize_data['height']);
					}
				}
				elseif($inst == 'crop_smaller')
				{
					if($nueva_imagen->width > $resize_data['width'] and $nueva_imagen->height > $resize_data['height'])
					{
						$nueva_imagen = $nueva_imagen->crop($resize_data['width'], $resize_data['height']);
					}
				}
			}

			$nueva_imagen->save($new_image,TRUE);
		}
		else
		{
			copy($image_dir, $new_image);
		}

		return $data;

	}


	function create_copies($type)
	{
		$this->load->helper('cy_upload/photo_size');

		$sizes = photo_size_list($type);

		if($sizes === FALSE)
		{
			return FALSE;
		}

		if($this->carga == FALSE)
		{
			return FALSE;
		}


		foreach($sizes as $key => $size)
		{
			$image = $this->copy_image($size);
			$lista_copias[$key] = $image['id'];
		}

		return $lista_copias;

	}

}
