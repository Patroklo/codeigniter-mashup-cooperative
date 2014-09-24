<?php
$CI =& get_instance();

$CI->load->_include_class('modules/cy_messages/models/Cy_messages_model');

class Cy_comments_model extends Cy_messages_model
{

    // cargar del config datos:
    /*
     *      1 - si permite comentarios de tipo anónimo, solo registrado o registrado con cuentas estilo facebook (oauth 2 https://github.com/thephpleague/oauth2-client)
     *      2 - si cargan siempre todos los mensajes o van cargando poco a poco (paginación infinita)
     *      3 - si se ven como ascendentes o descendentes en creación
     *      4 - inicializar:
     *
     *                  se establece el tipo de mensaje (
     *
     */

    public $tableName			= 'comments';

	public $allowed_comments;

	public $list_view;

	public $comment_view;

	public $new_comment_form_view;

	public $edit_comment_form_view;

	public $recaptcha_view;
	
	public $login_view;
	
	public $comment_form_position;

	public $order_type          = 'ASC';

	public $object 				= 'comment_object';

	public $carga 				= FALSE;

	public $message_type;

	public $reference_id;
	
	public $inner_id;

	public $config_type         = 'normal';

	public $comment_types_table = 'comment_types';

    public $stream				= array();

    function __construct()
    {
	    $this->load->config('cy_comments/comments', TRUE);

	    $config_data = $this->config->item($this->config_type, 'comments');

	    foreach ($config_data as $key => $data)
	    {
		    $this->{$key} = $data;
	    }

	    $array_checks = array('message_type', 'allowed_comments');

		foreach ($array_checks as $var_name)
		{
			if ($this->{$var_name} === NULL)
			{
				throw new Exception('Comment library not initialized. (NO '.$var_name.')');
			}
		}

        parent::__construct();
    }

    /**
     * ========================================================================================================
     * ========================================================================================================
     *              CRUD CRUD CRUD CRUD
     * ========================================================================================================
     * ========================================================================================================
     */

	function carga($data, $object = TRUE)
	{
		$return_data = parent::carga($data, $object);

		$this->reference_id = $this->carga->get_data('reference_id');
		$this->message_type = $this->carga->get_data('message_type');
		$this->inner_id		= $this->carga->get_data('inner_id');
		
		return $return_data;
	}


    /**
     * Inserts new message
     *
     * @return inserted id
     * @author Joseba J
     */

    function insert($data)
    {

        if ( ! $this->msg_insert_inner_id_permission($data['reference_id'], $data['inner_id']))
	    {
		    return FALSE;
	    }

	    $this->reference_id = $data['reference_id'];
		$this->message_type = $data['message_type'];
		$this->inner_id		= $data['inner_id'];

	    // check message_type
	    // only neccesary in this case, because it can't be changed once inserted
	    if ( ! $this->comment_check_type())
	    {
		    return FALSE;
	    }
		$install_data = array();
	    $install_data['message_type']   = $this->message_type;
	    $install_data['reference_id']   = $this->reference_id;
		$install_data['inner_id']		= $this->inner_id;
	    $install_data['user_id']		= ($this->auth->logged_in() ? $this->auth->get_user_id() : 0);

	    if (array_key_exists('anonymous_name', $data))
	    {
		    $install_data['anonymous_name'] = $data['anonymous_name'];
	    }
	    $install_data['message_text'] = $data['message_text'];
	    $install_data['ip']				= $this->input->ip_address();
	    $install_data['creation_date']	= date("Y-m-d H:i:s");

	    $new_id = beep_from($this->tableName)->values($install_data)->insert();

        return $this->carga($new_id);
    }

    function update($data)
    {
	    if ($this->carga === FALSE)
	    {
		    return FALSE;
	    }

	    if (!$this->msg_update_permission($this->carga->get_data('id')))
	    {
		    return FALSE;
	    }

	    // we don't let changes of subforum in a message from update method
	    if (array_key_exists('reference_id', $data))
	    {
		    unset($data['reference_id']);
	    }

		if (array_key_exists('inner_id', $data))
		{
			unset($data['inner_id']);
		}

	    if (array_key_exists('message_type', $data))
	    {
		    unset($data['message_type']);
	    }


	    $data['edited'] 		= 1;
	    $data['edition_date']	= date("Y-m-d H:i:s");
	    $data['edition_ip']		= $this->input->ip_address();

	    foreach ($data as $key => $d)
	    {
		    $this->carga->set_data($key, $d);
	    }

	    $this->carga->save();
    }

	function delete()
	{
		if ($this->carga === FALSE)
		{
			return FALSE;
		}

		if ( ! $this->msg_delete_permission($this->carga->get_data('id')))
		{
			return FALSE;
		}

		$this->carga->set_data('deleted', '1');
		$this->carga->save();

		$this->descarga();

		return TRUE;
	}


    /**
     * loads a stream of comments
     *
     * @return array of comments
     * @author  Joseba J
     */
    function load_comments($limit = FALSE, $offset = 0)
    {

        if ( ! $this->msg_read_permission($this->message_type))
        {
            return FALSE;
        }

        $data_original 					= array();
        $data_original['reference_id']	= $this->reference_id;
		
		if ($this->inner_id != NULL)
		{
			$data_original['inner_id'] = $this->inner_id;
		}

        $query		  = $this->get_query()->where($data_original);


        if($limit != FALSE)
        {
            $query	  = $query->limit($limit)->offset($offset);
        }

	    $message_list = $query->get();

	    // add anonymous objects
	    return $this->check_anonymous($message_list);
    }


    protected function get_query()
    {
        return beep($this->object)->where(array('deleted' => 0, 'message_type' => $this->message_type))
	                              ->order_by('id', $this->order_type);
    }

	/**
	 * ========================================================================================================
	 * ========================================================================================================
	 *              PUBLIC METHODS
	 * ========================================================================================================
	 * ========================================================================================================
	 */

	/**
	 *
	 * Shows all comments from a reference_id returning an html view
	 *
	 * @param $reference_id
	 * @param bool $limit
	 * @param int $offset
	 * @return string
	 */
	public function show_comments($reference_id, $inner_id = NULL, $limit = FALSE, $offset = 0)
	{

		$this->reference_id = $reference_id;
		$this->inner_id		= $inner_id;

		if ( ! $this->msg_read_permission($this->reference_id))
		{
			return FALSE;
		}

		$view_data = new stdClass();

		$view_data->comment_list = $this->load_comments($limit, $offset);

		$view_data->single_comment_view = $this->comment_view;

		$view_data->can_comment = FALSE;
		$view_data->login_view = $this->logged_comments();


		if ($this->msg_insert_inner_id_permission($this->reference_id, $this->inner_id) and $view_data->login_view == FALSE)
		{
			$this->load->model('cy_comments/form_models/Cy_comments_form');
			$this->load->helper('form');

			// add the jquery call for commenting
			$this->js_load->add('comments.form()');

			$view_data->comment_form_data = new stdClass();
			$view_data->comment_form_data->reference_id = $this->reference_id;
			$view_data->comment_form_data->comment_type = $this->message_type;
			$view_data->can_comment  = TRUE;
			$view_data->comment_form_view = $this->new_comment_form_view;
			$view_data->comment_form_position = $this->comment_form_position;
		}

		$return_html = $this->load->view($this->list_view, $view_data, TRUE);

		return $return_html;
	}

	/**
	 *
	 * returns a view with the comment loaded (useful when inserting via ajax comments)
	 *
	 * @return string
	 */
	public function show_comment()
	{
		if ( ! $this->carga)
		{
			return FALSE;
		}

		if ( ! $this->msg_read_permission($this->carga->get_data('id')))
		{
			return FALSE;
		}
		
		$view_data = new stdClass();

		$view_data->comment_list = array($this->carga);

		$return_html = $this->load->view($this->comment_view, $view_data, TRUE);

		return  $return_html;
	}

	public function show_edit_comment()
	{
		if ( ! $this->carga)
		{
			return FALSE;
		}

		if ( ! $this->msg_update_permission($this->carga->get_data('id')))
		{
			return FALSE;
		}

		$this->load->model('cy_comments/form_models/Cy_comments_form');

		$this->Cy_comments_form->carga($this->carga);

		$this->load->helper('form');

		$view_data = new stdClass();
		$view_data->reference_id = $this->reference_id;
		$view_data->comment_type = $this->message_type;
		$view_data->comment_id   = $this->carga->get_data('id');
		$view_data->inner_id	 = $this->inner_id;

		return $this->load->view($this->edit_comment_form_view, $view_data, TRUE);
	}

	public function show_errors()
	{
		$errors = $this->Cy_comments_form->get_errors();

		$return_data = $errors['global_error'];

		return $return_data;

	}

    /**
     * ========================================================================================================
     * ========================================================================================================
     *              SECURITY METHODS
     * ========================================================================================================
     * ========================================================================================================
     */


	/**
	 * Checks if the actual type exists as comment type in the comment_type table
	 */
		function comment_check_type()
		{
			$query = $this->correcaminos->beep_from($this->comment_types_table)->where(array('comment_type' => $this->message_type))->get();

			if ($query->num_rows() > 0)
			{
				return TRUE;
			}
			return FALSE;
		}

     
     	function logged_comments()
		{
			if($this->auth->logged_in() != TRUE and $this->allowed_comments != 'anonymous')
			{
				return $this->login_view;
			}
			return FALSE;
		}
     
		function msg_insert_inner_id_permission($reference_id, $inner_id)
		{
			$this->load->model('cy_comment_admin/Cy_comment_admin');


			
			return ! $this->Cy_comment_admin->is_closed($this->message_type, $reference_id, $inner_id);
		}

		function msg_update_permission()
		{
			if ($this->carga === FALSE)
			{
				return FALSE;
			}

			// can't edit anonymous comments
			if ($this->carga->get_data('user_id') == 0)
			{
				return FALSE;
			}

			if ($this->auth->is_admin())
			{
				return TRUE;
			}

			if ($this->carga->get_data('user_id') == $this->auth->get_user_id())
			{
				return TRUE;
			}

			if ($this->global_update_permission())
			{
				return TRUE;
			}

			return $this->carga->can_update();
		}


		function msg_delete_permission()
		{
			if ($this->carga === FALSE)
			{
				return FALSE;
			}

			return  $this->carga->can_delete();
		}


		/**
		 * gives a global permission about accessing to a specific or
		 * set of messages
		 *
		 * @return boolean
		 * @author  Joseba J
		 */
		function global_read_permission()
		{
			return TRUE;
		}


		function global_insert_permission()
		{
			return TRUE;
		}

		function global_update_permission()
		{
			return TRUE;
		}


		function msg_read_permission($message_id)
		{
			if ($this->global_read_permission())
			{
				return TRUE;
			}

			return $this->carga->can_read();
		}

}