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
 * Properties parser.
 *
 * @author Nicolas Guillaud de Saint-Ferréol <support@apiwork.com>
 */

require_once('lib/ApimoApi.php');

class Apimo_Api_Properties extends Apimo_Api
{
	protected $api = 'properties';

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
		$result['create_table'] = $this->createSql('medias');

		// retrieve agency list
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
			foreach($results as $property)
			{
				// check for property entry
				try {
					$new = true;
					$sql = 'SELECT COUNT(*) from `'.$this->dbTable[$this->api].'` WHERE external_id = ? LIMIT 1';
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
					external_id = :external_id,
					reference = :reference,
					user_id = :user_id,
					category = :category,
					type = :type,
					subtype = :subtype,
					city = :city,
					city_id = :city_id,
					district = :district,
					district_id = :district_id,
					longitude = :longitude,
					latitude = :latitude,
					price = :price,
					price_fees = :price_fees,
					price_hide = :price_hide,
					commission = :price_commission,
					guarantee = :price_guarantee,
					currency = :currency,
					area = :area,
					rooms = :rooms,
					bedrooms = :bedrooms,
					sleeps = :sleeps,
					view_type = :view_type,
					view_landscape = :view_landscape,
					orientations = :orientations,
					`condition` = :condition,
					standing = :standing,
					activities = :activities,
					services = :services,
					floor = :floor,
					heating_device = :heating_device,
					heating_access = :heating_access,
					heating_type = :heating_type,
					hot_water_device = :hot_water_device,
					hot_water_access = :hot_water_access,
					waste_water = :waste_water,
					proximities = :proximities,
					tags = :tags,
					updated_at = :updated_at';
	  			if($new)
				{
					$result['agencies'][$agency['external_id']]['object_created']++;
					$sql = 'INSERT INTO `'.$this->dbTable[$this->api].'` SET '.$commonSql.', created_at = :created_at';
				} 
				 else 
				{
					 $result['agencies'][$agency['external_id']]['object_updated']++;
					 $sql = 'UPDATE `'.$this->dbTable[$this->api].'` SET '.$commonSql.' WHERE external_id = :external_id';
				}

				// retrieve user_id
				try {
					$new = true;
					$sqlUser = 'SELECT id from `'.$this->dbTable['users'].'` WHERE external_id = :external_id';
					$stmt2 = $db->prepare($sqlUser);
					$stmt2->bindParam(':external_id', $property['user']['id'], PDO::PARAM_INT);
					$stmt2->execute();
				    $userId = $stmt2->fetchColumn();
					if(!$userId)
					{
						$userId = null;
					}
				} catch(PDOException $ex) {
				    echo "SQL Error: ".$ex->getMessage();
				}

				// prepare query
				$stmt = $db->prepare($sql);
				$stmt->bindParam(':agency_id', $agency['id'], PDO::PARAM_STR);
				$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
				$stmt->bindParam(':external_id', $property['id'], PDO::PARAM_STR);
				$stmt->bindParam(':reference', $property['reference'], PDO::PARAM_STR);
				$stmt->bindParam(':category', $property['category'], PDO::PARAM_INT);
				$stmt->bindParam(':type', $property['type'], PDO::PARAM_INT);
				$stmt->bindParam(':subtype', $property['type_specific'], PDO::PARAM_INT);
				$stmt->bindParam(':city', $property['city']['name'], PDO::PARAM_INT);
				$stmt->bindParam(':city_id', $property['city']['id'], PDO::PARAM_INT);
				$stmt->bindParam(':district', $property['district']['name'], PDO::PARAM_INT);
				$stmt->bindParam(':district_id', $property['district']['id'], PDO::PARAM_INT);
				$stmt->bindParam(':longitude', $property['longitude'], PDO::PARAM_STR);
				$stmt->bindParam(':latitude', $property['latitude'], PDO::PARAM_STR);
				$stmt->bindParam(':price', $property['price']['value'], PDO::PARAM_STR);
				$stmt->bindParam(':price_fees', $property['price']['fees'], PDO::PARAM_STR);
				$stmt->bindParam(':price_hide', $property['price']['hide'], PDO::PARAM_BOOL);
				$stmt->bindParam(':price_commission', $property['price']['commission'], PDO::PARAM_STR);
				$stmt->bindParam(':price_guarantee', $property['price']['guarantee'], PDO::PARAM_STR);
				$stmt->bindParam(':currency', $property['price']['currency'], PDO::PARAM_INT);
				$stmt->bindParam(':area', $property['area']['value'], PDO::PARAM_STR);
				$stmt->bindParam(':rooms', $property['rooms'], PDO::PARAM_STR);
				$stmt->bindParam(':bedrooms', $property['bedrooms'], PDO::PARAM_INT);
				$stmt->bindParam(':sleeps', $property['sleeps'], PDO::PARAM_INT);
				$stmt->bindParam(':heating_device', $property['heating']['device'], PDO::PARAM_INT);
				$stmt->bindParam(':heating_access', $property['heating']['access'], PDO::PARAM_INT);
				$stmt->bindParam(':heating_type', $property['heating']['type'], PDO::PARAM_INT);
				$stmt->bindParam(':hot_water_device', $property['water']['hot_device'], PDO::PARAM_INT);
				$stmt->bindParam(':hot_water_access', $property['water']['hot_access'], PDO::PARAM_INT);
				$stmt->bindParam(':waste_water', $property['water']['waste'], PDO::PARAM_INT);
				$stmt->bindParam(':view_type', $property['view']['type'], PDO::PARAM_INT);
				$stmt->bindParam(':view_landscape', $property['view']['landscape'], PDO::PARAM_STR);
				$stmt->bindParam(':orientations', $property['orientations'], PDO::PARAM_STR);
				$stmt->bindParam(':condition', $property['condition'], PDO::PARAM_INT);
				$stmt->bindParam(':standing', $property['standing'], PDO::PARAM_INT);
				$stmt->bindParam(':activities', $property['activities'], PDO::PARAM_STR);
				$stmt->bindParam(':services', $property['services'], PDO::PARAM_STR);
				$stmt->bindParam(':proximities', $property['proximities'], PDO::PARAM_STR);
				$stmt->bindParam(':tags', $property['tags'], PDO::PARAM_STR);
				$stmt->bindParam(':floor', $property['floor']['type'], PDO::PARAM_INT);
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
