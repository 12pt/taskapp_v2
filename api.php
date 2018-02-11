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

$cont->get("/api", function($arr) {
    global $db;
    if(isset($db)) {
        return $db->getAll();
    } else {
        return json_encode(array("error" => "unable to connect to database."));
    }
});

$cont->post("/api", function($arr) {
    $title;
    $content;
    
    if($arr["_params"]["title"]) {
        $title = $arr["_params"]["title"];
    }
    if($arr["_params"]["content"]) {
        $content = $arr["_params"]["title"];
    }

    return "making new task: \"$title\" -- \"$content\"";
});

$cont->put("/api/{id}", function($arr) {
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

    return "updated task " . $arr["id"] . " with title $title and content $content";
});

$cont->delete("/api/{id}", function($arr) {
    return "deleting post $id";
});

?>

