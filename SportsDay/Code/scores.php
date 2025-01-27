<!doctype html>
<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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

function fillArray($queryResult)
{
    $res = $queryResult->fetch_assoc();
    if ($queryResult->num_rows == 0 or is_null($res)) {
        $queryResult = array("Points" => 0);
        return $queryResult;
    } else {
        return $res;
    }
}

function getResults($eventid, $house, $competitorNumber, $mysqli)
{
    $result = $mysqli->query("SELECT eventresults.EventID, eventresults.ResultID, eventresults.CompetitorName, eventresults.CompetitorNumber, eventresults.House, eventresults.Points, eventresults.Place, eventresults.IsRecord, performances.ResultID, performances.Performance, performances.IsFail
                        FROM eventresults
                        INNER JOIN performances ON performances.ResultID = eventresults.ResultID
                        WHERE EventID = $eventid AND House = '$house' AND CompetitorNumber = $competitorNumber AND IsFail = ''
                        ORDER BY CAST(performances.Performance AS float) DESC;");
                        
    return $result;
}

// thoughts
// main row for each event title
// indented for each competitor beneath
$result = $mysqli->query("SELECT * FROM events;");

?>

<head>
    <title>Scores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="icon" type="image/x-icon" href="https://www.loretto.com/favicon.ico">
</head>

<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand mb-0 h1" href="/">Sports Day 2024</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="/scores.php">Scores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/gallery/g.php">Photos</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container pt-3">

    <div class="row">
        <div class="ps-1">
            <table class="table table-sm table-borderless mb-4">
                <tr class="table-warning">
                    <td>Possible new records highlighted in yellow!</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <table class="table">
            <thead>
                <tr>
                    <h6 class="col">House</h6>
                    <h6 class="col">Name</h6>
                    <h6 class="col">Places</h6>
                    <h6 class="col">Points</h6>
                    <h6 class="col">Time / Distance</h6>
                </tr>
            </thead>
            <tr>
                <td class="p-0"></td>
            </tr>
            <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {?>
                
                <div class="row">
                    <?php
                    $eventID = $row["EventID"];
                    $eventtype = $row["EventType"];
                    $eventname = $row["EventName"];
                    $agecategory = $row["AgeCategory"];
                    ?>
                    <tr class="table-secondary">
                        <td><?= $agecategory, " ", $eventname ?></td>
                    </tr>
                    <?php
                    
                    $macA = fillarray(getResults($eventID, "Mackintosh", 1, $mysqli));
                    
                    $macB = fillarray(getResults($eventID, "Mackintosh", 2, $mysqli));
                    $lanA = fillarray(getResults($eventID, "Langhorne", 1, $mysqli));
                    $lanB = fillarray(getResults($eventID, "Langhorne", 2, $mysqli));
                    $triA = fillarray(getResults($eventID, "Tristram", 1, $mysqli));
                    $triB = fillarray(getResults($eventID, "Tristram", 2, $mysqli));
                    $greA = fillarray(getResults($eventID, "Greenlees", 1, $mysqli));
                    $greB = fillarray(getResults($eventID, "Greenlees", 2, $mysqli));

                    $results = array($macA, $macB, $lanA, $lanB, $triA, $triB, $greA, $greB);

                    $places = array_column($results, 'Points');
                    
                    array_multisort($places, SORT_DESC, $results);



                    ?>
                    <tr>
                        <td class="pt-0">
                            <table class="table table-sm table-borderless">
                                <?php
                                foreach ($results as $rows) {
                                    if ($rows["Points"] != 0) { ?>
                                        <tr <?php if ($rows["IsRecord"] == true) {
                                            echo "class='table-warning'";
                                        } ?> >
                                            <td class="col">
                                                <?= $rows["House"] ?>
                                            </td>
                                            <td class="col">
                                                <?= $rows["CompetitorName"] ?>
                                            </td>
                                            <td class="col">
                                                <?= $rows["Place"] ?>
                                            </td>
                                            <td class="col">
                                                <?= $rows["Points"] ?>
                                            </td>
                                            <td class="col ps-4">
                                                <?php if ($rows["Performance"] != ".") {
                                                    if ($eventtype == "Track") {
                                                        if ($eventID < 25 or $eventID > 36) {
                                                            echo $rows["Performance"], "s ";
                                                        } else {
                                                            $pieces = explode(".", $rows["Performance"]);
                                                            echo $pieces[0], "m ", $pieces[1], "s";
                                                        }
                                                    } else {
                                                        $pieces = explode(".", $rows["Performance"]);
                                                        echo $pieces[0], "m ", $pieces[1], "cm";
                                                    }
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } ?>
                            </table>
                        </td>
                    </tr>
                </div>
                <?php
            }
            ?>


            </tbody>
        </table>

    </div>
</div>
</body>
<?php


?>