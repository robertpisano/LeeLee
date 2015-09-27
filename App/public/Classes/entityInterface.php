<?php

class entityInterface extends DB{

	protected const $entityName;

	/* this will be set in individual entity the params of the "key" => "value" pairs */
	protected $params; 

	/* also set in the individual entity - will be all available variables in a "key"=>"value" array */
	protected $schema; 

	/* this will just be an array of the variable names Example array("variable1", 	"variable2",...)*/
	protected $entityVariableNames; 



	/* Will take all keys from schema and throw them into an array, you could just specify it in the venue, but this is better - more abstract - don't have to define as much in entity*/ 
	protected setEntityVariableNames()
	{
		/*TODO: implement exceptions */
		if(empty(this->$schema))
		{
			echo "you must set the Schema in the Entity, your interface has no structure to work with";
			return 0;
		}

		$i=0;
		$variableNames;
		foreach($schema as $key => $value)
		{
			$variableNames[$i] = $key;
		}
		this->$entityVariableNames = $variableNames;
	}


	/*does exactly what it says, will get rid of any keys that dont  exist in the schema for abstraction purposes*/
	private deleteNonExistingKeys($array)
	{
		if(!is_array($array))
		{
			if(is_bool($array))
				echo "deleteNonExistingKeys must be given an array- you've given it a boolean";
			if(is_numeric($array))
				echo "deleteNonExistingKeys must be given an array- you've given it a number";
			if(is_string($array))
				echo "deleteNonExistingKeys must be given an array- you've given it a string";
			return 0;
		}


		$newParams = [];
		$variablesLength = isSet(this->$entityVariableNames) ? count(this->$entityVariableNames) : null;

		foreach($array as $key => $value)
		{
			if(array_key_exists($key, this->$schema))
			{
				$newParams[$key] = $array[$key]; 
			}
		}

		return $newParams;
	}

	/*
		this checks to see if all the variables for this entity are set
		this is now the only place entityVariables is used.

	*/

	protected needsSearch()
	{

		boolean $needSearch = false; /* will remain false if all the variables for the Entity schema are filled*/

		$schemaLength = count(this->schema);

		for($i=0;$i<$schemaLength; $i++)
		{
			if(!isSet(this->$params[this->$entityVariableNames[$i]])
			{
				$needsSearch = true;
			}

		}

		return $needsSearch;
	}


	/* 
			Takes $params and makes sure it's minimal and compliant with this->$schema uses the deleteNonExistingKeys function
	*/
	protected cleanParams()
	{
		this->$params = deleteNonExistingKeys(this->$params);
		
	}

	/*
		will search off the set parameters for this entity - which means whatever paramters are available will be searched off of to get the rest
	*/
	protected search()
	{
		if(!empty(this->$entityVariableNames)){ this->setEntityVariableNames;}
		this->cleanParams();
		this->select();
		foreach(this->$params as $key => $value)
		{
			this->where($key, "=", $value);
		}
		this->run();

	}

	/* may be based off the precedence that each value returned will be Entity_variable. Not ideal. need a better way*/

	protected setObjects($sqlReturnParam = array())
	{
		/* 
			need to do some testing before implementing this
			- what does it looks like when multiple values are returned?
			- what does it look like when joins are returned - you'll have to parse that data


			-- here is example: 
			select u_event.name, u_event.id, u_event.Description, u_venue.id, u_venue.description, u_venue.name from u_event join u_venue on u_event.venue_id = u_venue.id order by u_event.name
			array(4) { 
				array(4) { 
					[0]=> array(6) { 
							["event_name"]=> string(13) "Charity Event" 
							["event_id"]=> string(1) "5" 
							["event_Description"]=> string(21) "I'm making a big test" 
							["venue_id"]=> string(1) "2" ["venue_description"]=> NULL 
							["venue_name"]=> string(6) "Olapic" } 
					[1]=> array(6) { 
							["event_name"]=> string(8) "open bar" 
							["event_id"]=> string(1) "2" 
							["event_Description"]=> string(16) "olapics open bar" 
							["venue_id"]=> string(1) "2" 
							["venue_description"]=> NULL 
							["venue_name"]=> string(6) "Olapic" } 
					[2]=> array(6) { 
							["event_name"]=> string(11) "Pants Party" 
							["event_id"]=> string(1) "1" 
							["event_Description"]=> string(86) "...... I really don't know what to say, this is just one big sexual innuendo of a test" 
							["venue_id"]=> string(1) "1" 
							["venue_description"]=> NULL 
							["venue_name"]=> string(10) "Rob's Room" }
					[3]=> array(6) { 
							["event_name"]=> string(4) "Test" 
							["event_id"]=> string(1) "4" 
							["event_Description"]=> string(54) "test numbero tres - want to see multiple rows returned" 
							["venue_id"]=> string(1) "1" 
							["venue_description"]=> NULL 
							["venue_name"]=> string(10) "Rob's Room" 
						} 
					}
				-- note - you'll need to change shit in the SQL database for handling joins. You'd expect the "name" parameter to hold
				the name of the event, since were selecting from u_event - but the event name was overwritten by the venue name, the only 
				fields that are right are the description fields - since their array names aren't identical.

				

				array(2) { 
					[0]=> array(24) { 
						["id"]=> string(1) "1" 
						["name"]=> string(11) "Pants Party" 
						["type"]=> string(5) "Party" 
						["street_num"]=> string(3) "142" 
						["street_name"]=> string(10) "Rensselaer" 
						["street_type"]=> string(6) "Avenue" 
						["city"]=> string(13) "Staten Island" 
						["state"]=> string(2) "NY" 
						["zip"]=> string(5) "10312" 
						["zip_plus_four"]=> NULL 
						["longitude"]=> string(8) "-74.1818" 
						["latitude"]=> string(7) "40.5484" 
						["phone"]=> string(12) "917-733-6817" 
						["url"]=> NULL ["capacity"]=> string(1) "2" 
						["created_timestamp"]=> string(19) "2015-06-04 12:40:59" 
						["modified_timestamp"]=> string(19) "0000-00-00 00:00:00" 
						["description"]=> string(86) "...... I really don't know what to say, this is just one big sexual innuendo of a test" ["current_amount"]=> NULL 
						["venue_id"]=> string(1) "1" 
						["start_time"]=> string(19) "2015-06-04 12:00:00" 
						["end_time"]=> string(19) "2016-05-10 13:00:00" 
						["likes"]=> string(3) "100" 
						["attendees"]=> string(1) "2" } 
					[1]=> array(24) { 
						["id"]=> string(1) "4" 
						["name"]=> string(4) "Test" 
						["type"]=> string(5) "Party" 
						["street_num"]=> string(3) "142" 
						["street_name"]=> string(10) "Rensselaer" 
						["street_type"]=> string(6) "Avenue" 
						["city"]=> string(13) "Staten Island" 
						["state"]=> string(2) "NY" ["zip"]=> string(5) "10312" 
						["zip_plus_four"]=> NULL ["longitude"]=> string(8) "-74.1818" 
						["latitude"]=> string(7) "40.5484" 
						["phone"]=> string(12) "917-733-6817" 
						["url"]=> NULL ["capacity"]=> string(1) "2" 
						["created_timestamp"]=> string(19) "2015-07-20 09:31:33" 
						["modified_timestamp"]=> string(19) "0000-00-00 00:00:00" 
						["description"]=> string(54) "test numbero tres - want to see multiple rows returned" 
						["current_amount"]=> NULL ["venue_id"]=> string(1) "1" 
						["start_time"]=> string(19) "2015-08-06 00:00:00" 
						["end_time"]=> string(19) "2015-08-09 00:00:00" 
						["likes"]=> string(2) "14" 
						["attendees"]=> string(1) "5" 
					} 
				}
		*/
		$sqlReturn; 
		if(!empty($sqlReturnParam)){$sqlReturn = $sqlReturnParam;}
		if(!empty(this->$sqlReturn)){$sqlReturn = this->$sqlReturn;}
		if(!empty($sqlReturn))
		{
			$entityType = this->$entityName;
			if(count($sqlReturn) == 0)
				echo "either you didn't run a query.... or no results were returned";
			if(count($sqlReturn) == 1)
			{
				foreach($sqlReturn as $key -> $value)
				{	
						$table = isSet(explode("___", $key)[1]) ? explode("___", $key)[0] : null; 
						//this should literally always be set because a query shouldn't run without 
						$
				}
			}

		}
	}

	protected newInsert(){

	}



	protected updateEntityEntry($id, $columnsAndValuesToUpdate, $params){
		if(empty($id) && empty($params))
		{
			echo "cannot update - nothing given to update";
		}
		//use params if id is empty
		if(empty($id))
		{

			//TODO create a function that will search if the key exists in the schema, 
			//if it does, place the key and value in a new array and return the new array
			//basically checks for bogus keys
			foreach($params as $key => $value)
			{
				if(array_key_exists($key, $schema)) //if key exists in the schema -- only search for relevant columns
				{
					this->where($key, "=", $value);	
				}
			}
			this->run();
			if(count(this->$sqlReturn)>1)
			{
				echo "you did not provide enough search terms - cannot update single entity"
			}
			else{
				foreach($params as $key, $schema)
				this->update(
				this->where("id", this->$sqlReturn()["id"]);
			}


		}



	}

}