<?php

require __DIR__ . '/vendor/autoload.php';


echo "testing the routing functions </br>";

$host = "localhost";
$name = "upp";
$user = "root";
$pass = "root";

//$selectTest = ["u_event.name","u_event.id","u_event.Description", "u_venue.id", "u_venue.description", "u_venue.name"];
$queryAsArray = " robert' ''  sdlvkn ";
if(!strpos($queryAsArray, "'")){echo "quack";}

echo DB::init();
DB::select();
DB::from("u_venue");
DB::where("name", "=", "Rob's room");
//DB::where("id", "=", "xy");
DB::join("u_event", ["u_event.venue_id", "=", "u_venue.id"]);
DB::orderBy(["u_event.name"]);
$x = DB::run();
echo count($x);	