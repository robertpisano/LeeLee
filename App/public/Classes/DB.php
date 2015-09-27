<?php
/*
Every table Must be set up in the form u_
*/

class DB{
	
	public static $query_string = "";
	public static $tableName = "";
	public static $tableList;
	public static $selectPortion = [];
	public static $joinPortion = [];
	public static $wherePortion = [];
	public static $orWherePortion = [];
	public static $groupByPortion = [];
	public static $joinSelections = [];
	public static $orderByPortion;
	public static $PDO_Connection;
	public static $debugging;
	public static $type = "select";
	public static $sqlReturn;
	public static $comparisons = [];
	public static $tablesUsed = []; 
	public static $escapeChars=[
			"\0",
			"\'",
			"\"",
			"\n",
			"\r",
			"\t",
			"\b",
			"\\",
			"\Z",
			"\%",
			"\_"
		];
	public static $comparators = [
			'=',
			'!=',
			'>',
			'<',
			'<=',
			'>=',
			'like',
			'not like',
			'not',
			'null',
			'is',
			'is not'
	];
	//array of tables will only be in select/where functions. 
	//If the table exists in the select/where statements, but no join was used, it will throw an error.
	//also used in the end statment to track tables

	//***************************************************
	/* todo  add logic for tablesUsed in the where function*/
	//***************************************************

	/*************************************************
	// todo: WHERE logic needs to be able to use is not null and such as comparisons
	// null would be the value for the record. Add logic to break up query in this way in the query function
	/*************************************************/

	/*************************************************
	// TODO: add the sanatizePARAMS function, which will sanatize the value in a where statement. 
	/*************************************************

	//************************************************
	// TODO: FINISH SANITIZEPARAMS FUNCTION
	//************************************************

	//*************************************************** 
		todo put the logic for "and" statements on joins*
	//***************************************************

	/*this will escape characters that need to be escaped for mysql injection just as 0, ;, \b, etc etc */
	private static function sanitizeParams($params = array()){
		
		foreach($params as $param)
		{
			if($param){}
		}	
	}

	/*
	used as a helper function to create the queries - will return the "tableName.columnName" for every column.
	This is necessary for join statement where some tables have the same columns and you need to specify which table is from which column
	*/
	private static function getColumnsFromTable($tableName, $column = null){
		if(!isset($tableName)
		{
			$tableName = self::$tableName
		}
		$query = "show COLUMNS from {$tableName}";
		$query = ($column != null && $column != "") ? $query . " like \"{$column}\"" : $query;  
		$PDOstatement = self::$PDO_Connection->prepare($query);
		$PDOstatement->execute();
		$return = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
		$columns = [];
		for ($i=0; $i<count($return); $i++)
		{
			$columns[count($columns)] = "$tableName.".$return[$i]["Field"];
		}
		return $columns;
	}

	/*
		initializes the PDO connection to the database
	*/
	public static function init(){
		if(!isset(self::$PDO_Connection))
		{
			$host = "localhost";
			$name = "upp";
			$user = "root";
			$pass = "root";
			self::$PDO_Connection = new PDO('mysql:host=localhost;dbname=upp', $user, $pass);
		}
	}

	/*
		Another helper function - this is used in select statement to cross check that all columns are actually in the table 

	*/
	public static function existInTable($params = array()){
		$existingColumns = [];
		
		if(empty($params)) return null; 
		
		if(!empty($params) && is_array($params))
		{

			for($i=0; $i<count($params); $i++)
			{
				$selectorStatement = explode('.', $params[$i]); //returns an array [tableName, columnName] or if no table is specified [columnName]
				$table= empty($selectorStatement[1]) ? self::$tableName : trim($selectorStatement[0]); //if there was no table specified, use current table, else use the table specified
				$column = empty($selectorStatement[1]) ? trim($selectorStatement[0]) : trim($selectorStatement[1]); //if no table is specified, columnName will be held in the first array entry from the explodes statement
				
				$columns = self::getColumnsFromTable($table, $column); 

				if(!empty($columns) && count($columns) == 1) 
					$existingColumns[count($existingColumns)] =  "$table.$column" ;
				/* this section of code depends on how the like query in getColumnsFromTable works
				if it only returns columns with exactly the same name then this is not needed. 
				If it returns columns with similar names then this is needed... i leave it here just in case*/
				elseif(!empty($columns)){
					for($i=0;$i<count($columns);$i++)
					{
						if($column == $columns[$i]["Field"]) $existingColumns[count($existingColumns)] =  "$table.".$columns[$i]["Field"];
					}		
				}
				/*end of that portion*/

				else echo "you fucked up. the column {$column} does not exist in table {$table}</br>";
			}	
			return $existingColumns;
		}
	}

	/*
		intended use: select("description"); - for just one select
		for multiple selections
		select(null,  array("description", "name", "id"))

		if column is set, params array will be ignored...
	*/
	public static function select($column = null, $params = array()){ 
		self::$type = "select";
		if(empty($column))
		{
				if(!empty($params))
				{
					//This will only give you the columns (pulled from params) that are in the table currently used. A join query may have multiple columns from a second table
					self::$selectPortion = self::existInTable($params);


					//some may not exist in the current table (set by the class/object) so they werent put in the selectPortion with the previous section
					//it could be because the programmer wasn't specific with the table in the case of joins
					//example: venue->select(["description", "start_time"])
					//join(pr_events, "pr_events.venue_id=pr_venue.id") 
					//start_time will not be in the venue table because the join hasn't been specified yet and it wasn't specified as pr_events.venue
					//save it for later and revisit it it in join function
					foreach($params as $column)
					{
						if(!in_array($column, self::$selectPortion))
						{
							self::$joinSelections[count(self::$joinSelections)] = $column;
						}
					}

					//some columns may exist twice. that means the user wasn't specific with the column  - save it for later in joinSelection
					//ex: select(["name", "name"]);
					//join(pr_events, "pr_events.venue_id=pr_venue.id") 
					//both names would appear in selectPortion - but one must be made more specific - remove it from selectPortion and add it to joinSelections
					for($count1=0; $count1< count(self::$selectPortion); $count1++)
					{
						$count2=$count1+1;
						while($count2< count(self::$selectPortion))
						{
							if(self::$selectPortion[$count1] == self::$selectPortion[$count2])
							{
								self::$joinSelections[count(self::$joinSelections)] = self::$selectPortion[$count2];
								unset(self::$selectPortion[$count2]);
							}
							else
							{
								$count2++;
							}
						}
					}
				}
				else
				{
					echo "you didn't give the select portion any params - I'll be assuming its \"*\" </br>";
				}
		}
		else
		{
			$column = self::existInTable([$column]);
			if(empty($column) || in_array($column, self::$selectPortion))
			{
				self::$joinSelections[count(self::$joinSelections)] = $column;
			}
			else{
				self::$selectPortion[count(self::$selectPortion)] = $column;
			}
		}


	}


	public static function from($tableName = ""){
		self::$tableName = $tableName;
		self::$tablesUsed[count(self::$tablesUsed)] = $tableName;
	}


	public static function join($tableName, $on, $and = array()){
		if(empty($tableName) || empty($on)){
		 echo "your join statment doesn't have a tablename or an on statement.... idiot";
		 return 0;
		}

		self::$tablesUsed[count(self::$tablesUsed)] = $tableName;
		$count = count(self::$joinPortion);

		if(is_array($on))
		{
			self::$joinPortion[$count]["tableName"] = $tableName;
			self::$joinPortion[$count]["on"]["column1"] = !empty($on["column1"]) ? $on["column1"] : $on[0];
			self::$joinPortion[$count]["on"]["comparison"] = !empty($on["comparison"]) ? $on["comparison"] : $on[1];
			self::$joinPortion[$count]["on"]["column2"] = !empty($on["column2"]) ? $on["column2"] : $on[2];
		}

		if(is_string($on))
		{
			self::$joinPortion[$count]["on"]["string"] = $on;
		}

		//in the select function if the columns weren't found through existInTable it could be a result of two things
		//a) not being specific - it will only
		//b) the column just doesn't exist
		//in the case of a - this will give it a more specific context for tableName
		if(!empty(self::$joinSelection))
		{	
			$selectStatement = [];
			$count = 0;
			foreach(self::$joinSelection as $column)
			{
				$selectStatement[$count] = "$tableName.$column"; //tablename is from the parameters of join function
				$count++;
			}
			self::select($selectStatement);
		}
		/*
		//*************************************************** 
		todo put the logic for "and" statements on joins*
		//***************************************************
		*/
	}

	public static function orWhere($columnName, $value, $comparison = '='){

	}


	public static function in(){


	}

	public static function leftJoin(){

	}

	public static function insert(){


	}


	public static function where($columnName, $comparison = "=", $value ="", $params = array() ){	
		$thisWhere;
		if(!empty(self::existInTable([$columnName])))
		  $columnName =  self::existInTable([$columnName])[0];
		else{ return 0;}


		if(in_array($comparison, self::$Comparisons) == 0) {
			echo "you fucked up the comparison value in the where statement.</br>"; 
			return 0;
		}
		if(!empty($columnName) && !empty($value) && !empty($comparison))
			self::$wherePortion[count(self::$wherePortion)] = [
				"column"=>$columnName,
				"value"=>$value,
				"comparison"=>$comparison
			];
		if(!empty($params))
		{
			foreach($params as $whereParam)
			{
				if(!empty($whereParam["column"]) && !empty($whereParam["value"]) && !empty($whereParam["comparison"]))
					$count = count(self::$wherePortion);
					self::$wherePortion[$count] = [
					"column"=>$columnName,
					"value"=>$value,
					"comparison"=>$comparison
				];
			}
		}
		else self::in($params);
	}

	public static function groupBy($field){
		if(!empty($field))
		{
			$fieldArray = !is_array($field) ? [$field] : $field;
			self::$groupByPortion = self::existInTable($fieldArray);
		}
	}

	public static function orderBy($field){
		if(!empty($field))
		{
			$fieldArray = !is_array($field) ? [$field] : $field;
			self::$orderByPortion = self::existInTable($fieldArray);
		}
	}

	public static function execute($statement = null){
		$query = self::$PDO_Connection->prepare($statement);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	

	
	public static function query($query){
		/*desconstruct query then reconstruct it you'll be able to run checks */

		$reserved_words = [
						"select",
						"from",
						"where",
						"and",
						"or",
						"order",
						"order by",
						"insert",
						"in",
						"into",
						"join",
						"left",
						"left join",
						"group",
						"group by",
						"distinct"	
		];
		$partial_reserved_words = ["inner","outer","group","order"];

		if(!is_string($query)){echo "you need to pass th query function a string"; return 0;}
		//deconstruct query
		$queryAsArray = explode(" ", $qeury);

		//go through each "reserved word", grab its params, and call the corresponding function for it
		$i=0;
		while($i<count($queryAsArray))
		{
			if(in_array($queryAsArray[$i], $reserved_words))
			{	
				$reservedWord = $queryAsArray[$i];

				//two-worded reserved words will get split in half by the explode statement, this makes them whole again
				if(in_array($queryAsArray[$i] . " " . $queryAsArray[$i+1], $partial_reserved_words))
				{
					$i++;
					$reservedWord = $reservedWord . " " . $queryAsArray[$i];
				}

				/* phrases that have quotations may get split apart. 
				This part will take care of whether or not there are quotations - it will 
				conglomerate and treat the quotation as one param example:
				caption = "this shit is bananas b-a-n-a-n-a-s"*/
				$x=0;
				$i++;
				$thisPart = [];
				while(!in_array($queryAsArray[$i], $reserved_words) && $i<count($queryAsArray))
				{
					$thisPart[$x] = $queryAsArray[$i];
					//searches for whether or not the first and last occurrences of ' and " if they are equal
					if(strpos($queryAsArray[$i], "'") && (strpos($queryAsArray[$i], "'") == strrpos($queryAsArray[$i], "'") || strpos($queryAsArray[$i], "\"") != strrpos($queryAsArray[$i], "\"")))
					{
						$indexOfQuotation = strpos($queryAsArray[$i], "'") ? strpos($queryAsArray[$i], "'") : strpos($queryAsArray[$i], "\"");
						$quotation = substr($queryAsArray[$i], $indexOfQuotation, $indexOfQuotation+1);
						$i++;
						while(!strpos($queryAsArray[$i], $quotation) && $i<count($queryAsArray))
						{
							$thisPart[$x] = $thisPart[$x] . " " . $queryAsArray[$i];
							$i++;
						}
						if(strpos($queryAsArray[$i], $quotation))
						{
							$thisPart[$x] = $thisPart[$x] . " " . $queryAsArray[$i];
							$i++;
						}
						$x++;
					}
					else{
						$i++;
						$x++;
					}	
				}

				//if(in_array($queryAsArray[$i], $partial_reserved_words) && !in_array())


				switch($reservedWord){
					case "select":
						if(count($thisPart)>1)
						{
							$j=0;
							/*iterate through the select statements to get rid of any trailing commas, they will be placed back in there*/
							while($j < count($thisPart))
							{
								self::select();
							}
						}
						if(count($thisPart) == 1)
						{
							self::select();
						}
						break;
					case "from":
						if(count($thisPart) == 1)
						{
							self::from();
						}
						break;
					case "where":

						break;
					case "and":
						break;
					case "or":
						break;
					case "order by":
						break;
				}

			}
			else
			{
				echo "you've fucked up at the word ". $queryAsArray[$i];
				return 0;
			}

		}

		$PDOStatement = self::$PDO_connection->prepare($query);
		$PDOStatement->execute();
		return $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function run(){
		if(self::$type == "select")
		{
			if(empty(self::$selectPortion))
			{
				//self::$query_string = "select * from " . self::$tableName;
				for($i=0; $i<count(self::$tablesUsed); $i++)
				{
					$allColumns = self::getColumnsFromTable(self::$tablesUsed[$i]);	
					self::select(null, $allColumns);
				}
				
			}

			self::$query_string = "select";
			for($iterator = 0; $iterator<count(self::$selectPortion); $iterator++)
			{
					self::$query_string =  self::$query_string . " " . self::$selectPortion[$iterator];
					//two underscores denotes the table name : table{undersocre}{underscore}columna
					self::$query_string =  self::$query_string . " as " . explode("." , self::$selectPortion[$iterator])[0] . "___" . explode(".",self::$selectPortion[$iterator])[1];
					self::$query_string = $iterator < count(self::$selectPortion)-1 ? self::$query_string  . ","   : self::$query_string;
			}
			self::$query_string = self::$query_string . " from  " . self::$tableName;
		
			
			if(!empty(self::$joinPortion))
			{
				for($i = 0; $i<count(self::$joinPortion); $i++)
				{
					self::$query_string = self::$query_string . " join " . self::$joinPortion[$i]["tableName"];

					if(!empty(self::$joinPortion[$i]["on"]["column1"]) && !empty(self::$joinPortion[$i]["on"]["comparison"]) && !empty(self::$joinPortion[$i]["on"]["column2"]) )
					{
						self::$query_string = self::$query_string 
						. " on " 
						. self::$joinPortion[$i]["on"]["column1"] 
						. " "
						. self::$joinPortion[$i]["on"]["comparison"]
						. " " 
						. self::$joinPortion[$i]["on"]["column2"];
					}
					if(array_key_exists("string", self::$joinPortion[$i]["on"]) && !empty(self::$joinPortion[$i]["on"]["string"]))
					{
						self::$query_string = self::$query_string . " on " . self::$joinPortion[$i]["on"]["string"];
					}
				}
			}

			if(!empty(self::$wherePortion))
			{
				self::$query_string = self::$query_string . " " . " where" ;
				for($iterator = 0; $iterator<count(self::$wherePortion); $iterator++)
				{
					if(is_int(self::$wherePortion[$iterator]["value"]))
						self::$query_string = $iterator < count(self::$wherePortion)-1 
							? self::$query_string . " " . self::$wherePortion[$iterator]["column"]. " " . self::$wherePortion[$iterator]["comparison"] . " " . self::$wherePortion[$iterator]["value"].","   
							: self::$query_string  . " " . self::$wherePortion[$iterator]["column"] . " " .  self::$wherePortion[$iterator]["comparison"] . " " . self::$wherePortion[$iterator]["value"];
					if(is_string(self::$wherePortion[$iterator]["value"]))
						self::$query_string = $iterator < count(self::$wherePortion)-1 
							? self::$query_string . " " . self::$wherePortion[$iterator]["column"]. " " . self::$wherePortion[$iterator]["comparison"] . " \"" . self::$wherePortion[$iterator]["value"]."\" and"   
							: self::$query_string  . " " . self::$wherePortion[$iterator]["column"] . " " .  self::$wherePortion[$iterator]["comparison"] . " \"" . self::$wherePortion[$iterator]["value"]. "\"";
				}
			}

			if(!empty(self::$groupByPortion))
			{
				self::$query_string = self::$query_string . " group by " . self::$groupByPortion[0]; 
			}

			if(!empty(self::$orderByPortion))
			{
				self::$query_string = self::$query_string . " order by " . self::$orderByPortion[0]; 
			}



			echo self::$query_string;
			$PDOstatement = self::$PDO_Connection->prepare(self::$query_string);
			$PDOstatement->execute();
			$columns = $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
			
			echo "<br><br>";
			var_dump($columns);
			self::$sqlReturn = $columns;
			return $columns;
		}
	}

}