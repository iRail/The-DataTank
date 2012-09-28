<?php

/**
 * An class for ITS XML data
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("model/resources/AResourceStrategy.class.php");
include_once("model/DBQueries.class.php");
include_once("includes/ITSXMLToPHP.class.php");

class ITSXML extends AResourceStrategy {

    public function read(&$configObject, $package, $resource) {

        $resultObj = ITSXMLtoPHP::xmlFileToObject($configObject->uri);

        /**
         * Structure of the resulting object:
         * 
         * STRUCTURE PART 1
         * 
         * parkings
         *  parking
         *      name = 
         *      longitude = 
         *      latitude  =
         *  ...
         * 
         * STRUCTURE PART 2
         * 
         * ITSPS (original data)
         */
        $ITSobjectmodel = new stdClass();
        $ITSobjectmodel->parkings = array();

        /*
         * Current structure
         * Offstreetparking is a member of the Operator object
         *   comes in an array
         */

        // get the OffStreetParking's and the OnStreetParking's 



        $datamembers = get_object_vars($resultObj);

        /*
          echo "<pre>";
          print_r($datamembers);
          echo "</pre>";
          exit(); */

        $operator = $datamembers["Operator"];

        $offstreetparkings = null;
        $onstreetparkings = null;

        if (property_exists($operator, "OffstreetParking")) {
            $offstreetparkings = $operator->OffstreetParking;
        }

        if (property_exists($operator, "OnstreetParking")) {
            $onstreetparkings = $operator->OnStreetParking;
        }

        // NOTE they can be arrays' if multiple parkings are present, or just an object, if only one is present
        // 
        // get the OffStreetParking('s)
        // parse them and put the necessary information in STRUCTURE PART 1

        if ($offstreetparkings != null) {

            if (is_object($offstreetparkings)) {

                $parking = $this->assembleParking($offstreetparkings);
                array_push($ITSobjectmodel->parkings, $parking);
            } else { // it's an array of offstreetparkings
                foreach ($offstreetparkings as $offstreetparking) {

                    $parking = $this->assembleParking($offstreetparking);
                    array_push($ITSobjectmodel->parkings, $parking);
                }
            }
        }



        // get the OnStreetParking('s)
        // these are a bit more trickier since they have two definitions
        // for now it looks like GeneralInfo always contains a geolocation, as 
        // is desribed in the xsd schema.
        // parse them and put the necessary information in STRUCTURE PART 1

        if ($onstreetparkings != null) {

            if (is_object($onstreetparkings)) {

                $parking = $this->assembleParking($onstreetparkings);
                array_push($ITSobjectmodel->parkings, $parking);
            } else { // it's an array of offstreetparkings
                foreach ($onstreetparkings as $onstreetparking) {

                    $parking = $this->assembleParking($offstreetparking);
                    array_push($ITSobjectmodel->parkings, $parking);
                }
            }
        }

        $ITSobjectmodel->itsps = $resultObj;


        return $ITSobjectmodel;
    }

    private function assembleParking($ITSparking) {

        $parking = new stdClass();
        
        try {

            $name = $ITSparking->GeneralInfo->IDInfo->Name->Name;
            $latitude = $ITSparking->GeneralInfo->GeoLocation->Latitude->Latitude;
            $longitude = $ITSparking->GeneralInfo->GeoLocation->Longitude->Longitude;
           
            $parking->name = $name;
            $parking->longitude = $longitude;
            $parking->latitude = $latitude;
            return $parking;
            
        } catch (Exception $ex) {
            return $parking;
        }
    }

    public function onUpdate($package, $resource) {
        
    }

    public function documentCreateRequiredParameters() {
        return array("uri");
    }

    public function documentReadRequiredParameters() {
        return array();
    }

    public function documentUpdateRequiredParameters() {
        return array();
    }

    public function documentCreateParameters() {
        return array(
            "uri" => "The uri to the xml document."
        );
    }

    public function documentReadParameters() {
        return array();
    }

    public function documentUpdateParameters() {
        return array();
    }

    // This will probably contain the upper level elements of the xml document, or won't be used at all
    public function getFields($package, $resource) {
        return array();
    }

}
?>