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

$eventID = $_GET["eventID"];
$eventtype = $mysqli->query("SELECT events.EventID, eventresults.EventID, events.EventType FROM events 
INNER JOIN eventresults ON events.EventID = eventresults.EventID
WHERE events.EventID = '$eventID';")->fetch_assoc()["EventType"];
$eventname = $mysqli->query("SELECT events.EventID, eventresults.EventID, events.EventName FROM events 
INNER JOIN eventresults ON events.EventID = eventresults.EventID
WHERE events.EventID = '$eventID';")->fetch_assoc()["EventName"];
$agecategory = $mysqli->query("SELECT events.EventID, eventresults.EventID, events.AgeCategory FROM events 
INNER JOIN eventresults ON events.EventID = eventresults.EventID
WHERE events.EventID = '$eventID';")->fetch_assoc()["AgeCategory"];

function fillArray($queryResult)
{
    return $queryResult->fetch_assoc();
}

$macQuery = $mysqli->query("SELECT * FROM eventresults WHERE EventID = '$eventID' AND House = 'Mackintosh' ORDER BY CompetitorNumber;");
$lanQuery = $mysqli->query("SELECT * FROM eventresults WHERE EventID = '$eventID' AND House = 'Langhorne' ORDER BY CompetitorNumber;");
$triQuery = $mysqli->query("SELECT * FROM eventresults WHERE EventID = '$eventID' AND House = 'Tristram' ORDER BY CompetitorNumber;");
$greQuery = $mysqli->query("SELECT * FROM eventresults WHERE EventID = '$eventID' AND House = 'Greenlees' ORDER BY CompetitorNumber;");
list($macA, $macB) = array(fillArray($macQuery), fillArray($macQuery));
list($lanA, $lanB) = array(fillArray($lanQuery), fillArray($lanQuery));
list($triA, $triB) = array(fillArray($triQuery), fillArray($triQuery));
list($greA, $greB) = array(fillArray($greQuery), fillArray($greQuery));
$results = array($macA, $macB, $lanA, $lanB, $triA, $triB, $greA, $greB);
?>

<head>
    <title>Event Review</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="https://www.loretto.com/favicon.ico">
</head>

<div class="container pt-4">
    <div class="row">
        <div class="col-sm-9">
            <p class="h3"><?= $agecategory, " ", $eventname?> Results</p>
            <table class="table">
                <thead class="table-light">
                <tr>
                    <th>House</th>
                    <th>Name</th>
                    <?php if ($eventtype == "Track" or $eventtype == "High Jump") {
                        echo "<th>Place</th>";
                    }?>
                    <th>Points</th>
                    <th>Record?</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // output data of each row
                foreach ($results as $row) {
                    if (!is_null($row)) { ?>
                        <tr <?php if ($row["IsRecord"] == true) {
                            echo "class='table-warning'";
                        } ?> >
                            <td>
                                <?= $row["House"] ?>
                            </td>
                            <td>
                                <?= $row["CompetitorName"] ?>
                            </td>
                        <?php if ($eventtype == "Track" or $eventtype == "High Jump") {
                            echo "<td>";
                            echo $row['Place'];
                            echo "</td> ";
                        }?>
                            <td>
                                <?= $row["Points"] ?>
                            </td>
                            <td>
                                <?php if ($row["IsRecord"] == true) {
                                    echo "Yes";
                                } ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <a type="button" href="/admin/input.php" class="btn btn-info">Back to Scoring</a>
        </div>
    </div>
</div>
