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

	public function update()
	{
		// retrieve results
		$results = $this->callApi();

		// request for database
		$db = $this->getDatabase();

		// init vars
		$result = array(
			'api' => $this->api,
			'table' => $this->dbTable[$this->api],
			'create_table' => false,
			'object_updated' => 0,
			'object_created' => 0
		);

        // need to create table ?
		$result['create_table'] = $this->createSql($this->api);

		// each table
		foreach($results as $agency)
		{
			$date = date('Y-m-d H:i:s');

			// retrieve catalog entry
			try {
				$new = true;
				$sql = 'SELECT COUNT(*) from `'.$this->dbTable[$this->api].'` WHERE external_id = ? LIMIT 1';
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
			$commonSql = 'name = :name, active = 1, city = :city, country = :country, updated_at = :updated_at';
	  		if($new)
			{
				$result['object_created']++;
				$sql = 'INSERT INTO `'.$this->dbTable[$this->api].'` SET external_id = :external_id, '.$commonSql.', created_at = :created_at';
			} 
			 else 
			{
				$result['object_updated']++;
				$sql = 'UPDATE `'.$this->dbTable[$this->api].'` SET '.$commonSql.' WHERE external_id = :external_id';
			}

			// prepare query
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':external_id', $agency['id'], PDO::PARAM_INT);
			$stmt->bindParam(':name', $agency['name'], PDO::PARAM_STR);
			$stmt->bindParam(':city', $agency['city'], PDO::PARAM_STR);
			$stmt->bindParam(':country', $agency['country'], PDO::PARAM_STR);
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
