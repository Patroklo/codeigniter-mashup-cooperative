<?php

	echo '<h2>Permisos para '.$object_data->get_name().'</h2>';

	echo $this->Assign_permissions_model->map();

