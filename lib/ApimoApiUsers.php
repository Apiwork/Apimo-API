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
 * Users parser.
 *
 * @author Nicolas Guillaud de Saint-Ferréol <support@apiwork.com>
 */

require_once('lib/ApimoApi.php');

class Apimo_Api_Users extends Apimo_Api
{
	protected $api = 'users';

	public function update()
	{
		// request for database
		$db = $this->getDatabase();

		// init vars
		$result = array(
			'api' => $this->api,
			'table' => $this->dbTable[$this->api],
			'create_table' => false,
			'agencies' => array()
		);

        // need to create table ?
		$result['create_table'] = $this->createSql($this->api);

		// retrieve user list
		try {
			$new = true;
			$sql = 'SELECT id, external_id, name from `'.$this->dbTable['agencies'].'`';
			$stmt = $db->prepare($sql);
			$stmt->execute();
		    $rows = $stmt->fetchAll();
		} catch(PDOException $ex) {
		    echo "SQL Error: ".$ex->getMessage();
		}

		foreach($rows as $agency)
		{
			// vars
			$date = date('Y-m-d H:i:s');

			// retrieve results
			$results = $this->callApi(array('agency' => $agency['external_id']));

			// init vars
			$result['agencies'][$agency['external_id']] = array(
				'name' => $agency['name'],
				'object_created' => 0,
				'object_updated' => 0
			);

			// each table
			foreach($results as $user)
			{
				// vars
				$date = date('Y-m-d H:i:s');

				// retrieve catalog entry
				try {
					$new = true;
					$sql = 'SELECT COUNT(*) from `'.$this->dbTable[$this->api].'` WHERE external_id = ? LIMIT 1';
					$stmt = $db->prepare($sql);
					$stmt->bindParam(1, $user['id'], PDO::PARAM_INT);
					$stmt->execute();
					if($stmt->fetchColumn())
					{
						$new = false;
					}
				} catch(PDOException $ex) {
					echo "SQL Error: ".$ex->getMessage();
				}

				// insert or update entry
				$commonSql = 'agency_id = :agency_id, active = :active, lastname = :lastname, firstname = :firstname, phone = :phone, email = :email, updated_at = :updated_at';
	  			if($new && $user['active'])
				{
					$result['agencies'][$agency['external_id']]['object_created']++;
					$sql = 'INSERT INTO `'.$this->dbTable[$this->api].'` SET external_id = :external_id, '.$commonSql.', created_at = :created_at';
				} 
				 else 
				{
					$result['agencies'][$agency['external_id']]['object_updated']++;
					$sql = 'UPDATE `'.$this->dbTable[$this->api].'` SET '.$commonSql.' WHERE external_id = :external_id';
				}

				// prepare query
				$stmt = $db->prepare($sql);
				$stmt->bindParam(':external_id', $user['id'], PDO::PARAM_INT);
				$stmt->bindParam(':agency_id', $agency['id'], PDO::PARAM_BOOL);
				$stmt->bindParam(':active', $user['active'], PDO::PARAM_BOOL);
				$stmt->bindParam(':lastname', $user['lastname'], PDO::PARAM_STR);
				$stmt->bindParam(':firstname', $user['firstname'], PDO::PARAM_STR);
				$stmt->bindParam(':phone', $user['phone'], PDO::PARAM_STR);
				$stmt->bindParam(':email', $user['email'], PDO::PARAM_STR);
				$stmt->bindParam(':updated_at', $date, PDO::PARAM_STR);
	  			if($new && $user['active'])
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
