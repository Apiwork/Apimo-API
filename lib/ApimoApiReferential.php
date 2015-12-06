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
 * Referential parser.
 *
 * @author Nicolas Guillaud de Saint-Ferréol <support@apiwork.com>
 */

require_once('lib/ApimoApi.php');

class Apimo_Api_Referential extends Apimo_Api
{
	protected $api = 'referential';

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
		foreach($results as $type => $table)
		{
			// each values of each table
			foreach($table as $values)
			{
				// default values
				if(!array_key_exists('names', $values))
				{
					$values['names'] = null;
				}

				// retrieve catalog entry
				try {
					$new = true;
					$sql = 'SELECT COUNT(*) from `'.$this->dbTable[$this->api].'` WHERE type = ? AND culture = ? AND value = ? LIMIT 1';
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
					$sql = 'INSERT INTO `'.$this->dbTable[$this->api].'` SET type = :type, culture = :culture, value = :value, name = :name, names = :names';
				} 
				 else 
				{
					$result['object_updated']++;
					$sql = 'UPDATE `'.$this->dbTable[$this->api].'` SET name = :name, names = :names WHERE type = :type AND culture = :culture AND value = :value';
				}

				// prepare query
				$stmt = $db->prepare($sql);
				$stmt->bindParam(':type', $type, PDO::PARAM_STR);
				$stmt->bindParam(':culture', $values['culture'], PDO::PARAM_STR);
				$stmt->bindParam(':value', $values['id'], PDO::PARAM_INT);
				$stmt->bindParam(':name', $values['name'], PDO::PARAM_STR);
				$stmt->bindParam(':names', $values['names'], PDO::PARAM_STR);

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
