<?php
include_once('Config.class.php');
include_once('rb.php');

// This is how you configure it.
R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

// This is how you create a bean.
$message1 = R::dispense('feedback_messages');
$message1->url_request = 'http://localhost/Test123';
$message1->msg = 'test123';
$id1 = R::store($message1);

$message2 = R::dispense('feedback_messages');
$message2->url_request = 'http://localhost/Test456';
$message2->msg = 'test456';
$id2 = R::store($message2);

echo 'Message 1 id: ' . $id1 . "\n";
echo 'Message 2 id: ' . $id2 . "\n";

// This is how you get an existing bean.
$message3 = R::load('feedback_messages', $id1);
echo 'Message 1 & 3: ' . ($message1->getID() == $message3->getID()) . "\n";

// This is how you update a bean.
$message2->url_request = 'http://localhost/Test678';
$id2 = R::store($message2);

// This is how you find a bean, no sql-injection possible.
$message4 = R::findOne(
    'feedback_messages',
    'url_request = :url_request',
    array(':url_request' => 'http://localhost/Test678')
);
echo 'Message 2 & 4: ' . ($message2->getID() == $message4->getID()) . "\n";

// This is how you delete a bean.
R::trash($message1);
R::trash($message2);

?>
