<?php
/**
 * Created by PhpStorm.
 * User: agott
 * Date: 2018-03-07
 * Time: 19:55
 */

require_once("../vendor/autoload.php");
require_once("Scraper.php");

$singleton = Clapperboard\Scraper::sharedInstance();
$singleton->fetchMovies();
include "ResponseHandler.php";
outputArray("movies", $singleton->moviesDb);

?>