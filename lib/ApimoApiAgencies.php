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
 * Agencies parser.
 *
 * @author Nicolas Guillaud de Saint-Ferréol <support@apiwork.com>
 */

require_once('lib/ApimoApi.php');

class Apimo_Api_Agencies extends Apimo_Api
{
	protected $api = 'agencies';

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
		$activeIds = array();

        // need to create table ?
		$result['create_table'] = $this->createSql($this->api);

		// each table
		foreach($results as $agency)
		{
			// vars
			$date = date('Y-m-d H:i:s');

			// active agencies
			$activeIds[] = $agency['id'];

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
			$commonSql = 'company = :company, name = :name, active = :active, address = :address, address_more = :address_more, zipcode = :zipcode, city = :city, country = :country, phone = :phone, fax = :fax, email = :email, url = :url, updated_at = :updated_at';
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
			$stmt->bindParam(':active', $agency['active'], PDO::PARAM_BOOL);
			$stmt->bindParam(':company', $agency['company'], PDO::PARAM_STR);
			$stmt->bindParam(':name', $agency['name'], PDO::PARAM_STR);
			$stmt->bindParam(':address', $agency['address'], PDO::PARAM_STR);
			$stmt->bindParam(':address_more', $agency['address_more'], PDO::PARAM_STR);
			$stmt->bindParam(':zipcode', $agency['zipcode'], PDO::PARAM_STR);
			$stmt->bindParam(':city', $agency['city'], PDO::PARAM_STR);
			$stmt->bindParam(':country', $agency['country'], PDO::PARAM_STR);
			$stmt->bindParam(':phone', $agency['phone'], PDO::PARAM_STR);
			$stmt->bindParam(':fax', $agency['fax'], PDO::PARAM_STR);
			$stmt->bindParam(':email', $agency['email'], PDO::PARAM_STR);
			$stmt->bindParam(':url', $agency['url'], PDO::PARAM_STR);
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
		
		// unactive agencies
		$sql = 'UPDATE `'.$this->dbTable[$this->api].'` SET active = 0 WHERE external_id NOT IN ('.implode(',', $activeIds).')';
		// execute query
		try {
			$db->exec($sql);
		} catch(PDOException $ex) {
		    echo "SQL Error: ".$ex->getMessage();
		}

        // return report
		return $result;
	}
}
