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

class Apimo_Api_Referential extends Apimo_Api
{
	protected $api = 'referential';

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
		$stmt->bindParam(1, $this->dbTable['referential'], PDO::PARAM_STR);
		$stmt->execute();
		if(!$stmt->fetchColumn())
		{
			// create table
			$db->exec('CREATE TABLE `'.$this->dbTable['referential'].'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
		 	  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  `value` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `culture` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
			  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		 	  `names` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		 	 PRIMARY KEY (`id`),
		 	 KEY `key_1` (`type`,`value`,`culture`)
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
		$result = array(
			'api' => $this->api,
			'table' => $this->dbTable['referential'],
			'create_table' => false,
			'object_updated' => 0,
			'object_created' => 0
		);

        // need to create table ?
		$result['create_table'] = $this->create();

		// each table
		foreach($results as $type => $table)
		{
			// each values of each table
			foreach($table as $values)
			{
				// default values
				if(array_key_exists('names', $values))
				{
					$values['names'] = null;
				}

				// retrieve catalog entry
				try {
					$new = true;
					$sql = 'SELECT COUNT(*) from `'.$this->dbTable['referential'].'` WHERE type = ? AND culture = ? AND value = ? LIMIT 1';
					$stmt = $db->prepare($sql);
					$stmt->bindParam(1, $type, PDO::PARAM_STR);
					$stmt->bindParam(2, $values['culture'], PDO::PARAM_STR);
					$stmt->bindParam(3, $values['id'], PDO::PARAM_INT);
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
					$sql = 'INSERT INTO `'.$this->dbTable['referential'].'` SET type = ?, culture = ?, value = ?, name = ?, names = ?';
				} 
				 else 
				{
					$result['object_updated']++;
					$sql = 'UPDATE `'.$this->dbTable['referential'].'` SET type = ?, culture = ?, value = ?, name = ?, names = ? WHERE type = ? AND culture = ? AND value = ?';
				}

				// prepare query
				$stmt = $db->prepare($sql);
				$stmt->bindParam(1, $type, PDO::PARAM_STR);
				$stmt->bindParam(2, $values['culture'], PDO::PARAM_STR);
				$stmt->bindParam(3, $values['id'], PDO::PARAM_INT);
				$stmt->bindParam(4, $values['name'], PDO::PARAM_STR);
				$stmt->bindParam(5, $values['names'], PDO::PARAM_STR);
	  			if(!$new)
				{
					$stmt->bindParam(6, $type, PDO::PARAM_STR);
					$stmt->bindParam(7, $values['culture'], PDO::PARAM_STR);
					$stmt->bindParam(8, $values['id'], PDO::PARAM_INT);
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
