<?php

/**
 * This class respresents the semantic sitemap for making the RDF crawlable.
 *
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Semanticsitemap extends AResource {

    private $urlset;

    public static function getParameters() {
        
    }

    public static function getRequiredParameters() {
        
    }

    public function call() {
        $this->getData();
        return $this->urlset;
    }

    public static function getAllowedPrintMethods() {
        return array("xml");
    }

    private function getData() {
        $doc = ResourcesModel::getInstance()->getAllDoc();
        
        $this->urlset = "";
        foreach ($doc as $package => $resources) {
            $this->urlset .= "<sc:dataset>";
            $this->urlset .="<sc:datasetLabel>" . $package . "</sc:datasetLabel>";
            $this->urlset .="<sc:datasetURI>" . Config::$HOSTNAME . Config::$SUBDIR . $package . "</sc:datasetURI>";
            $this->urlset .="<sc:linkedDataPrefix slicing=''>" . Config::$HOSTNAME . Config::$SUBDIR . $package . "</sc:linkedDataPrefix>";
            foreach ($resources as $resource => $val) {
                if (count(ResourcesModel::getInstance()->getResourceRequiredParameters($package, $resource)) == 0) {
                    $$this->urlset .='<sc:dataDumpLocation>' . Config::$HOSTNAME . Config::$SUBDIR . $package . $resource . '.rdf_xml' . '</sc:dataDumpLocation>';
                }
            }
            //$this->urlset .="<lastmod></lastmod>";
            //$this->urlset .="<sc:sparqlEndpointLocation>".Config::$HOSTNAME.Config::$SUBDIR."TDTInfo/sparql</sc:sparqlEndpointLocation>";
            $this->urlset .="<changefreq>monthly</changefreq>";
            $this->urlset .= "</sc:dataset>";
        }
    }

    public static function getDoc() {
        return "This is the Semantic Sitemap";
    }

}

?>
