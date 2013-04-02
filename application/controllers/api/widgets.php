<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Adam Whitney
 * @link		http://outergalactic.org/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Widgets extends REST_Controller
{
	
	function get()
    {
    	// Example data for testing.
    	$widgets = array(
    			1 => array('id' => 1, 'name' => 'sprocket'),
    			2 => array('id' => 2, 'name' => 'gear')
    	);
    	 
    	$id = $this->_get('id');
    	if(!$id)
    	{
    		//$widgets = $this->widgets_model->getWidgets();    		    		
    		if($widgets)
    			$this->response($widgets, 200); // 200 being the HTTP response code
    		else
    			$this->response(array('error' => 'Couldn\'t find any widgets!'), 404);
    	}

        //$widget = $this->widgets_model->getWidget($id);
    	$widget = @$widgets[$id]; // test code

        if($widget)
            $this->response($widget, 200); // 200 being the HTTP response code
        else
            $this->response(array('error' => 'Widget could not be found'), 404);
    }
    
    function post()
    {
		$data = $this->_post_args;
		try {
			//$id = $this->widgets_model->createWidget($data);
			$id = 3; // test code
			//throw new Exception('Invalid request data', 400); // test code
			//throw new Exception('Widget already exists', 409); // test code
		} catch (Exception $e) {
			// Here the model can throw exceptions like the following:
			// * For invalid input data: new Exception('Invalid request data', 400)
			// * For a conflict when attempting to create, like a resubmit: new Exception('Widget already exists', 409)
			$this->response(array('error' => $e->getMessage()), $e->getCode());
		}
		if ($id) {
			$widget = array('id' => $id, 'name' => $data['name']); // test code
			//$widget = $this->widgets_model->getWidget($id);
			$this->response($widget, 201); // 201 being the HTTP response code
		} else
			$this->response(array('error' => 'Widget could not be created'), 404);
    }
    
    public function put()
    {
		$data = $this->_put_args;
		try {
			//$id = $this->widgets_model->updateWidget($data);
			$id = $data['id']; // test code
			//throw new Exception('Invalid request data', 400); // test code
		} catch (Exception $e) {
			// Here the model can throw exceptions like the following:
			// * For invalid input data: new Exception('Invalid request data', 400)
			// * For a conflict when attempting to create, like a resubmit: new Exception('Widget already exists', 409)
			$this->response(array('error' => $e->getMessage()), $e->getCode());
		}
		if ($id) {
			$widget = array('id' => $data['id'], 'name' => $data['name']); // test code
			//$widget = $this->widgets_model->getWidget($id);
			$this->response($widget, 200); // 200 being the HTTP response code
		} else
			$this->response(array('error' => 'Widget could not be found'), 404);
    }
        
    function delete()
    {
    	
    	// Example data for testing.
    	$widgets = array(
    			1 => array('id' => 1, 'name' => 'sprocket'),
    			2 => array('id' => 2, 'name' => 'gear'),
    			3 => array('id' => 3, 'name' => 'nut')
    	);
    	
    	$id = $this->_get('id');
    	if(!$id)
    	{
    		$this->response(array('error' => 'An ID must be supplied to delete a widget'), 400);
    	}

        //$widget = $this->widgets_model->getWidget($id);
    	$widget = @$widgets[$id]; // test code

    	if($widget) {
    		try {
    			//$this->widgets_model->deleteWidget($id);
    			//throw new Exception('Forbidden', 403); // test code
    		} catch (Exception $e) {
    			// Here the model can throw exceptions like the following:
    			// * Client is not authorized: new Exception('Forbidden', 403)
    			$this->response(array('error' => $e->getMessage()), $e->getCode());
    		}
    		$this->response($widget, 200); // 200 being the HTTP response code
    	} else
    		$this->response(array('error' => 'Widget could not be found'), 404);
    }
}