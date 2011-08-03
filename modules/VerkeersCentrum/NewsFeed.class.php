<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This method of Verkeerscentrum will get the newsfeed of Belgian traffic jams, accidents and works
 */

class NewsFeed extends AResource{

     private $lang;

     public static function getParameters(){
	  return array("lang" => "Language in which the newsfeed should be returned");
     }

     public static function getRequiredParameters(){
	  return array();
     }

     public function setParameter($key,$val){
	  if($key == "lang"){
	       $this->lang = $val;
	  }
     }

     public function call(){
	  $data = $this->getData();
	  return $this->parseData($data);
     }

    private function getData(){
	  $scrapeUrl = "http://www.verkeerscentrum.be/verkeersinfo/tekstoverzicht_actueel?lastFunction=info&sortCriterionString=TYPE&sortAscending=true&autoUpdate=&cbxFILE=CHECKED&cbxINC=CHECKED&cbxRMT=CHECKED&cbxINF=CHECKED&cbxVlaanderen=CHECKED&cbxWallonie=CHECKED&cbxBrussel=CHECKED&searchString=&searchStringExactMatch=true";
	  return utf8_encode(TDT::HttpRequest($scrapeUrl)->data);
     }
     
     private function parseData($data){
	  preg_match_all('/<tr>.*?<td width="2" bgcolor="#EAF0BF"><\/td>.*?<td width="68" height="31" style="width:68px; height=31px" bgcolor="#EAF0BF" align="center" valign="middle"><img border="0" src="images\/(.*?).gif" alt="" width="31" height="31" \/>.*?<\/td>.*? class="Tekst_bericht">(.*?)<\/span>.*?class="Tekst_bericht">(.*?)\s*<\/span>.*?class="Tekst_bericht">(.*?)<\/span>/smi', $data, $matches, PREG_SET_ORDER);
	  //1 = soort
	  //2 = location
	  //3 = message
	  //4 = time
	  $result = new stdClass();
	  $i = 0;

	  foreach($matches as $match){
	       $cat = $match[1];
	       $cat = str_ireplace("ongeval_driehoek","accident",$cat);
	       $cat = str_ireplace("file_driehoek","traffic jam",$cat);
	       $cat = str_ireplace("i_bol","info",$cat);
	       $cat = str_ireplace("werkman","works",$cat);

	       $result->item[$i] = new StdClass();
	       $result->item[$i]->category = trim(str_replace("\s\s+"," ",strip_tags($cat)));
	       $result->item[$i]->location = trim(str_replace("\s\s+"," ",strip_tags($match[2])));
	       $result->item[$i]->message =trim(str_replace("\s\s+"," ",strip_tags($match[3])));
	       $result->item[$i]->time = Time($this->parseTime(trim(str_replace("\s\s+"," ",strip_tags($match[4])))));
	       $i++;
	  }
	  return $result;
     }
 
     /**
      * Parses the time according to Het Verkeerscentrum
      */
     private function parseTime($str){
	  preg_match("/([0-2][0-9]):([0-5][0-9])( (\d\d)-(\d\d)-(\d\d))?/",$str,$match);
	  $h = $match[1];
	  $i = $match[2];
	  
	  $d = date("d");
	  $m = date("m");
	  $y = date("y");
	  if(isset($match[3])){
	       $d = $match[4];
	       $m = $match[5];
	       $y = $match[6];
	  }
	  $y = "20".$y;
	  return mktime($h,$i,0,$m,$d,$y);
     }
     

     public static function getAllowedPrintMethods(){
	  return array("json","xml", "jsonp", "php", "html");
     }

     public static function getDoc(){
	  return "This is a function which will return all the latest news";
     }

}

?>