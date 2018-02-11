<?php declare(strict_types=1);
require __DIR__ . "/vendor/autoload.php";
require_once(__DIR__ . "/database.php");

use Pirouette\Controller;

#-------------------------------------------------------------------------------

global $db;
$dbinfo = include(__DIR__ . '/config.php');
$db = new Database($dbinfo["host"], $dbinfo["dbname"], $dbinfo["username"], $dbinfo["password"]);

#-------------------------------------------------------------------------------

$cont = new Controller();

$cont->get("/api.php", function($arr) {
    global $db;
    if(isset($db)) {
        return $db->getAll();
    } else {
        return json_encode(array("error" => "unable to connect to database."));
    }
});

$cont->post("/api.php", function($arr) {
    $title;
    $content;
    
    if($arr["_params"]["title"]) {
        $title = $arr["_params"]["title"];
    }
    if($arr["_params"]["content"]) {
        $content = $arr["_params"]["title"];
    }

    if(isset($title) && isset($content)) {
        global $db;
        return $db->add($title, $content);
    }
    header("HTTP/1.1 400 Bad Request");
});

$cont->put("/api.php/{id}", function($arr) {
    $title;
    $content;
    
    if($arr["_params"]["title"]) {
        # set the task title to the given arg.
        $title = $arr["_params"]["title"];
    }
    if($arr["_params"]["content"]) {
        # set the task contents to the given arg.
        $content = $arr["_params"]["content"];
    }

    if(isset($title) && isset($content)) {
        global $db;
        return $db->update($arr["id"], $title, $content);
    }
    header("HTTP/1.1 400 Bad Request");
});

$cont->delete("/api.php/{id}", function($arr) {
    if($arr["id"] > 0) {
        global $db;
        return $db->delete($arr["id"]);
    }
    header("HTTP/1.1 400 Bad Request");
});

?>

