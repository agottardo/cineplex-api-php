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

    private $THEATRES_CACHE_FILE_NAME = "theatres_cache.txt";

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
    private function __construct()
    {
    }

    /**
     * Fetch a list of theatres from api.cineplex.com and save them
     * to the $theatresDb array.
     */
    public function fetchTheatres()
    {
        // The function caches the result on the server as a .txt to avoid
        // marking too many request to api.cineplex.com.
        // TODO: Download a new version from the API every other day or so.
        if (!file_exists($this->THEATRES_CACHE_FILE_NAME)) {
            // We do not have a cached version on disk, so download one and read that.
            $endpointURL = "https://www.cineplex.com/api/v1/theatres?language=en-us&range=100000&skip=0&take=1000";
            $json = file_get_contents($endpointURL);
            file_put_contents($this->THEATRES_CACHE_FILE_NAME, $json);
        } else {
            // We already have a cached version, so just read that.
            $json = file_get_contents($this->THEATRES_CACHE_FILE_NAME);
        }
        // Decode the JSON from the file.
        $json_obj = json_decode($json);
        $data = $json_obj->data;
        // The API provides a number of theatres. Keep this in mind for later.
        $totalCount = $json_obj->totalCount;
        foreach ($data as $aTheatreData) {
            // For each theatre in the API response, create a new Theatre object
            // so that we can work more easily with it under the OOP paradigm.
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
            // Add the theatre to our DB.
            array_push($this->theatresDb, $newTheatre);
        }
        // Before going anywhere, make sure the number of theatres in our DB is the same
        // as what should have been supposedly provided by the Cineplex API.
        assert(sizeof($this->theatresDb) == $totalCount);
    }

    /**
     * Produce a JSON format output of the theatres database.
     * PRECONDITION: $this->fetchTheatres() was called previously.
     */
    public function printTheatres()
    {
        $responseArray = array();
        // In the JSON, in addition to the DB also add:
        // - timestamp at which the response was generated
        // - timestamp at which the data was sourced from api.cineplex.com
        // - hostname of the server that generated the response
        $responseArray["generated_at"] = time();
        $responseArray["sourced_at"] = filemtime($this->THEATRES_CACHE_FILE_NAME);
        $responseArray["generated_by"] = gethostname();
        $responseArray["theatres"] = $this->theatresDb;
        // Print the response.
        echo(json_encode($responseArray, JSON_PRETTY_PRINT));
    }

}