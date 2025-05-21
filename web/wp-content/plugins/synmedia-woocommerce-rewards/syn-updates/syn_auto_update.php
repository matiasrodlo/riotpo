<?php
	
class SYN_Auto_Update
{
    /**
     * The plugin current version
     * @var string
     */
    public $current_version;
    /**
     * The plugin remote update path
     * @var string
     */
    public $update_path = 'http://www.synmedia.ca/api';
    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     * @var string
     */
    public $plugin_slug;
    /**
     * Plugin name (plugin_file)
     * @var string
     */
    public $slug;
    /**
     * Envato item id
     * @var string
     */
    public $item_id;
    /**
     * Envato item licence
     * @var string
     */
    public $licence_id;
    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct($current_version, $plugin_slug, $item_id, $licence_id)
    {
    	/* set_site_transient('update_plugins', null); */
    	// Set the class public variables  
    	$this->current_version = $current_version;
	    $this->plugin_slug = $plugin_slug;
	    $this->item_id = $item_id;
	    $this->licence_id = $licence_id;
	    // define the alternative API for updating checking
	    add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));
	    // Define the alternative response for information checking
	    add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
    }
    
    /**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 * @return object $ transient
	 */
	function check_update($checked_data) {
		//Comment out these two lines during testing.
		$wp_version = floatval(get_bloginfo('version'));
		if (empty($checked_data->checked))
			return $checked_data;

		$args = array(
			'slug' =>  $this->plugin_slug,
			'version' => $checked_data->checked[$this->plugin_slug],
		);
		$request_string = array(
				'body' => array(
					'action' => 'basic_check', 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url')),
					'licence_id' => $this->licence_id,
					'item_id' => $this->item_id
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);
		
		// Start checking for an update
		$raw_response = wp_remote_post($this->update_path, $request_string);
		if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
			$response = unserialize($raw_response['body']);
		
		if (is_object($response) && !empty($response)) // Feed the update data into WP updater
			$checked_data->response[$this->plugin_slug] = $response;
		
		return $checked_data;
	}
	/**
	 * Add our self-hosted description to the filter
	 *
	 * @param boolean $false
	 * @param array $action
	 * @param object $arg
	 * @return bool|object
	 */
	public function check_info($def, $action, $args)
	{
		if ($args->slug != $this->plugin_slug)
			return false;
		
		// Get the current version
		$plugin_info = get_site_transient('update_plugins');
		$args->version = $this->current_version;
		$wp_version = get_bloginfo('version');
		
		$request_string = array(
				'body' => array(
					'action' => $action, 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url')),
					'licence_id' => $this->licence_id,
					'item_id' => $this->item_id
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);
		
		$request = wp_remote_post($this->update_path, $request_string);
		
		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
		} else {
			$res = unserialize($request['body']);
			
			if ($res === false)
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
		}
		
		return $res;
	}

}


?>