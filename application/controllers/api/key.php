<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Keys Controller
 *
 * This is a basic Key Management REST controller to make and delete keys.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Makoto Ishijima (Based on Phil Sturgeon's Key class and Adam Whitney's + Phil's REST_Controller)
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php
require(APPPATH.'/libraries/REST_Controller.php');

class Key extends REST_Controller
{
	protected $methods = array(
		'post' => array('level' => 10, 'limit' => 10),
		'delete' => array('level' => 10),
		'put' => array('level' => 10),
	);

	/**
	 * Key Create
	 *
	 * Insert a key into the database.
	 *
	 * @access	public
	 * @return	void
	 */
	public function post()
    {
		// Build a new key
		$key = self::_generate_key();

		// If no key level provided, give them a rubbish one
		$level = $this->_put('level') ? $this->_put('level') : 1;
		$ignore_limits = $this->_put('ignore_limits') ? $this->_put('ignore_limits') : 1;

		// Insert the new key
		if (self::_insert_key($key, array('level' => $level, 'ignore_limits' => $ignore_limits)))
		{
			$this->response(array('status' => 1, 'key' => $key), 201); // 201 = Created
		}

		else
		{
			$this->response(array('status' => 0, 'error' => 'Could not save the key.'), 500); // 500 = Internal Server Error
		}
    }

	// --------------------------------------------------------------------

	/**
	 * Key Delete
	 *
	 * Remove a key from the database to stop it working.
	 *
	 * @access	public
	 * @return	void
	 */
	public function delete()
    {
		$key = $this->_delete('key');

		// Does this key even exist?
		if ( ! self::_key_exists($key))
		{
			// NOOOOOOOOO!
			$this->response(array('status' => 0, 'error' => 'Invalid API Key.'), 400);
		}

		// Kill it
		self::_delete_key($key);

		// Tell em we killed it
		$this->response(array('status' => 1, 'success' => 'API Key was deleted.'), 200);
    }

	// --------------------------------------------------------------------

	/**
	 * Update Key
	 *
	 * Change the level, suspend or regenerate
	 *
	 * @access	public
	 * @return	void
	 */
	public function put()
    {
		$key = $this->_put('key');

		$new_level = $this->_put('new_level');
        $regenerate = $this->_put('regenerate');
        $suspend = $this->_put('suspend');

        if($key && !empty($suspend) && $suspend == 1) {
            $this->_suspend_key($key);
        } elseif($key && !empty($regenerate) && $regenerate == 1) {
            $this->_regenerate_key($key);
        } elseif($key && (!empty($new_level) && $new_level >= 0 && $new_level <= 10)) {
            $this->_update_key_level($key, $new_level);
        } else {
            $this->response(array('status' => 0, 'error' => 'Missing parameters'), 400); // 500 = Internal Server Error
        }
    }

	// --------------------------------------------------------------------

	/**
	 * Suspend Key
	 *
	 * Change the level
	 *
	 * @access	private
     * @param string $key
	 * @return	void
	 */
	private function _suspend_key($key = '')
    {
		// Does this key even exist?
		if ( ! self::_key_exists($key))
		{
			// NOOOOOOOOO!
			$this->response(array('error' => 'Invalid API Key.'), 400);
		}

		// Update the key level
		if (self::_update_key($key, array('level' => 0)))
		{
			$this->response(array('status' => 1, 'success' => 'API Key was suspended.'), 200); // 200 = OK
		}

		else
		{
			$this->response(array('status' => 0, 'error' => 'Could not suspend the key.'), 500); // 500 = Internal Server Error
		}
    }

    // --------------------------------------------------------------------

    /**
     * Update Key Level
     *
     * @access	private
     * @param string $key
     * @param int $new_level
     * @return	void
     */
    private function _update_key_level($key,  $new_level)
    {
        // Does this key even exist?
        if ( ! self::_key_exists($key))
        {
            // NOOOOOOOOO!
            $this->response(array('error' => 'Invalid API Key.'), 400);
        }

        // Update the key level
        if (self::_update_key($key, array('level' => $new_level))) {
            $this->response(array('status' => 1, 'success' => 'API Key was updated.'), 200); // 200 = OK
        } else {
            $this->response(array('status' => 0, 'error' => 'Could not update the key level.'), 500); // 500 = Internal Server Error
        }
    }

	// --------------------------------------------------------------------

	/**
	 * Regenerate Key
	 *
	 * Remove a key from the database to stop it working.
	 *
	 * @access	private
	 * @return	void
	 */
	private function _regenerate_key()
    {

		$old_key = $this->_put('key');
		$key_details = self::_get_key($old_key);

		// The key wasnt found
		if ( ! $key_details)
		{
			// NOOOOOOOOO!
			$this->response(array('status' => 0, 'error' => 'Invalid API Key.'), 400);
		}

		// Build a new key
		$new_key = self::_generate_key();

		// Insert the new key
		if (self::_insert_key($new_key, array('level' => $key_details->level, 'ignore_limits' => $key_details->ignore_limits)))
		{
			// Suspend old key
			self::_update_key($old_key, array('level' => 0));

			$this->response(array('status' => 1, 'new key' => $new_key, 'success' => 'Key has been regenerated'), 201); // 201 = Created
		}

		else
		{
			$this->response(array('status' => 0, 'error' => 'Could not save the key.'), 500); // 500 = Internal Server Error
		}
    }

	// --------------------------------------------------------------------

	/* Helper Methods */
	
	private function _generate_key()
	{
		$this->load->helper('security');

		do
		{
			$salt = do_hash(time().mt_rand());
			$new_key = substr($salt, 0, config_item('rest_key_length'));
		}

		// Already in the DB? Fail. Try again
		while (self::_key_exists($new_key));

		return $new_key;
	}

	// --------------------------------------------------------------------

	/* Private Data Methods */

	private function _get_key($key)
	{
		return $this->db->where(config_item('rest_key_column'), $key)->get(config_item('rest_keys_table'))->row();
	}

	// --------------------------------------------------------------------

	private function _key_exists($key)
	{
		return $this->db->where(config_item('rest_key_column'), $key)->count_all_results(config_item('rest_keys_table')) > 0;
	}

	// --------------------------------------------------------------------

	private function _insert_key($key, $data)
	{
		
		$data['key'] = $key;
		$data['date_created'] = function_exists('now') ? now() : time();

		return $this->db->set($data)->insert(config_item('rest_keys_table'));
	}

	// --------------------------------------------------------------------

	private function _update_key($key, $data)
	{
		return $this->db->where(config_item('rest_key_column'), $key)->update(config_item('rest_keys_table'), $data);
	}

	// --------------------------------------------------------------------

	private function _delete_key($key)
	{
		return $this->db->where(config_item('rest_key_column'), $key)->delete(config_item('rest_keys_table'));
	}
}
