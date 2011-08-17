<?php

// View resource
class AdminResourceHandler {
    public function GET() {
        include_once ("templates/TheDataTank/header.php");
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $resources = '<h2>Resources</h2><ul>';
        foreach (R::find('generic_resource') as $resource) {
            $resources .= '<li><a href="' . Config::$HOSTNAME . Config::$SUBDIR . 
                'admin/resources/' . $resource->id . '/">' .
                htmlentities($resource->resource_name) .
                '</a> - <a href="' . Config::$HOSTNAME . Config::$SUBDIR .
                'admin/resources/' . $resource->id . '/delete/">(del)</a></li>';
        }
        $resources .= '</ul>';
        echo $resources;
        echo '<a href="' . Config::$HOSTNAME . Config::$SUBDIR .
            'admin/resources/add/">+ Add Resource</a>';
        //echo '<a id="add_resource" href="/' . Config::$SUBDIR . '/"#">Add Resource';
        include_once ("templates/TheDataTank/footer.php");
    }
}

// Add resource
class AdminAddResourceHandler {
    public function GET() {
        include_once ("templates/TheDataTank/header.php");
        //TODO add better form.
        $add_form = '
            <h2>Add Resource</h2>
            <form name="Add Resource" method="POST">
                name: <input type="text" name="name" /><br /><br />
                <input type="submit" value="Save" />
            </form>';
        echo $add_form;
        include_once ("templates/TheDataTank/footer.php");
    }

    public function POST() {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $resource = R::dispense('resource');
        $resource->resource_name = $_POST['name'];
        $id = R::store($resource);
        header('Location: ' . Config::$HOSTNAME . Config::$SUBDIR . 'admin/resources/');
    }
}

// View single resource
class AdminViewResourceHandler {
    public function GET($matches) {
        include_once ("templates/TheDataTank/header.php");
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $resource = R::load('resource', $matches['resource_id']);
        if (!$resource) {
            throw new Exception('404: page not found'); //TODO 404
        }
        $resources = '<h2>' . $resource->resource_name . '</h2>';
        //TODO api url, and list resources in resource
        echo '<p>api: ...</p>';
        echo '<p>resources: ...</p>';
        include_once ("templates/TheDataTank/footer.php");
    }
}

// Remove resource
class AdminRemoveResourceHandler {
    public function GET($matches) {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $resource = R::load('resource', $matches['resource_id']);
        R::trash($resource);
        header('Location: ' . Config::$HOSTNAME . Config::$SUBDIR . 'admin/resources/');
    }
}

?>

