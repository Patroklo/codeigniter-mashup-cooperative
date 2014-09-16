mini tutorial para usar disqus:

	1 - poner nombre de la cuenta en config['disqus.shortname'];
	
	2 - si queremos ponerle un id específico a la página de comentarios
		hay que cargarle un objeto
		
			$this->disqus->carga($objeto);
			
		usará el nombre de la clase y su get_data('id') para hacer un identificador
		único.
		
		// posible mejora : poder enviarle a mano más datos para que los ponga como identificador
		
		
	3 - para mostrarlo:
		
			echo $this->disqus->show_comments();
			
	4 - llamadas a la api de disqus
	
			4.1 - hay que definir una clave privada en config (parece que la pública no es necesaria)
	
			4.2 - hacer la llamada
				
				$this->CI->disqusapi->trends(o lo que sea)->listThreads(array('limit'=>'10'))
			
				lista de funciones: https://disqus.com/api/docs/

