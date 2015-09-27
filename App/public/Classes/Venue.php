<?php

class Venue extends DB{
	protected const $schema = [
			"id" => "",
			"description" => "",
			"type" => "",
			"addressFull" => "",
			"streetNum" => "",
			"streetName" => "",
			"streetType" => "",
			"city" => "",
			"zip" => "",
			"phone" => "",
			"capacity" => "",
			"url" => "",
			"latitude" => "",
			"longitutde" => ""
	];
	protected $id;
	public $description;
	public $name;
	public $type;
	public $addressFull;
	public $streetNum;
	public $streetName;
	public $streetType;
	public $city;
	public $state;
	public $zip;
	public $phone;
	public $capacity;
	public $description;
	public $url;
	public $latitude;
	public $longitude;

	/*array of comment objects associated with this venue*/
	public $comments = [];

	/*array of events associated with this venue*/
	public $events = [];

	public __construct($id, $name, $type, $addressFull, $city, $state, $zip, $phone, ){
		this->$tableName = "u_venue";
		this->$entityName = "venue";


	}

	public updateVenue()
	{

	}


	public updateAddress()
	{

	}

	public 
}