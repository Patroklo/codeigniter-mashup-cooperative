<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
			

		
		
	

			
			$query = $this->db->get_where('poblacion', array('idpoblacion' => '1'));
			
			
				echo '<pre>';
				  echo var_dump($query->result());
				echo '</pre>';
			
				$query = $this->db->select('poblacion, poblacionseo')
		                  ->where_in('idpoblacion', array(1,2))
		                  ->get('poblacion');
				echo '<pre>';
			  echo var_dump($query->result());
			echo '</pre>';	
			// echo '<pre>';
			  // echo var_dump(beep_from('poblacion')->values(array('postal' => 0001))->where_in('idpoblacion', array(1, 2))->update());
			// echo '</pre>';

		die();

		$start = microtime(TRUE);
		
		for($i = 0; $i < 1; $i++)
		{	
			$p = Poblacion_model::all();

			 $derp = $p->toArray();
		}
				
		$end = microtime(TRUE);
		
		
			echo '<pre>';
			  echo var_dump($end-$start);
			echo '</pre>';
		
		
		//$p = Poblacion_model::where('idpoblacion > ', 1)->where('idpoblacion <',5)->get();
		//$p = Poblacion_model::where(array('idpoblacion >' => 1, 'idpoblacion <' => 5))->pluck('poblacion');
		/*$p = Poblacion_model::where(array('poblacion' => 'derp'))->select('poblacion')->find(1,2);
		
			echo '<pre>';
			  echo var_dump($p->toArray());
			echo '</pre>';
		
		$p = Poblacion_model::find(array(1,2), array('poblacion'));
		
			echo '<pre>';
			  echo var_dump($p->toArray());
			echo '</pre>';
		$p = Poblacion_model::find(1,2,array('poblacion'));
		
			echo '<pre>';
			  echo var_dump($p->toArray());
			echo '</pre>';
		$p = Poblacion_model::select('poblacion')->find(1,2);
		
			echo '<pre>';
			  echo var_dump($p->toArray());
			echo '</pre>';
		echo '<pre>';
		  echo var_dump(count($p));
		echo '</pre>';
*/
		die();
		$this->load->view('welcome_message');
	}

	function derp()
	{
		
			echo '<pre>';
			  echo var_dump('variable');
			echo '</pre>';
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */