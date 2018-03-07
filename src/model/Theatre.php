<?php
/**
 * Created by PhpStorm.
 * User: agott
 * Date: 2018-03-07
 * Time: 14:14
 */

namespace Clapperboard;

class Theatre {
    public $id;
    public $name;
    public $address1;
    public $address2;
    public $city;
    public $provinceCode;
    public $postalCode;
    public $latitude;
    public $longitude;
    public $urlSlug;
    public $isDriveIn;
    public $imageURL;
    public $movies;

    /**
     * Produces a formatted version of the address, by also merging address1 and address2
     * when necessary.
     * @param $withName boolean whether to add the name or not
     * @return String formatted address
     */
    public function getFormattedAddress($withName) : String {
        $retVal = $this->address1.", ".$this->city." ".$this->provinceCode." ".$this->postalCode;
        if ($withName) {
            $retVal = $this->name.", ".$retVal;
        }
        return $retVal;
    }
}