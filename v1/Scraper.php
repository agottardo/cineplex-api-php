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

    private $MOVIES_NOW_PLAYING_CACHE_FILE_NAME = "movies_np_cache.txt";
    private $MOVIES_COMING_SOON_CACHE_FILE_NAME = "movies_cs_cache.txt";
    private $MOVIES_NOW_PLAYING_API_ENDPOINT = "https://api.cineplex.com/api.svc/MoviesNowPlaying/?AccessKey=ED63F40D-5165-49AD-9975-A463DDF122D5";
    private $MOVIES_COMING_SOON_API_ENDPOINT = "https://api.cineplex.com/api.svc/MoviesComingSoon/?AccessKey=ED63F40D-5165-49AD-9975-A463DDF122D5";

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
    private function __construct() {}

    /**
     * Fetch a list of theatres from api.cineplex.com and save them
     * to the $theatresDb array.
     */
    public function fetchTheatres() {
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

    public function fetchMovies() {
        if (!file_exists($this->MOVIES_NOW_PLAYING_CACHE_FILE_NAME)) {
            // We do not have a cached version on disk, so download one and read that.
            $npContents = file_get_contents($this->MOVIES_NOW_PLAYING_API_ENDPOINT);
            file_put_contents($this->MOVIES_NOW_PLAYING_CACHE_FILE_NAME, $npContents);
        } else {
            // We already have a cached version, so just read that.
            $npContents = file_get_contents($this->MOVIES_NOW_PLAYING_CACHE_FILE_NAME);
        }
        $xml = new SimpleXMLElement($npContents);
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

        if (!file_exists($this->MOVIES_COMING_SOON_CACHE_FILE_NAME)) {
            // We do not have a cached version on disk, so download one and read that.
            $csContents = file_get_contents($this->MOVIES_COMING_SOON_API_ENDPOINT);
            file_put_contents($this->MOVIES_COMING_SOON_CACHE_FILE_NAME, $csContents);
        } else {
            // We already have a cached version, so just read that.
            $csContents = file_get_contents($this->MOVIES_COMING_SOON_CACHE_FILE_NAME);
        }
        $xml = new SimpleXMLElement($csContents);
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

        $serverName = "clipperboard.database.windows.net";
        $connectionOptions = array(
            "Database" => "clipperboardsql",
            "Uid" => "andreagott",
            "PWD" => "y4UNqDcx2tH89Y98a*]6Jw72U@2"
        );

        //Establishes the connection
        $conn = sqlsrv_connect($serverName, $connectionOptions);
        assert($conn != false);
        foreach ($this->moviesDb as $movie) {
            $tsql = "INSERT INTO movies (title, releaseDate, runtime, genres, synopsis, posterURL, trailerURL, rating, webURL, cineplexId)
VALUES (?,?,?,?,?,?,?,?,?,?);";
            $params = array($movie->name, $movie->releaseDate, $movie->runtime, $movie->genres, $movie->synopsis, $movie->posterURL, $movie->trailerURL, $movie->rating, $movie->webURL, $movie->id);
            $getResults = sqlsrv_query($conn, $tsql, $params);
            $rowsAffected = sqlsrv_rows_affected($getResults);
            if ($getResults == FALSE or $rowsAffected == FALSE)
                die(sqlsrv_errors());
            else
                sqlsrv_free_stmt($getResults);
        }


    }

}