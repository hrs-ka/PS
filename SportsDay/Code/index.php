<!doctype html>
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

function getPoints($house, $mysqli)
{
    $total = 0;
    $sql = "SELECT Points, House FROM eventresults WHERE House='$house'";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $total += $row['Points'];
        }
        return array("Points" => $total, "House" => $house);
    } else {
        return array("Points" => 0, "House" => $house);
    }
}

$macpoints = getPoints("Mackintosh", $mysqli);
$lanpoints = getPoints("Langhorne", $mysqli);
$tripoints = getPoints("Tristram", $mysqli);
$grepoints = getPoints("Greenlees", $mysqli);

$points = array($macpoints, $lanpoints, $tripoints, $grepoints);

$places = array_column($points, 'Points');
array_multisort($places, SORT_DESC, $points);

?>

<html>

<head>
    <title>Sports Day 2024</title>
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
                    <a class="nav-link active" aria-current="page" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/scores.php">Scores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/gallery/g.php">Photos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" target="_blank" href="https://www.justgiving.com/page/loretto-fetlor?utm_medium=fundraising&utm_content=page%2Floretto-fetlor&utm_source=copyLink&utm_campaign=pfp-share">Fet-Lor Fundraising</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container pt-2">
    <?php
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );
    $marqueetext = file_get_contents('https://sportsdaymarquee.pages.dev/marquee', false, stream_context_create($arrContextOptions));
    ?>
    <div class="row pb-2">
        <marquee><?= $marqueetext ?></marquee>
    </div>

    <div class="row">

        <div class="col-sm-3">
            <p class="h3">Point Totals</p>
            <table class="table table-borderless"> <?php
                $num = 1;
                foreach ($points as $housepoints) {
                    $currentHouse = $housepoints["House"];
                    if ($currentHouse == "Mackintosh") {
                        $colour = "background-color: midnightblue; color: white;";
                    } elseif ($currentHouse == "Tristram") {
                        $colour = "background-color: pink; color: white;";
                    } elseif ($currentHouse == "Langhorne") {
                        $colour = "background-color: green; color: white;";
                    } elseif ($currentHouse == "Greenlees") {
                        $colour = "background-color: indigo; color: white;";
                    }
                    if ($num == 1) {
                        $place = "1st";
                    } elseif ($num == 2) {
                        $place = "2nd";
                    } elseif ($num == 3) {
                        $place = "3rd";
                    } elseif ($num == 4) {
                        $place = "4th";
                    }
                    ?>

                    <tr class="m-3">
                        <td class="col-1"><?= $place ?></td>
                        <td <?= "style='$colour'" ?> class="rounded-start"><?= $housepoints["House"] ?></td>
                        <td <?= "style='$colour'" ?> class="rounded-end"><?= $housepoints["Points"] ?></td>
                    </tr>
                    <?php
                    $num = $num + 1;
                } ?>

            </table>
        </div>

        <div class="col-sm-9">
            <p class="h3">Latest Results</p>
            <table class="table mb-2">
                <thead class="table-light">
                <tr>
                    <th>Event</th>
                    <th>Winner</th>
                    <th>House</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $latestresults = $mysqli->query(
                    "SELECT eventresults.ResultID, eventresults.EventID, eventresults.CompetitorName, eventresults.House, events.EventName, events.EventType, events.AgeCategory
FROM events
INNER JOIN eventresults 
ON events.EventID = eventresults.EventID
WHERE eventresults.Points = 8
GROUP BY eventresults.ResultID
ORDER BY eventresults.ResultID DESC
LIMIT 12;
"
                );
                if ($latestresults->num_rows > 0) {
                    // output data of each row
                    while ($row = $latestresults->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <?php echo $row["AgeCategory"];
                                echo " ";
                                echo $row["EventName"] ?>                               
                            </td>
                            <td>
                                <?php echo $row["CompetitorName"] ?>
                            </td>
                            <td>
                                <?php echo $row["House"] ?>
                            </td>

                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <div class="col-6">
                <a type="button" class="btn btn-success" href="/scores.php">View Scores</a>
            </div>

        </div>

    </div>
    


</div>
</body>
</html>

