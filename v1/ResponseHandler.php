<?php
/**
 * Created by PhpStorm.
 * User: agott
 * Date: 2018-03-08
 * Time: 21:05
 */

function outputArray($arrayName, $array)
{
    // In the JSON, in addition to the DB also add:
    // - timestamp at which the response was generated
    // - timestamp at which the data was sourced from api.cineplex.com
    // - hostname of the server that generated the response
    $responseArray["generated_at"] = time();
    //$responseArray["sourced_at"] = filemtime($filename);
    $responseArray["generated_by"] = gethostname();
    $responseArray[$arrayName] = $array;
    // Print the response.
    echo(json_encode($responseArray, JSON_PRETTY_PRINT));
}