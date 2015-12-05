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

class Apimo_Api_Properties extends Apimo_Api
{
	protected $api = 'properties';

	public function callApi($params = array())
	{
		// build URL
		$url = $this->buildUrl('get'.ucfirst($this->api), 'json', $params);

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
		$stmt->bindParam(1, $this->dbTable['properties'], PDO::PARAM_STR);
		$stmt->execute();
		if(!$stmt->fetchColumn())
		{
			// create table
			$db->exec('CREATE TABLE `'.$this->dbTable['properties'].'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
		 	  `agency_id` integer(11) COLLATE utf8_unicode_ci NOT NULL,
		 	  `apimo_id` integer(11) COLLATE utf8_unicode_ci NOT NULL,
		 	  `category` integer(11) COLLATE utf8_unicode_ci NOT NULL,
		 	  `price` decimal(12, 2) COLLATE utf8_unicode_ci NOT NULL,
		 	  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
		 	  `created_at` datetime COLLATE utf8_unicode_ci NOT NULL,
		 	  `updated_at` datetime COLLATE utf8_unicode_ci NOT NULL,
		 	 PRIMARY KEY (`id`),
		 	 KEY `key_1` (`agency_id`),
		 	 KEY `key_2` (`apimo_id`),
		 	 KEY `key_3` (`category`)
			) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;');
			return true;
		}
		return false;
	}

	public function update()
	{
		// request for database
		$db = $this->getDatabase();

		// init vars
		$date = date('Y-m-d H:i:s');
		$result = array(
			'api' => $this->api,
			'table' => $this->dbTable['properties'],
			'create_table' => false,
			'agencies' => array()
		);

		// need to create table ?
		$result['create_table'] = $this->create();

		// retrieve agency list
		try {
			$new = true;
			$sql = 'SELECT id, apimo_id, name from `'.$this->dbTable['agencies'].'`';
			$stmt = $db->prepare($sql);
			$stmt->execute();
		    $rows = $stmt->fetchAll();
		} catch(PDOException $ex) {
		    echo "SQL Error: ".$ex->getMessage();
		}

		foreach($rows as $agency)
		{
			// retrieve results
			$results = $this->callApi(array('agency' => $agency['apimo_id']));

			// init vars
			$result['agencies'][$agency['apimo_id']] = array(
				'name' => $agency['name'],
				'object_created' => 0,
				'object_updated' => 0
			);

			// each table
			foreach($results as $property)
			{
				// check for property entry
				try {
					$new = true;
					$sql = 'SELECT COUNT(*) from `'.$this->dbTable['properties'].'` WHERE apimo_id = ? LIMIT 1';
					$stmt = $db->prepare($sql);
					$stmt->bindParam(1, $property['id'], PDO::PARAM_INT);
					$stmt->execute();
					if($stmt->fetchColumn())
					{
						$new = false;
					}
				} catch(PDOException $ex) {
					echo "SQL Error: ".$ex->getMessage();
				}

				// insert or update entry
				$commonSql = 'agency_id = :agency_id, 
					apimo_id = :apimo_id,
					category = :category,
					price = :price,
					currency = :currency,
					updated_at = :updated_at';
	  			if($new)
				{
					$result['agencies'][$agency['apimo_id']]['object_created']++;
					$sql = 'INSERT INTO `'.$this->dbTable['properties'].'` SET '.$commonSql.', created_at = :created_at';
				} 
				 else 
				{
					 $result['agencies'][$agency['apimo_id']]['object_updated']++;
					 $sql = 'UPDATE `'.$this->dbTable['properties'].'` SET '.$commonSql.' WHERE apimo_id = :apimo_id';
				}

				// prepare query
				$stmt = $db->prepare($sql);
				$stmt->bindParam(':agency_id', $agency['id'], PDO::PARAM_STR);
				$stmt->bindParam(':apimo_id', $property['id'], PDO::PARAM_STR);
				$stmt->bindParam(':category', $property['category'], PDO::PARAM_INT);
				$stmt->bindParam(':price', $property['price']['value'], PDO::PARAM_INT);
				$stmt->bindParam(':currency', $property['price']['currency'], PDO::PARAM_INT);
				$stmt->bindParam(':updated_at', $date, PDO::PARAM_INT);
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
		}

        // return report
		return $result;
	}
}
