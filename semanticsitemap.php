<?php

/**
 * Description of semanticsitemap
 *
 * @author Miel Vander Sande
 */
include_once 'model/ResourcesModel.class.php';
include_once 'aspects/caching/Cache.class.php';
include_once 'Config.class.php';
include_once 'includes/rb.php';

R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

$doc = ResourcesModel::getInstance()->getAllDoc();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:sc='http://sw.deri.org/2007/07/sitemapextension/scschema.xsd'>\n";
foreach ($doc as $package => $resources) {
    echo "\t<sc:dataset>\n";
    echo "\t\t<sc:datasetLabel>" . $package . "</sc:datasetLabel>\n";
    echo "\t\t<sc:datasetURI>" . Config::$HOSTNAME . Config::$SUBDIR . $package . "</sc:datasetURI>\n";
    //echo "\t\t<sc:linkedDataPrefix slicing=''>" . Config::$HOSTNAME . Config::$SUBDIR . $package . "</sc:linkedDataPrefix>\n";
    foreach ($resources as $resource => $val) {
        if (property_exists($val, 'requiredparameters')) {
            if (count($val->requiredparameters) == 0)
                echo "\t\t<sc:dataDumpLocation>" . Config::$HOSTNAME . Config::$SUBDIR . $package . $resource . ".nt" . "</sc:dataDumpLocation>\n";
        }
    }
    echo "\t\t<lastmod></lastmod>\n";
    echo "\t\t<sc:sparqlEndpointLocation></sc:sparqlEndpointLocation>\n";
    echo "\t\t<changefreq>monthly</changefreq>\n";
    echo "\t</sc:dataset>\n";
}
echo "</urlset>"
?>
