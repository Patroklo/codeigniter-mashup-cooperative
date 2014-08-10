<?php

require_once APPPATH.'modules/cy_upload/models/upload_model.php';

class images_upload_model extends upload_model {

    //public $tableName   = 'gallery_images';
    public $directory   = 'gallery';
    public $className   = 'image';
    
    
    function __construct($config = array())
    {
        parent::__construct($config);
    }
    
    public function rules()
    {
        return  array(array('field' => 'files', 'label' => 'la imagen', 'rules' => 'file_required|file_size_max[5MB]|file_allowed_type[image]|file_image_maxdim[10000,10000]|callback_check_portafolio_size'));
    }
	
	public function rules_update()
	{
        return  array('field' => 'files', 'label' => 'la imagen', 'rules' => 'file_size_max[5MB]|file_allowed_type[image]|file_image_maxdim[10000,10000]|callback_check_portafolio_size');
	}

	/* 	# 	Columna 	Tipo 	Cotejamiento 	Atributos 	Nulo 	Predeterminado 	Extra 	Acción
	1 	id 	int(11) 			No 	Ninguna 	AUTO_INCREMENT 	Cambiar Cambiar 	Eliminar Eliminar 	Más Mostrar más acciones
	2 	image_id 	int(11) 			No 	Ninguna 		Cambiar Cambiar 	Eliminar Eliminar 	Más Mostrar más acciones
	3 	gallery_id 	int(11) 			No 	Ninguna 		Cambiar Cambiar 	Eliminar Eliminar 	Más Mostrar más acciones
	4 	url 	varchar(255) 	latin1_swedish_ci 		No 	Ninguna 		Cambiar Cambiar 	Eliminar Eliminar 	Más Mostrar más acciones
	5 	insert_date 	datetime 			No 	Ninguna 		Cambiar Cambiar 	Eliminar Eliminar 	Más Mostrar más acciones
	6 	image_order 	int(11)*/
	
	public function insert($id)
	{
		if (!$this->auth->get_portafolio_active())
		{
			return FALSE;
		}
		
		
		//comprueba tamaño de todo lo insertado
		//si supera el máximo asignado, derp, fuera.

		//inserta parent
		$data = parent::insert($id);

		//comprobamos el tamaño de la imagen con el portafolio
		$this->load->model('account_restrictions_model');
		$this->account_restrictions_model->activate_account();
		if($this->account_restrictions_model->check_portafolio_size(array('file_size' => $data['file_size'], 'gallery_id' => $id)) == FALSE)
		{
			//borramos la imagen y retornamos false
			parent::delete($data['id']);
			return "TAMAÑO DE GALERÍA SUPERADO";
		}
		
		
		$query = $this->me()->where('gallery_id', $id)->select('max(image_order) as orden', FALSE)->get();
		
		$gallery_id = $id;
		
		$row = $query->row();
		
		$orden = $row->orden;
		
		$insert_data = array(
							'image_id'   => $data['id'],
							'gallery_id' => $gallery_id,
							'url'		 => $data['id'],
							'insert_date'=> date("Y-m-d H:i:s"),
							'image_order'=> $orden + 1
							);
		$id = $this->me()->values($insert_data)->insert();
		
		
		
		//creamos copias de la imagen en distintos tamaños
		$lista_copias = $this->create_copies('imagenes_galeria');

		// $update_data['copies'] = json_encode($lista_copias);
// 		
		// $this->me()->values($update_data)->where('id', $id)->update();

		//esto es importante, porque es el ID de la tabla de imagenes
		//lo que nos importa y no el id de la imagen subida en la tabla
		//de uploads
		$this->carga->id = $id;

		$this->update_sizes($gallery_id);
		
		
		//si la galería no tiene una imagen de portada, se pondrá esta
		$this->load->model('images_model');
		$query = $this->images_model->me()->where('gallery_id', $gallery_id)->where('cover', '1')->get();
		

		if($query->is_empty())
		{
			$this->me()->values(array('cover' => '1'))->where('id', $id)->update();
		}
		
		return TRUE;

	}


	public function update()
	{
		if (!$this->auth->get_portafolio_active())
		{
			return FALSE;
		}
		
		
		if($this->carga == FALSE)
		{
			return FALSE;
		}	
		
		$previous_copies = $this->images_model->carga->get_data('copies');
		
		$retorno = parent::update();
		
		if($retorno != FALSE)
		{
			$image_copies = json_decode($previous_copies, TRUE);

			foreach($image_copies as $i)
			{
				$this->images_upload_model->delete($i);
			}
		}
		
		return $retorno;
	}
	
	public function update_sizes($gallery_id)
	{
		$query = beep_from($this->insert_tableName)->where('classid', $this->classData['id'])
												   ->where('innerid', $gallery_id)
												   ->select('sum(file_size) as file_size', false)
												   ->get();
		
		$row = $query->row();
		
		if(!is_null($row->file_size))
		{
			$tamano = $row->file_size;

			$this->load->model('gallery_model');
			
			$this->gallery_model->me()->values(array('size' => $tamano))->where('id', $gallery_id)->update();
			
			$query = $this->gallery_model->me()->where('id', $gallery_id)->get();
			
			$gallery = $query->row();

			$this->load->model('portafolio_model');
			$this->portafolio_model->update_sizes($gallery->portafolioid);
		}
		
	}
	
	public function check_portafolio_size($gallery_id)
	{

		$this->do_upload($this->get_field_name());
		$upload_data = $this->upload_data();

        //no ha habido subida
        if(is_null($upload_data['file_size']))
        {
            return TRUE;
        }
		
		$image = $upload_data;

		//comprobamos el tamaño de la imagen con el portafolio
		$this->load->model('account_restrictions_model');
		
			
		$this->account_restrictions_model->activate_account();

		if($this->account_restrictions_model->check_portafolio_size(array('file_size' => $image['file_size'], 'gallery_id' => $gallery_id)) == FALSE)
		{
			return FALSE;
		}
		return TRUE;
	}


    function retorno_OK()
    {
    	$this->load->model('images_model');
		$this->images_model->carga_object($this->carga->id);
		
          return json_encode(array(
                'result' => 'ok',
                'file' => array(
                    'id'    => $this->carga->id,
                    'path'  => $this->images_model->carga->image('sm_crop')
                )
            ));
    }
	
	function create_copies($type)
	{
		
		$lista_copias = parent::create_copies($type);
		
		$update_data = array('copies' => json_encode($lista_copias));

		$this->me()->where('image_id', $this->carga->id)->values($update_data)->update();

		return $lista_copias;
	}


	public function show_upload($name = FALSE)
	{

		if ($this->auth->get_portafolio_active())
		{
			return parent::show_upload($name);
		}
		else
		{
			if ($this->auth->datos->get_data('portafolio_admin'))
			{
				$return	= '<div class="alert alert-danger">Tu plan ha caducado. Para poder subir archivos <a href="/admin/plan">has de renovarlo</a>.</div>';
			}
			else
			{
				$return	= '<div class="alert alert-danger">Tu plan ha caducado. Para poder subir archivos, el administrador del portafolio ha de renovarlo.</div>';
			}
		
			return $return;
		
		}

	}


}