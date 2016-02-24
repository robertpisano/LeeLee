<?php

$startTime = microtime(true);
require_once __DIR__.'/../../vendor/autoload.php';
use Silex\Application;

// This is the default config.
$config = array(
    'debug' => true,
    'timer.start' => $startTime,
    'monolog.name' => 'silex-bootstrap',
    'monolog.level' => \Monolog\Logger::DEBUG,
    'monolog.logfile' => __DIR__.'/log/app.log',
    'db' => array(
        'dbs.options' => array(
            'photos' => array(
                'driver' => 'pdo_mysql',
                'host' => '192.168.50.4',
                'dbname' => 'Cibo_NY',
                'user' => 'root',
                'password' => 'MattLeeRobMike123',
                'charset' => 'utf8',
            ),
        ),
    ),
);

// Initialize Application
$app = new Application($config);

// Register DoctrineServiceProvider
$app->get("/business/nearby/{latitude}/{longitude}", 
    function($latitude, $longitude){
       $returnArray = array(
            "data" => array(
                    "businesses" => array(
                        array(
                            "name" => "testing",
                            "celebrating_days" => array(
                                    "hotdogs",
                                    "iceCream"
                                ),
                            "deals" => array(
                                    array(
                                        "date" => "2016-02-22",
                                        "deal" => "20 dollars off ice cream"
                                        ),
                                    array(
                                        "date" => "2016-02-22",
                                        "deal" => "20 cents off hot dogs"
                                        )
                                )
                            ),
                        array(
                            "name" => "testing2",
                            "celebrating_days" => array(
                                    "hotdogs",
                                    "iceCream"
                                ),
                            "deals" => array(
                                    array(
                                        "date" => "2016-02-22",
                                        "deal" => "20 dollars off ice cream"
                                        ),
                                    array(
                                        "date" => "2016-02-22",
                                        "deal" => "20 cents off hot dogs"
                                        )
                                )
                            ),
                        array(
                            "name" => "testing3",
                            "celebrating_days" => array(
                                    "hotdogs",
                                    "iceCream"
                                ),
                            "deals" => array(
                                    array(
                                        "date" => "2016-02-22",
                                        "deal" => "20 dollars off ice cream"
                                        ),
                                    array(
                                        "date" => "2016-02-22",
                                        "deal" => "20 cents off hot dogs"
                                        )
                                )
                            )
                )
            )
        );
        $return = json_encode($returnArray);
        return $return;
} );


return $app;
