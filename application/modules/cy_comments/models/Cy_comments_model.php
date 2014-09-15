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

	public $comment_form_view;

	public $order_type          = 'ASC';

	public $object 				= 'message_object';

	public $carga 				= FALSE;

	public $message_type;

	public $config_type         = 'normal';

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


    /**
     * Inserts new message
     *
     * @return inserted id
     * @author Joseba J
     */

    function insert($data)
    {
        if ( ! $this->msg_insert_permission($data['reference_id']))
	    {
		    return FALSE;
	    }

	    $data['message_type']   = $this->message_type;
	    $data['ip']				= $this->input->ip_address();
	    $data['creation_date']	= date("Y-m-d H:i:s");
	    $data['user_id']		= ($this->auth->logged_in() ? $this->auth->get_user_id() : 0);

	    $new_id = beep_from($this->tableName)->values($data)->insert();


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
	}


    /**
     * loads a stream of comments
     *
     * @return array of comments
     * @author  Joseba J
     */
    function load_comments($reference_id, $limit = FALSE, $offset = 0)
    {

        if ( ! $this->msg_read_permission($this->message_type))
        {
            return FALSE;
        }

        $data_original 					= array();
        $data_original['reference_id']	= $reference_id;


        $query		  = $this->get_query()->where($data_original)->
											offset($offset);

        if($limit != FALSE)
        {
            $query	  = $query->limit($limit);
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


	public function show_comments($reference_id, $limit = FALSE, $offset = 0)
	{

		if ( ! $this->msg_read_permission($reference_id))
		{
			return FALSE;
		}

		$view_data = new stdClass();

		$view_data->comment_list = $this->load_comments($reference_id, $limit, $offset);


		$return_html = $this->load->view($this->list_view, $view_data, TRUE);

		if ($this->msg_insert_permission($reference_id))
		{
			$this->load->model('cy_comments/form_models/Cy_comments_form');
			$this->load->helper('form');
			
			// add the jquery call for commenting
			$this->js_load->add('comments.form()');
			
			$form_data = array ('reference_id' 	=> $reference_id,
								'comment_type'	=> $this->message_type);
			
			$return_html.= $this->load->view($this->comment_form_view, $form_data, TRUE);
		}

		return $return_html;
	}

    /**
     * ========================================================================================================
     * ========================================================================================================
     *              SECURITY METHODS
     * ========================================================================================================
     * ========================================================================================================
     */
		function msg_insert_permission($reference_id)
		{
			return TRUE;
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

			if ($this->auth->is_admin())
			{
				return TRUE;
			}

			if ($this->carga->get_data('user_id') == $this->auth->get_user_id())
			{
				return TRUE;
			}

			if ($this->global_delete_permission())
			{
				return TRUE;
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

		function global_delete_permission()
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





    /**
     * ========================================================================================================
     * ========================================================================================================
     *              ADMIN AND MOD METHODS
     * ========================================================================================================
     * ========================================================================================================
     */

    /**
     * moves the post to another subforum.
     *
     * @return boolean
     * @author Joseba J
     */
    function admin_move_post($new_subforum)
    {
        if ($this->carga === FALSE)
        {
            return FALSE;
        }

        // checks with the origin forum
        if ($this->check_basic_permissions() === FALSE)
        {
            return FALSE;
        }

        if ($this->check_user_is_mod() === FALSE)
        {
            return FALSE;
        }

        $this->carga_forum($new_subforum);

        // same checks but with the destiny forum
        if ($this->check_basic_permissions() === FALSE)
        {
            return FALSE;
        }

        if ($this->check_user_is_mod() === FALSE)
        {
            return FALSE;
        }

        // this change counts as a insert in the new forum---
        if ($this->global_insert_permission() == FALSE)
        {
            return FALSE;
        }

        $data = array();
        $data['reference_id'] = $new_subforum;

        return parent::update($data);

    }

    /**
     * undocumented function
     *
     * @return void
     * @author
     */
    function admin_close_post()
    {
        if ($this->carga === FALSE)
        {
            return FALSE;
        }

        if ($this->check_user_is_mod() === FALSE)
        {
            return FALSE;
        }

        if ($this->check_basic_permissions() === FALSE)
        {
            return FALSE;
        }

        $data = array();
        $data['closed'] = 1;

        return parent::update($data);
    }

}