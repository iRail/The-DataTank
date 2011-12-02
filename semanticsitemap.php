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
header("content-type: application/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:sc='http://sw.deri.org/2007/07/sitemapextension/scschema.xsd'>";
foreach ($doc as $package => $resources) {
    echo "<sc:dataset>";
    echo "<sc:datasetLabel>" . $package . "</sc:datasetLabel>";
    echo "<sc:datasetURI>" . Config::$HOSTNAME . Config::$SUBDIR . $package . "</sc:datasetURI>";
    //echo "\t\t<sc:linkedDataPrefix slicing=''>" . Config::$HOSTNAME . Config::$SUBDIR . $package . "</sc:linkedDataPrefix>\n";
    foreach ($resources as $resource => $val) {
        if ($resource != 'creation_date') {
            if (property_exists($val, 'requiredparameters')) {
                if (count($val->requiredparameters) == 0)
                    echo "<sc:dataDumpLocation>" . Config::$HOSTNAME . Config::$SUBDIR . $package . $resource . ".nt" . "</sc:dataDumpLocation>";
            }
        } else {
            $dt = new DateTime();
            $dt->setTimestamp($val);
            echo "<lastmod>".$dt->format('Y-m-d\TH:i:s') . "</lastmod>";
        }
    }

    echo "<sc:sparqlEndpointLocation></sc:sparqlEndpointLocation>";
    echo "<changefreq>monthly</changefreq>\n";
    echo "</sc:dataset>";
}
echo "</urlset>"
?>
