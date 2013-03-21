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
    	if(!$this->_get('id'))
    	{
    		//$users = $this->some_model->getSomething( $this->get('limit') );
    		$users = array(
    		array('id' => 1, 'name' => 'Some Guy', 'email' => 'example1@example.com'),
    		array('id' => 2, 'name' => 'Person Face', 'email' => 'example2@example.com'),
    		3 => array('id' => 3, 'name' => 'Scotty', 'email' => 'example3@example.com', 'fact' => array('hobbies' => array('fartings', 'bikes'))),
    		);

    		if($users)
    		{
    			$this->response($users, 200); // 200 being the HTTP response code
    		}

    		else
    		{
    			$this->response(array('error' => 'Couldn\'t find any users!'), 404);
    		}
    	}

        // $user = $this->some_model->getSomething( $this->_get('id') );
    	$users = array(
			1 => array('id' => 1, 'name' => 'Some Guy', 'email' => 'example1@example.com', 'fact' => 'Loves swimming'),
			2 => array('id' => 2, 'name' => 'Person Face', 'email' => 'example2@example.com', 'fact' => 'Has a huge face'),
			3 => array('id' => 3, 'name' => 'Scotty', 'email' => 'example3@example.com', 'fact' => 'Is a Scott!', array('hobbies' => array('fartings', 'bikes'))),
		);
		
    	$user = @$users[$this->_get('id')];
    	
        if($user)
        {
            $this->response($user, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'User could not be found'), 404);
        }
    }
    
    function post()
    {
    	if(!$this->_get('id'))
    	{
    		var_dump($this->request->body);
    	}
        //$this->some_model->updateUser( $this->get('id') );
        $message = array('id' => $this->_get('id'), 'name' => $this->_post('name'), 'email' => $this->_post('email'), 'message' => 'ADDED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
    
    function delete()
    {
    	//$this->some_model->deletesomething( $this->get('id') );
        $message = array('id' => $this->_get('id'), 'message' => 'DELETED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }

	public function put()
	{
		var_dump($this->_put('foo'));
	}
}