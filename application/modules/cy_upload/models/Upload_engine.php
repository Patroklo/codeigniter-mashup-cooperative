<?php

class Upload_engine extends CI_Model{


	public $fieldName;
	public $tableName = 'upload_data';
	public $classData = array();
	public $directory;
	public $active_className;
	
	
	function __construct()
	{
		$this->load->library('upload');

		$this->load->library('cy_upload/Cy_uploader');
	}

	/**
	 * Initializes the data of the model in order to work with its methods
	 * This method must be called before any other call except "get_classData"
	 *
	 * @return Void
	 * @author Patroklo
	 */
	function initialize($data)
	{
		$this->fieldName 		= $data['fieldName'];
		$this->directory 		= $data['directory'];
		$this->active_className = $data['className'];
		
		if(array_key_exists('tableName', $data))
		{
			$this->tableName		= $data['tableName'];
		}
		
		
		// row del tipo de fichero que se quiere subir
		// classdata debería guardarse en un array de classnames, así ahorramos trabajo de llamadas y demás
		$this->get_classData($data['className']);
		
		
		// function check fields para comprobar que esté bien rellenado el engine
		// habrá que meter el check en los upload, insert y demás
		
		$this->check_method_initialization();
		
	}

	/**
	 * Checks if the method has all its data well initialized; if not, will throw an exception
	 *
	 * @return void
	 * @author  Patroklo
	 */
	function check_method_initialization() 
	{
		$valid = TRUE;
		
		if(is_null($this->fieldName) or is_null($this->active_className))
		{
			$valid = FALSE;
		}
		
		if(!array_key_exists($this->active_className, $this->classData))
		{
			$valid = FALSE;
		}
		
		if($valid == FALSE)
		{
			throw new Exception("No se ha definido una clase de upload válida: ".$this->active_className, 1);
			die();
		}
	}
	
	/**
	 * Lazy loads all the classNames of the table and returns the data of the given className.
	 *
	 * @return array
	 * @author  Patroklo
	 */
	function get_classData($className)
	{
		if(empty($this->classData))
		{
			$query = beep_from('upload_classes')->get();
	
			foreach($query->result('array') as $row)
			{
				$this->classData[$row['class']] = $row;
			}
		}
				
		if(!array_key_exists($className, $this->classData))
		{
			throw new Exception("No se ha definido una clase de upload válida: ".$className, 1);
			die();
		}
		
		return $this->classData[$className];
	}
	

	function get_field_name()
	{
		return $this->fieldName;
	}


	public function upload_data()
	{
		return $this->cy_uploader->data();
	}

	public function do_upload($field = FALSE)
	{
		if($field === FALSE)
		{
			$field = $this->get_field_name();
		}

		return $this->cy_uploader->do_upload($field);
	}


	function insert($id)
	{

		$this->check_method_initialization();

		if(is_null($id) or !is_numeric($id))
		{
			throw new Exception("El id de referencia para insertar un nuevo archivo debe ser un numérico.", 1);
			die();
		}

		$field = $this->get_field_name();

		$this->do_upload($field);
		$upload_data = $this->upload_data();

        //no ha habido subida
        if(is_null($upload_data['file_size']))
        {
            return FALSE;
        }

		if(!is_file($upload_data['full_path']))
		{
			throw new Exception("Error interno, no se ha seleccionado ningún archivo.", 1);
			die();
		}

		$classData = $this->get_classData($this->active_className);

		$data = array(
						'classid'		=> $classData['id'],
						'innerid'		=> $id,
						'upload_date'	=> date("Y-m-d H:i:s"),
						'dir'			=> $this->cy_uploader->get_directory($this->directory),
						'format'		=> $upload_data['file_ext'],
						'file_size'		=> $upload_data['file_size']
					 );

		$id = beep_from($this->tableName)->values($data)->insert();


		$new_ext = strtolower($upload_data['file_ext']);

		if($new_ext == '.jpeg')
		{
			$new_ext = '.jpg';
		}

		$update_data = array(
								'filename' => $id,
								'file'	   => $data['dir'].$id.$new_ext
							);

		$data['id']			= $id;
		$data['filename'] 	= $update_data['filename'];
		$data['file']		= $update_data['file'];

		if($new_ext == '.jpg' or $new_ext == '.jpeg' or $new_ext == '.tiff')
		{
			$exif_data = exif_read_data($upload_data['full_path']);

			if($exif_data !== FALSE)
			{
				$update_data['exif'] 	= json_encode($exif_data);
			}
		}

		beep_from($this->tableName)->where('id', $id)->values($update_data)->update();


		rename($upload_data['full_path'], $update_data['file']);

		return $data;

	}



	public function delete_inner_id($id)
	{
		$this->check_method_initialization();
		
		$classData = $this->get_classData($this->active_className);
		
		$query = beep_from($this->tableName)->where('innerid', $id)->where('classid', $classData['id'])->get();

		if($query->num_rows() == 0)
		{
			return FALSE;
		}
		foreach($query->result() as $row)
		{
			$this->delete($row->id);
		}

	}

	public function delete($id)
	{
		$this->check_method_initialization();
		
		$classData = $this->get_classData($this->active_className);
		
		$query = beep_from($this->tableName)->where('id', $id)->where('classid', $classData['id'])->get();

		if($query->num_rows() == 0)
		{
			return FALSE;
		}

		$row = $query->row();

		if(is_file($row->file))
		{
			unlink($row->file);
		}

		beep_from($this->tableName)->where('id', $id)->delete();

		return TRUE;
	}



	//hay que llamar a esta función en una función de controller que recibe ficheros multiupload
	//ya que envía el $_FILES mal montado
	//solo usar cuando el S_FILES esté mal montado, no siempre lo estará
	public function prepare_multiupload_files()
	{
        foreach($_FILES as &$f)
        {
            foreach($f as $key => $p)
            {
                if(is_array($p))
                {
                    $f[$key] = $p[0];
                }
            }
        }
	}
	
}