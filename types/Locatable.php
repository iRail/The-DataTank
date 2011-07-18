<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   * Author:  Jan Vansteenlandt <jan aŧ iRail.be>
   * Author:  Pieter Colpaert <pieter aŧ iRail.be>
   * License: AGPLv3
   *
   * This file contains the first frontier that dispaches requests to different method calls. This file will receive the call
   *
   *
   * This interface implements the basic needs for a kml <Placemark>
   * needs -> name, description Point(= made out of latitude and longitude)
   *
   * Notice: If this file reaches more than 100 lines a rewrite is needed
   */

interface Locatable{

     public function getLong();
     public function getLat();
     public function getName();
     public function getDescription();
     
  }

?>