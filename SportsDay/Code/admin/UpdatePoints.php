<?php

$db_host = 'localhost';
$db_user = '';
$db_password = '';
$db_db = '';


$mysqli = @new mysqli(
    $db_host,
    $db_user,
    $db_password,
    $db_db
);

if ($mysqli->connect_error) {
    echo 'Errno: ' . $mysqli->connect_errno;
    echo '<br>';
    echo 'Error: ' . $mysqli->connect_error;
    exit();
}
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function enterPoints($eventID, $resultID, $points, $mysqli)
{
    $mysqli->query("UPDATE eventresults SET Points = '$points' WHERE EventID = '$eventID' AND ResultID = '$resultID'");
}

$agecategory = test_input($_POST["AgeCategory"]);
// Handles differently names eventnames and queries the db for event ID
if (array_key_exists("EventName", $_POST)) {
    $eventname = test_input($_POST["EventName"]);
    $eventID = $mysqli->query("SELECT EventID FROM events WHERE EventName = '$eventname' AND AgeCategory = '$agecategory'")->fetch_assoc()["EventID"];
} else {
    $eventname = test_input($_POST["Events"]);
    $eventID = $mysqli->query("SELECT EventID FROM events WHERE EventName = '$eventname' AND AgeCategory = '$agecategory'")->fetch_assoc()["EventID"];
}

$houses = array("Mackintosh", "Mackintosh", "Langhorne", "Langhorne", "Tristram", "Tristram", "Greenlees", "Greenlees");
// fill variables for track
$competitorNums = array(1, 2, 1, 2, 1, 2, 1, 2);


$points = array(test_input($_POST["macpointsA"]), test_input($_POST["macpointsB"]), test_input($_POST["lanpointsA"]), test_input($_POST["lanpointsB"]), test_input($_POST["tripointsA"]), test_input($_POST["tripointsB"]), test_input($_POST["grepointsA"]), test_input($_POST["grepointsB"]));

for ($count = 0; $count <= 7; $count++) {
    $resultID = $mysqli->query("SELECT ResultID FROM eventresults WHERE House = '$houses[$count]' AND CompetitorNumber = '$competitorNums[$count]' AND eventID = '$eventID'")->fetch_assoc()["ResultID"];
    enterPoints($eventID, $resultID, $points[$count], $mysqli);
}

header("Location: ./points.php");

//header("Location: http://www.loretto.tech/admin/points.php");
//exit();
?>