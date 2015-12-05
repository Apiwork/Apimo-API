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

require_once('lib/ApimoApi.php');

class Apimo_Api_Agencies extends Apimo_Api
{
	protected $api = 'agencies';

	public function callApi()
	{
		// build URL
		$url = $this->buildUrl('get'.ucfirst($this->api));

		// call API
		return $this->call($url);
	}

	public function create()
	{
		// request for database
		$db = $this->getDatabase();

		// look if table exists
		$sql = 'SHOW TABLES LIKE ?';
		$stmt = $db->prepare($sql);
		$stmt->bindParam(1, $this->dbTable['agencies'], PDO::PARAM_STR);
		$stmt->execute();
		if(!$stmt->fetchColumn())
		{
			// create table
			$db->exec('CREATE TABLE `'.$this->dbTable['agencies'].'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
		 	  `apimo_id` integer(11) COLLATE utf8_unicode_ci NOT NULL,
		 	  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
		 	  `created_at` datetime COLLATE utf8_unicode_ci NOT NULL,
		 	  `updated_at` datetime COLLATE utf8_unicode_ci NOT NULL,
		 	 PRIMARY KEY (`id`),
		 	 KEY `key_1` (`apimo_id`)
			) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;');
			return true;
		}
		return false;
	}

	public function update()
	{
		// retrieve results
		$results = $this->callApi();

		// request for database
		$db = $this->getDatabase();

		// init vars
		$date = date('Y-m-d H:i:s');
		$result = array(
			'api' => $this->api,
			'table' => $this->dbTable['agencies'],
			'create_table' => false,
			'object_updated' => 0,
			'object_created' => 0
		);

        // need to create table ?
		$result['create_table'] = $this->create();

		// each table
		foreach($results as $agency)
		{
			// retrieve catalog entry
			try {
				$new = true;
				$sql = 'SELECT COUNT(*) from `'.$this->dbTable['agencies'].'` WHERE apimo_id = ? LIMIT 1';
				$stmt = $db->prepare($sql);
				$stmt->bindParam(1, $agency['id'], PDO::PARAM_INT);
				$stmt->execute();
				if($stmt->fetchColumn())
				{
					$new = false;
				}
			} catch(PDOException $ex) {
			    echo "SQL Error: ".$ex->getMessage();
			}

			// insert or update entry
	  		if($new)
			{
				$result['object_created']++;
				$sql = 'INSERT INTO `'.$this->dbTable['agencies'].'` SET apimo_id = :apimo_id, name = :name, created_at = :created_at, updated_at = :updated_at';
			} 
			 else 
			{
				$result['object_updated']++;
				$sql = 'UPDATE `'.$this->dbTable['agencies'].'` SET name = :name, updated_at = :updated_at WHERE apimo_id = :apimo_id';
			}

			// prepare query
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':apimo_id', $agency['id'], PDO::PARAM_INT);
			$stmt->bindParam(':name', $agency['name'], PDO::PARAM_STR);
			$stmt->bindParam(':updated_at', $date, PDO::PARAM_STR);
	  		if($new)
			{
				$stmt->bindParam(':created_at', $date, PDO::PARAM_STR);
			}

			// execute query
			try {
				$stmt->execute();
			} catch(PDOException $ex) {
			    echo "SQL Error: ".$ex->getMessage();
			}
	 	}

        // return report
		return $result;
	}
}
