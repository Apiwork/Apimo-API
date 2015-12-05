<?php

/*
 * This file is part of Apimo Api.
 *
 * (c) 2015 Apiwork
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Default parser implementation.
 *
 * @author Nicolas Guillaud de Saint-Ferréol <support@apiwork.com>
 */
abstract class Apimo_Api
{
	protected $dbTable = array(
		'agencies' => 'apimo_agencies',
		'properties' => 'apimo_agencies',
		'referential' => 'apimo_referential'
	);

    abstract function callApi();
    abstract function create();
    abstract function update();

	public function __construct()
	{
		$config = parse_ini_file(dirname(__FILE__).'/../config/config.ini');

		$this->url = $config['url'];
		$this->provider = $config['provider'];
		$this->key = $config['key'];
		$this->version = $config['version'];
		$this->adapter = $config['adapter'];

		$this->database = $config['database'];
		$this->database_user = $config['database_user'];
		$this->database_password = $config['database_password'];

		try {
			$this->db = new PDO($this->database, $this->database_user, $this->database_password);
		} catch(PDOException $ex) {
		    echo "DB Error ".$ex->getMessage();
			exit();
		}
	}

	function getDatabase()
	{
		return $this->db;
	}

	function buildUrl($method, $type = 'json', $params = array())
	{
		// build security key
		$timestamp = time();
		$sha1 = sha1($this->key.$timestamp);
		
		// params
		$paramsString = '';
		if($params)
		{
			foreach($params as $param => $value)
			{
				$paramsString = '&'.$param.'='.$value;
			}
		}

		// build and return URL
		return $this->url.'?provider='.$this->provider.
			'&timestamp='.$timestamp.
			'&sha1='.$sha1.
			'&method='.$method.
			'&type='.$type.
			'&version='.$this->version.
			$paramsString;
	}

    function call($url)
    {
		if($this->adapter == 'curl')
		{
			// curl init
			$ch = curl_init($url);

			// curl settings
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Realtix browser');

        	// curl request
			$content = curl_exec($ch);

        	// close curl
			curl_close($ch);
		
		} 
		  else 
		{
			// php method
			$content = file_get_contents($url);
		}

		// return json response
		return json_decode($content, true);
    }
}
