<?php

$app = Slim::getInstance('TheDataTank');

/***** Api Middleware *****/

/**
 * This function determines the content-type of the response object by
 * first looking for the Accept header and subsequently the .format
 * section in the uri.
 */
$contentNegotiation = function($default='json') use ($app) {
    return function() use ($app, $default) {
        $contentTypes = array(
            'json' => 'application/json',
            'jsonp' => 'application/jsonp',
            'html' => 'text/html',
            'xml' => 'text/xml',
            'kml' => 'text/xml'
        );
        if ($app->request()->headers('Accept') !== '*/*') {
            $type = $app->request()->headers('Accept');
        } else {
            // Get .format out of resource uri.
            $a = explode('.', $app->request()->getResourceUri());
            if (empty($a)) {
                $type = $contentTypes[$default];
            } else {
                $type = $contentTypes[end($a)];
            }
        }
        $app->response()->header('Content-Type', $type);
    };
};

/**
 * This function will check if the user is allowed to perform the
 * role. If not a http Unauthorized 401 will be returned.
 * TODO Implement.
 */
$authenticateForRole = function ($role='member') {
    return function() use ($role) {
        //...
    };
};

/***** Api *****/

/**** Module ****/

// GET module: display all modules
$app->get('/api/module(.:format)', $contentNegotiation('json'),
        function($format='json') use ($app) {
    $modules = R::find('module');
    $json = Serializer::serialize($modules);
    $app->response()->write($json);
});

// POST module: create new module
$app->post('/api/module(.:format)', function($format='json') use ($app) {
    $module = R::dispense('module');
    $module->name = $app->request()->post('name');
    try {
        R::store($module);
    } catch (Exception $ex) {
        $app->halt(400, $ex->getMessage());
    }
    $app->response()->status(201);
    $app->response()->header('Location', $module->getUrl());
});

// PUT module: chnage name of module
$app->put('/api/module/:name(.:format', function($name, $format='json') use ($app) {
    $module = R::load('module', $name);
    $module->name = $app->request()->post('name');
    try {
        R::store($module);
    } catch (Exception $ex) {
        $app->halt(400, $ex->getMessage());
    }
    $app->response()->status(200);
    $app->response()->header('Location', $module->getUrl());
});

//DELETE module
$app->delete('/api/module/:name(.:format)', function($name, $format='json') use ($app) {
    try {
        R::trash('module', $name);
    } catch (Exception $ex) {
        $app->halt(400, $ex->getMessage());
    }
    $app->response()->status(204);
});

/**** Resource ****/

// TODO everything

$app->get('/api/resource(.:format)', function($format='json') use ($app) {
    //...
});

$app->post('/api/resource(.:format)', function($format='json') use ($app) {
    //...
});

$app->put('/api/resource/:name(.:format', function($name, $format='json') use ($app) {
    //...
});

$app->delete('/api/resource/:name(.:format)', function($name, $format='json') use ($app) {
    //...
});

?>
