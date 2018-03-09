<?php
/**
 * Created by PhpStorm.
 * User: agott
 * Date: 2018-03-07
 * Time: 14:30
 */

namespace Clapperboard;

use SimpleXMLElement;

require_once("model/Theatre.php");
require_once("model/Movie.php");

final class Scraper {

    public $theatresDb = array();
    public $moviesDb = array();

    private $THEATRES_CACHE_FILE_NAME = "theatres_cache.txt";
    private $THEATRES_API_ENDPOINT = "https://www.cineplex.com/api/v1/theatres?language=en-us&range=100000&skip=0&take=1000";

    private $MOVIES_CACHE_FILE_NAME = "movies_cache.txt";
    private $MOVIES_API_ENDPOINT = "https://api.cineplex.com/api.svc/FeaturedNowPlayingAndComingSoon/?AccessKey=ED63F40D-5165-49AD-9975-A463DDF122D5";

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
            $json = file_get_contents($this->THEATRES_API_ENDPOINT);
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

    public function fetchMovies()
    {

        if (!file_exists($this->MOVIES_CACHE_FILE_NAME)) {
            // We do not have a cached version on disk, so download one and read that.
            $urlContents = file_get_contents($this->MOVIES_API_ENDPOINT);
            file_put_contents($this->MOVIES_CACHE_FILE_NAME, $urlContents);
        } else {
            // We already have a cached version, so just read that.
            $urlContents = file_get_contents($this->MOVIES_CACHE_FILE_NAME);
        }
        $xml = new SimpleXMLElement($urlContents);
        foreach ($xml->entry as $entry) {
            $contentXml = $entry->content->children("http://schemas.microsoft.com/ado/2007/08/dataservices/metadata")->children("http://schemas.microsoft.com/ado/2007/08/dataservices");
            $newMovie = new Movie();
            $newMovie->id = (int)$contentXml->FilmID;
            $newMovie->name = (string)$contentXml->Title;
            $newMovie->releaseDate = (string)$contentXml->ReleaseDate;
            $newMovie->genres = (string)$contentXml->Genre;
            $newMovie->synopsis = (string)$contentXml->Synopsis;
            $newMovie->posterLargeURL = (string)$contentXml->LargePosterImageURL;
            $newMovie->trailerURL = (string)$contentXml->TrailerURL;
            $newMovie->webURL = (string)$contentXml->WebURL;
            $newMovie->runtime = (int)$contentXml->Runtime;
            $newMovie->rating = (string)$contentXml->Rating;
            array_push($this->moviesDb, $newMovie);
        }
    }

}