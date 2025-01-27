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
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


function enterScoresTrack($eventID, $name, $house, $place, $resultID, $performance, $isfail, $competitorNum, $mysqli)
{

    if ($name != "") {
        $alreadyExists = $mysqli->query("SELECT eventresults.CompetitorName, performances.AttemptNumber, performances.ResultID, eventresults.EventID, events.EventName, events.EventID, eventresults.EventID
    FROM events
    INNER JOIN eventresults ON events.EventID = eventresults.EventID
    INNER JOIN performances ON eventresults.ResultID = performances.ResultID
    WHERE events.EventID = '$eventID' and performances.ResultID = '$resultID'");

        if ($alreadyExists->num_rows > 0) {
            $firstInput = False;
        } else {
            $firstInput = True;
        }

        if ($firstInput) {
            $mysqli->query("INSERT INTO eventresults (CompetitorName, EventID, House, CompetitorNumber, Place)
                VALUES ('$name', '$eventID', '$house', '$competitorNum', '$place')");

            $lastID = $mysqli->insert_id;
            $mysqli->query("INSERT INTO performances (Performance, IsFail, ResultID)
                VALUES ('$performance', '$isfail', '$lastID')");
        } else {
            $mysqli->query("UPDATE eventresults SET CompetitorName = '$name', House = '$house', Place = '$place' WHERE EventID = '$eventID' AND ResultID = '$resultID'");

            $mysqli->query("UPDATE performances SET Performance = '$performance', IsFail = '$isfail' WHERE ResultID = '$resultID'");

        }

    }


}

function enterScoresField($eventID, $name, $house, $competitorNum, $resultID, $performance, $isfail, $attemptNum, $mysqli)
{

    if ($name != "") {

        $existsAlready = $mysqli->query(
            "SELECT eventresults.CompetitorName, performances.AttemptNumber, performances.ResultID, eventresults.EventID, events.EventName, events.EventID, eventresults.EventID
    FROM events
    INNER JOIN eventresults ON events.EventID = eventresults.EventID
    INNER JOIN performances ON eventresults.ResultID = performances.ResultID
    WHERE events.EventID = '$eventID' and performances.ResultID = '$resultID' and performances.AttemptNumber = '$attemptNum';");

        if ($existsAlready->num_rows > 0) {
            $firstInput = False;
        } else {
            $firstInput = True;
        }

        if (is_null($resultID)) {
            $mysqli->query("INSERT INTO eventresults (CompetitorName, EventID, House, CompetitorNumber)
            VALUES ('$name', '$eventID', '$house', '$competitorNum')");
        } else {
            $mysqli->query("UPDATE eventresults SET CompetitorName = '$name' WHERE EventID = '$eventID' AND ResultID = '$resultID'");
        }

        $lastID = $mysqli->insert_id;
        if ($lastID == 0) {
            $lastID = $resultID;
        }

        // If new score input then insert
        if ($firstInput) {
            $mysqli->query("INSERT INTO performances (Performance, IsFail, AttemptNumber, ResultID)
                VALUES ('$performance', '$isfail', '$attemptNum', '$lastID')");
        } else {
            $mysqli->query("UPDATE performances SET Performance = '$performance', IsFail = '$isfail' WHERE ResultID = '$resultID' AND AttemptNumber = '$attemptNum'");
        }
    }
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
$alphabeticalHouses = array("Tristram", "Tristram", "Mackintosh", "Mackintosh", "Langhorne", "Langhorne", "Greenlees", "Greenlees");
$names = array(test_input($_POST["macnameA"]), test_input($_POST["macnameB"]), test_input($_POST["lannameA"]), test_input($_POST["lannameB"]), test_input($_POST["trinameA"]), test_input($_POST["trinameB"]), test_input($_POST["grenameA"]), test_input($_POST["grenameB"]));
$eventtype = test_input($_POST["EventType"]);

if (!array_key_exists("EventFinished", $_POST)) {

    // fill variables for track

    $invalids = array(test_input($_POST["macAinvalid"]), test_input($_POST["macBinvalid"]), test_input($_POST["lanAinvalid"]), test_input($_POST["lanBinvalid"]), test_input($_POST["triAinvalid"]), test_input($_POST["triBinvalid"]), test_input($_POST["greAinvalid"]), test_input($_POST["greBinvalid"]));

    $competitorNums = array(1, 2, 1, 2, 1, 2, 1, 2);
    


    if ($eventtype == "Track") {

        $minutes = array(test_input($_POST["macminsA"]), test_input($_POST["macminsB"]), test_input($_POST["lanminsA"]), test_input($_POST["lanminsB"]), test_input($_POST["triminsA"]), test_input($_POST["triminsB"]), test_input($_POST["greminsA"]), test_input($_POST["greminsB"]));
        $seconds = array(test_input($_POST["macsecsA"]), test_input($_POST["macsecsB"]), test_input($_POST["lansecsA"]), test_input($_POST["lansecsB"]), test_input($_POST["trisecsA"]), test_input($_POST["trisecsB"]), test_input($_POST["gresecsA"]), test_input($_POST["gresecsB"]));
        $places = array(test_input($_POST["macplaceA"]), test_input($_POST["macplaceB"]), test_input($_POST["lanplaceA"]), test_input($_POST["lanplaceB"]), test_input($_POST["triplaceA"]), test_input($_POST["triplaceB"]), test_input($_POST["greplaceA"]), test_input($_POST["greplaceB"]));

    
    } elseif ($eventtype == "High Jump") {

        $metres = array(test_input($_POST["macmetresA"]), test_input($_POST["macmetresB"]), test_input($_POST["lanmetresA"]), test_input($_POST["lanmetresB"]), test_input($_POST["trimetresA"]), test_input($_POST["trimetresB"]), test_input($_POST["gremetresA"]), test_input($_POST["gremetresB"]));
        $centimetres = array(test_input($_POST["maccentimetresA"]), test_input($_POST["maccentimetresB"]), test_input($_POST["lancentimetresA"]), test_input($_POST["lancentimetresB"]), test_input($_POST["tricentimetresA"]), test_input($_POST["tricentimetresB"]), test_input($_POST["grecentimetresA"]), test_input($_POST["grecentimetresB"]));
        $places = array(test_input($_POST["macplaceA"]), test_input($_POST["macplaceB"]), test_input($_POST["lanplaceA"]), test_input($_POST["lanplaceB"]), test_input($_POST["triplaceA"]), test_input($_POST["triplaceB"]), test_input($_POST["greplaceA"]), test_input($_POST["greplaceB"]));      
    

    } elseif ($eventtype == "Field") {

        $metres = array(test_input($_POST["macmetresA"]), test_input($_POST["macmetresB"]), test_input($_POST["lanmetresA"]), test_input($_POST["lanmetresB"]), test_input($_POST["trimetresA"]), test_input($_POST["trimetresB"]), test_input($_POST["gremetresA"]), test_input($_POST["gremetresB"]));
        $centimetres = array(test_input($_POST["maccentimetresA"]), test_input($_POST["maccentimetresB"]), test_input($_POST["lancentimetresA"]), test_input($_POST["lancentimetresB"]), test_input($_POST["tricentimetresA"]), test_input($_POST["tricentimetresB"]), test_input($_POST["grecentimetresA"]), test_input($_POST["grecentimetresB"]));

        $attemptNum = test_input($_POST["AttemptNum"]);

    }

    if ($eventtype == "Track") {
        $macperformanceA = $minutes[0] . "." . $seconds[0];
        $macperformanceB = $minutes[1] . "." . $seconds[1];
        $lanperformanceA = $minutes[2] . "." . $seconds[2];
        $lanperformanceB = $minutes[3] . "." . $seconds[3];
        $triperformanceA = $minutes[4] . "." . $seconds[4];
        $triperformanceB = $minutes[5] . "." . $seconds[5];
        $greperformanceA = $minutes[6] . "." . $seconds[6];
        $greperformanceB = $minutes[7] . "." . $seconds[7];
    } else {
        $macperformanceA = $metres[0] . "." . $centimetres[0];
        $macperformanceB = $metres[1] . "." . $centimetres[1];
        $lanperformanceA = $metres[2] . "." . $centimetres[2];
        $lanperformanceB = $metres[3] . "." . $centimetres[3];
        $triperformanceA = $metres[4] . "." . $centimetres[4];
        $triperformanceB = $metres[5] . "." . $centimetres[5];
        $greperformanceA = $metres[6] . "." . $centimetres[6];
        $greperformanceB = $metres[7] . "." . $centimetres[7];
    }

    $performances = array($macperformanceA, $macperformanceB, $lanperformanceA, $lanperformanceB, $triperformanceA, $triperformanceB, $greperformanceA, $greperformanceB);


    if ($eventtype == "Track" or $eventtype == "High Jump") {
        for ($count = 0; $count <= 7; $count++) {
            $resultID = $mysqli->query("SELECT ResultID FROM eventresults WHERE House = '$houses[$count]' AND CompetitorNumber = '$competitorNums[$count]' AND eventID = '$eventID'")->fetch_assoc()["ResultID"];
            enterScoresTrack($eventID, $names[$count], $houses[$count], $places[$count], $resultID, $performances[$count], $invalids[$count], $competitorNums[$count], $mysqli);
        }
    } elseif ($eventtype == "Field") {
        for ($count = 0; $count <= 7; $count++) {
            $resultID = $mysqli->query("SELECT ResultID FROM eventresults WHERE House = '$houses[$count]' AND CompetitorNumber = '$competitorNums[$count]' AND eventID = '$eventID'")->fetch_assoc()["ResultID"];
            enterScoresField($eventID, $names[$count], $houses[$count], $competitorNums[$count], $resultID, $performances[$count], $invalids[$count], $attemptNum, $mysqli);
        }
    }

    header("Location: /admin/input.php?EventName=$eventname&AgeCategory=$agecategory&EventType=$eventtype");

} else {

    function pointsField($eventID, $results1, $results2, $results3, $houses, $mysqli)
    {
        $names = array();
        $IDs = array();
        $bestresult = array(0, 0, 0, 0, 0, 0, 0, 0);
        for ($count = 0; $count <= 7; $count++) {
            $attempt1row = $results1->fetch_assoc();
            $attempt2row = $results2->fetch_assoc();
            $attempt3row = $results3->fetch_assoc();

            // Pointer increments by 1 each iteration to fetch new row
            list($attempt1perf, $attempt1invalid, $name, $resultID) = array($attempt1row["Performance"], $attempt1row["IsFail"], $attempt1row["CompetitorName"], $attempt1row["ResultID"]);
            list($attempt2perf, $attempt2invalid) = array($attempt2row["Performance"], $attempt2row["IsFail"]);
            list($attempt3perf, $attempt3invalid) = array($attempt3row["Performance"], $attempt3row["IsFail"]);

            array_push($names, $name);
            array_push($IDs, $resultID);


            if (!$attempt1invalid) {
                if ($bestresult[$count] < $attempt1perf) {
                    $bestresult[$count] = $attempt1perf;
                }
            }
            if (!$attempt2invalid) {
                if ($bestresult[$count] < $attempt2perf) {
                    $bestresult[$count] = $attempt2perf;
                }
            }
            if (!$attempt3invalid) {
                if ($bestresult[$count] < $attempt3perf) {
                    $bestresult[$count] = $attempt3perf;
                }
            }


        }

        $result = array();
        for ($count = 0; $count <= 7; $count++) {
            $currentResult = array("Score" => $bestresult[$count], "House" => $houses[$count], "ResultID" => $IDs[$count]);
            array_push($result, $currentResult);

        }

        $score = array_column($result, 'Score');
        array_multisort($score, SORT_DESC, $result);
        

        // update db with places
        for ($count = 0; $count <= 7; $count++) {
            $resID = ($result[$count])["ResultID"];
            $place = $count + 1;
            $mysqli->query("UPDATE eventresults SET Place = '$place' WHERE ResultID = '$resID'");
        }
        

        //copy logic from updatePoints - would be better to refactor this to a separate function
        $macQuery = $mysqli->query("SELECT Place, ResultID FROM eventresults WHERE House = 'Mackintosh' AND eventID = '$eventID' ORDER BY eventresults.Place;");
        $lanQuery = $mysqli->query("SELECT Place, ResultID FROM eventresults WHERE House = 'Langhorne' AND eventID = '$eventID' ORDER BY eventresults.Place;");
        $triQuery = $mysqli->query("SELECT Place, ResultID FROM eventresults WHERE House = 'Tristram' AND eventID = '$eventID' ORDER BY eventresults.Place;");
        $greQuery = $mysqli->query("SELECT Place, ResultID FROM eventresults WHERE House = 'Greenlees' AND eventID = '$eventID' ORDER BY eventresults.Place;");
        list($macA, $macB) = array(fillArray($macQuery), fillArray($macQuery));
        list($lanA, $lanB) = array(fillArray($lanQuery), fillArray($lanQuery));
        list($triA, $triB) = array(fillArray($triQuery), fillArray($triQuery));
        list($greA, $greB) = array(fillArray($greQuery), fillArray($greQuery));
        $Arace = array($macA, $lanA, $triA, $greA);
        $Brace = array($macB, $lanB, $triB, $greB);
        


        $places = array_column($Arace, 'Place');
        array_multisort($places, SORT_ASC, $Arace);

        $places = array_column($Brace, 'Place');
        array_multisort($places, SORT_ASC, $Brace);
        


        for ($count = 0; $count <= 3; $count++) {
            $resID = ($Arace[$count])["ResultID"];
            $points = 8 - $count;
            $mysqli->query("UPDATE eventresults SET Points = '$points' WHERE ResultID = '$resID'");
        }

        for ($count = 0; $count <= 3; $count++) {
            $resID = ($Brace[$count])["ResultID"];
            $points = 4 - $count;
            $mysqli->query("UPDATE eventresults SET Points = '$points' WHERE ResultID = '$resID'");
        }

        

    }

    function fillArray($queryResult)
    {
        $res = $queryResult->fetch_assoc();
        if ($queryResult->num_rows == 0 or is_null($res)) {
            $queryResult = array("Place" => 9);
            return $queryResult;
        } else {
            return $res;
        }
    }

    function pointsTrack($place, $eventID, $houses, $competitorNums, $mysqli)
    {


        $macQuery = $mysqli->query("SELECT Place, ResultID FROM eventresults WHERE House = 'Mackintosh' AND eventID = '$eventID' ORDER BY eventresults.Place;");
        $lanQuery = $mysqli->query("SELECT Place, ResultID FROM eventresults WHERE House = 'Langhorne' AND eventID = '$eventID' ORDER BY eventresults.Place;");
        $triQuery = $mysqli->query("SELECT Place, ResultID FROM eventresults WHERE House = 'Tristram' AND eventID = '$eventID' ORDER BY eventresults.Place;");
        $greQuery = $mysqli->query("SELECT Place, ResultID FROM eventresults WHERE House = 'Greenlees' AND eventID = '$eventID' ORDER BY eventresults.Place;");
        list($macA, $macB) = array(fillArray($macQuery), fillArray($macQuery));
        list($lanA, $lanB) = array(fillArray($lanQuery), fillArray($lanQuery));
        list($triA, $triB) = array(fillArray($triQuery), fillArray($triQuery));
        list($greA, $greB) = array(fillArray($greQuery), fillArray($greQuery));
        $Arace = array($macA, $lanA, $triA, $greA);
        $Brace = array($macB, $lanB, $triB, $greB);

        $places = array_column($Arace, 'Place');
        array_multisort($places, SORT_ASC, $Arace);

        $places = array_column($Brace, 'Place');
        array_multisort($places, SORT_ASC, $Brace);

        for ($count = 0; $count <= 3; $count++) {
            $resID = ($Arace[$count])["ResultID"];
            $points = 8 - $count;
            $mysqli->query("UPDATE eventresults SET Points = '$points' WHERE ResultID = '$resID'");
        }

        for ($count = 0; $count <= 3; $count++) {
            $resID = ($Brace[$count])["ResultID"];
            $points = 4 - $count;
            $mysqli->query("UPDATE eventresults SET Points = '$points' WHERE ResultID = '$resID'");
        }


    }

    function CheckRecord($PrevRecord, $performance, $eventtype, $resultID, $mysqli)
    {
        echo "In CheckRecord";

        
        if ($eventtype == "Track") {

            if (floatval($performance) <= floatval($PrevRecord)) {
                echo "<p>Updating isrecord...";
                $mysqli->query("UPDATE eventresults SET IsRecord = True WHERE ResultID = '$resultID'");
                echo("Error description: " . $mysqli -> error);
            } else {
                $mysqli->query("UPDATE eventresults SET IsRecord = False WHERE ResultID = '$resultID'");
            }
            
        } else {

            if (floatval($performance) >= floatval($PrevRecord)) {
                $mysqli->query("UPDATE eventresults SET IsRecord = True WHERE ResultID = '$resultID'");
            } else {
                $mysqli->query("UPDATE eventresults SET IsRecord = False WHERE ResultID = '$resultID'");
            }
        }


    }


    // sort the possible missing values and incorrect order
    function getAttemptResults($resultEventID, $attemptnumber, $funcsql)
    {
        return ($funcsql->query(
            "SELECT events.EventID, eventresults.EventID, eventresults.ResultID, eventresults.CompetitorName, eventresults.CompetitorNumber, eventresults.House, performances.PerformanceID, performances.Performance, performances.AttemptNumber, performances.IsFail
              FROM events
              INNER JOIN eventresults ON events.EventID = eventresults.EventID
              INNER JOIN performances ON eventresults.ResultID = performances.ResultID
              WHERE events.EventID = '$resultEventID' and performances.AttemptNumber = $attemptnumber
              ORDER BY eventresults.House DESC, eventresults.CompetitorNumber;"));
    }

    $resultsAttempt1 = getAttemptResults($eventID, 1, $mysqli);
    $resultsAttempt2 = getAttemptResults($eventID, 2, $mysqli);
    $resultsAttempt3 = getAttemptResults($eventID, 3, $mysqli);
    $competitorNums = array(1, 2, 1, 2, 1, 2, 1, 2);

    $record = $mysqli->query("SELECT PrevRecord FROM events WHERE EventID = '$eventID'")->fetch_assoc()["PrevRecord"];
    


    if ($eventtype == "Track" or $eventtype == "High Jump") {
        $places = array(test_input($_POST["macplaceA"]), test_input($_POST["macplaceB"]), test_input($_POST["lanplaceA"]), test_input($_POST["lanplaceB"]), test_input($_POST["triplaceA"]), test_input($_POST["triplaceB"]), test_input($_POST["greplaceA"]), test_input($_POST["greplaceB"]));
        
        echo "doing Track/HJ points";
        pointsTrack($places, $eventID, $houses, $competitorNums, $mysqli);
        echo "doing Track/HJ records";
        $bestResultID = $mysqli->query("SELECT ResultID FROM eventresults WHERE EventID = '$eventID' AND Points = 8")->fetch_assoc()["ResultID"];
        $bestResult = $mysqli->query("SELECT Performance FROM performances WHERE ResultID = '$bestResultID'")->fetch_assoc()["Performance"];
        CheckRecord($record, $bestResult, $eventtype, $bestResultID, $mysqli);

    } elseif ($eventtype == "Field") {
        
        echo "doing Field points";
        pointsField($eventID, $resultsAttempt1, $resultsAttempt2, $resultsAttempt3, $alphabeticalHouses, $mysqli);
        echo "doing Field record check";
        $bestResultID = $mysqli->query("SELECT ResultID FROM eventresults WHERE EventID = '$eventID' AND Points = 8")->fetch_assoc()["ResultID"];
        $bestResult = $mysqli->query("SELECT Performance FROM performances WHERE ResultID = '$bestResultID' AND NOT IsFail ORDER BY Performance DESC limit 1")->fetch_assoc()["Performance"];
        CheckRecord($record, $bestResult, $eventtype, $bestResultID, $mysqli);
    }

    //exit();
    header("Location: ./eventfinished.php?eventID=$eventID");

}

//$sql = "UPDATE results SET GPoints = $_POST[gre], LPoints = $_POST[lan], MPoints = $_POST[mac], TPoints = $_POST[tri], WHERE Eventname = '$_POST[Events]'";
//$result = $mysqli->query($sql);


//header("Location: http://www.loretto.tech/admin/Input.php");
//exit();
?>