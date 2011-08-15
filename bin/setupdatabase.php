<?php
include_once('MDB2.php');
include_once('../Config.class.php');

function createTables() {
    $conn = MDB2::connect(Config::$DSN, Config::$DB_OPTIONS);
    if(PEAR::isError($conn)) {
        die("=== Error while connecting : " . $conn->getMessage() . ' ===');
    }

    $r = $conn->loadModule('Manager'); //, null, false);

    $table_options = array(
        'comment' => 'Repository of people',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
        'type'    => 'innodb',
    );

    $msg_fields = array(
        'id' => array(
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => true,
        ),
        'url_request' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'msg' => array(
            'type' => 'clob',
            'notnull' => true,
        )
    );

    $request_fields = array(
        'id' => array(
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => true,
        ),
        'time' => array(
            'type' => 'timestamp',
            'notnull' => true,
            'notnull' => true,
        ),
        'user_agent' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'ip' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => true,
        ),
        'url_request' => array(
            'type' => 'text',
            'length' => 512,
            'notnull' => true,
        ),
	'module' => array(
	    'type' => 'text',
            'length' => 64,
            'notnull' => true,
	),
	'resource' => array(
	    'type' => 'text',
            'length' => 64,
            'notnull' => true,
	),
	'format' => array(
	    'type' => 'text',
            'length' => 64,
            'notnull' => true,
	),
	'subresources' => array(
	    'type' => 'text',
            'length' => 128,
            'notnull' => false,
	),
	'reqparameters' => array(
	    'type' => 'text',
            'length' => 128,
            'notnull' => false,
	),
	'allparameters' => array(
	    'type' => 'text',
            'length' => 164,
            'notnull' => false,
	)
	
    );

    $error_fields = array(
        'id' => array(
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => true,
        ),
        'time' => array(
            'type' => 'timestamp',
            'notnull' => true,
            'notnull' => true,
        ),
        'user_agent' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'ip' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => true,
        ),
        'url_request' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'error_message' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'error_code' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        )
    );

    $generic_resource__param_fields = array(
        'id' => array(
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => true,
        ),
        'module' => array(
            'type' => 'text',
            'notnull' => true,
            'notnull' => true,
        ),
        'resource' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'type' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => true,
        ),
        'req_params' => array(
            'type' => 'text',
            'length' => 512,
            'notnull' => true,
        ),
	'non_req_params' => array(
	    'type' => 'text',
            'length' => 512,
            'notnull' => false,
	),
	'resource_doc' => array(
            'type' => 'text',
            'length' => 512,
            'notnull' => true,
        ),
	'print_methods' => array(
            'type' => 'text',
            'length' => 60,
            'notnull' => true,
        ),
	'call_params' => array(
            'type' => 'text',
            'length' => 512,
            'notnull' => true,
        )
    );

    $generic_resource_doc_fields = array(
        'id' => array(
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => true,
        ),
        'module' => array(
            'type' => 'text',
            'notnull' => true,
            'notnull' => true,
        ),
        'resource' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ),
        'param' => array(
            'type' => 'text',
            'length' => 40,
            'notnull' => true,
        ),
        'doc' => array(
            'type' => 'text',
            'length' => 512,
            'notnull' => true,
        ),
        'is_param_req' => array(
            'type' => 'text',
            'length' => 2,
            'notnull' => true,
        )
    );



    $primary_contrain = array (
        'primary' => true,
        'fields' => array (
            'id' => array()
        )
    );

    

    echo $conn->createTable('requests', $request_fields, $table_options);
    echo $conn->createTable('errors', $error_fields, $table_options);
    echo $conn->createTable('feedback_messages', $msg_fields, $table_options);
    echo $conn->createTable('generic_resource_param',$generic_resource__param_fields,$table_options);
    echo $conn->createTable('generic_resource_doc',$generic_resource_doc_fields,$table_options);

    echo $conn->createConstraint('requests', 'id', $primary_contrain);
    echo $conn->createConstraint('errors', 'id', $primary_contrain);
    echo $conn->createConstraint('feedback_messages', 'id', $primary_contrain);
    echo $conn->createConstraint('generic_resource_param', 'id', $primary_contrain);
    echo $conn->createConstraint('generic_resource_doc', 'id', $primary_contrain);
    //$conn->createIndex('errors', 'event_timestamp', $definition);

    $conn->disconnect();
}

function main() {
    echo "Create Tables...\n";
    createTables();
    echo "Done.\n";
}

main();
?>

