<?php
/**
 * Created by PhpStorm.
 * User: agott
 * Date: 2018-03-07
 * Time: 14:30
 */

namespace Clapperboard;

require_once("model/Theatre.php");

final class Scraper {

    public $theatresDb = array();

    /**
     * Singleton entry point.
     * @return Scraper
     */
    public static function sharedInstance() {
        static $inst = null;
        if ($inst === null) {
            $inst = new Scraper();
        }
        return $inst;
    }

    /**
     * Private constructor [defeats instantiation].
     */
    private function __construct() {

    }

    public function getTheatres() {
        $cache_file_name = "theatres_cache.txt";
        if (!file_exists($cache_file_name)) {
            $endpointURL = "https://www.cineplex.com/api/v1/theatres?language=en-us&range=100000&skip=0&take=1000";
            $json = file_get_contents($endpointURL);
            file_put_contents($cache_file_name, $json);
        } else {
            $json = file_get_contents($cache_file_name);
        }
        $json_obj = json_decode($json);
        $data = $json_obj->data;
        $totalCount = $json_obj->totalCount;
        foreach ($data as $aTheatreData) {
            $newTheatre = new Theatre();
            $newTheatre->id = $aTheatreData->id;
            $newTheatre->name = $aTheatreData->name;
            $newTheatre->address1 = $aTheatreData->address1;
            $newTheatre->address2 = $aTheatreData->address2;
            $newTheatre->city = $aTheatreData->city;
            $newTheatre->provinceCode = $aTheatreData->provinceCode;
            $newTheatre->postalCode = $aTheatreData->postalCode;
            $newTheatre->latitude = $aTheatreData->latitude;
            $newTheatre->longitude = $aTheatreData->longitude;
            $newTheatre->urlSlug = $aTheatreData->urlSlug;
            $newTheatre->isDriveIn = $aTheatreData->isDriveIn;
            $newTheatre->imageURL = $aTheatreData->mobileBackgroundImageUrl;
            array_push($this->theatresDb, $newTheatre);
        }
        $responseArray = array();
        $responseArray["generated_at"] = time();
        $responseArray["generated_by"] = gethostname();
        $responseArray["theatres"] = $this->theatresDb;
        echo(json_encode($responseArray, JSON_PRETTY_PRINT));
    }

}