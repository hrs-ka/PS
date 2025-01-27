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


$sql = "SELECT * FROM events ORDER BY EventName";
$result = $mysqli->query($sql);

?>

    <html lang="">

    <head>
        <title>Points Entry</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="https://www.loretto.com/favicon.ico">
        <style>


            .inlinetext {
                display: inline-block;
                margin-right: 0.5%;
            }

            th, td {
                padding-left: 1%;
                padding-right: 1%;
                padding-bottom: 0.5%;
                padding-top: 0.5%;
                width: 12rem;
            }


        </style>


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

    <?php
    // Retrieve submitted event type and age category
    // define variables and set to empty values
    $eventtype = $agecategory = $eventname = $attemptno = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" and array_key_exists("Events", $_POST)) {
        $eventname = test_input($_POST["Events"]);
        $agecategory = test_input($_POST["AgeCategory"]);
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
        $agecategory = test_input($_POST["AgeCategory"]);
        $eventtype = test_input($_POST["EventType"]);
    }
    if (array_key_exists("Attempt1", $_POST)) {
        $attemptno = 1;
    } elseif (array_key_exists("Attempt2", $_POST)) {
        $attemptno = 2;
    } elseif (array_key_exists("Attempt3", $_POST)) {
        $attemptno = 3;
    }
    // $eventtype = ( $mysqli->query("SELECT EventType FROM `events` WHERE Name='$eventsubmitted'") -> fetch_assoc() ) ["EventType"];

    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function getPerformance($performanceevent, $performancenumber, $performancehouse, $funcsql)
    {
        $rslt = ($funcsql->query(
            "SELECT events.EventName, events.AgeCategory, events.EventID, eventresults.EventID, eventresults.ResultID, eventresults.CompetitorName, eventresults.CompetitorNumber, eventresults.House, eventresults.Points
                FROM events
                INNER JOIN eventresults ON events.EventID = eventresults.EventID
                INNER JOIN performances ON eventresults.ResultID = performances.ResultID
                WHERE events.EventID = '$performanceevent' and eventresults.House = '$performancehouse' and eventresults.CompetitorNumber = $performancenumber
                ORDER BY performances.Performance DESC;")->fetch_assoc());
        if (is_null($rslt)) {
            $rslt = array("EventName" => "", "eventresults" => "", "CompetitorName" => "", "CompetitorNumber" => "", "House" => "", "Place" => "", "Performance" => ".");
        }
        return $rslt;
    }

    function makePoints($placename, $dropver)
    {
        ?>
        <td>
            <select name="<?php echo $placename ?>" class="form-select">
                <option value="" disabled selected>Points</option>
                <?php for ($x = 2; $x <= 16; $x++) { ?>
                    <option <?php echo ($dropver['Points'] == $x/2) ? 'selected' : ''; ?>>
                        <?php echo $x/2 ?>
                    </option>
                <?php } ?>
            </select>
        </td>
        <?php
    }

    ?>

    <div class="container pt-4">

        <!-- Age selection dropdown and start of first form -->
        <?php if ($eventtype == "" and $eventname == "" and $agecategory == "") {    // if the event has not been set from a submit ?>
            <div class="row">
                <form class="form-control" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <label class="form-label" for="AgeCategory">Category:</label>
                    <select class="form-select" id="AgeCategory" name="AgeCategory">
                        <option>Junior</option>
                        <option>Middle</option>
                        <option>Senior</option>
                    </select>

                    <label class="form-label" for="EventType" style="margin-top: 1%">Type:</label>
                    <select class="form-select" id="EventType" name="EventType">
                        <option>Track</option>
                        <option>Field</option>
                        <option>High Jump</option>
                    </select>
                    <input type="submit" name="submit" value="Submit" class="btn btn-primary" style="margin-top: 1%">
                </form>
            </div>

        <?php } // Event form
        elseif ($eventtype != "" and array_key_exists('EventName', $_POST) != 1) {

            $eventquery = "SELECT * FROM events WHERE EventType='$eventtype' AND AgeCategory='$agecategory'";
            $events = $mysqli->query($eventquery);
            ?>
            <div class="row">

                <form class="form-control" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div>
                        <p class="h5 inlinetext">Selected:</p>
                        <span style="font-size: 115%"
                              class="badge text-bg-secondary inlinetext"><?php echo test_input($_POST["AgeCategory"]) ?></span>
                        <span style="font-size: 115%"
                              class="badge text-bg-secondary inlinetext"><?php echo $eventtype ?></span>
                        <input type="text"
                               value="<?php echo test_input($_POST["AgeCategory"]) ?>" name="AgeCategory" readonly
                               hidden>
                        <input type="text" value="<?php echo $eventtype ?>"
                               name="EventType" readonly hidden>
                        <a href="./points.php" class="badge text-bg-info">Edit</a>
                    </div>

                    <label class="form-label" for="Events">Event:</label>
                    <select class="form-select" id="Events" name="Events"><br>
                        <?php foreach ($events as $val) {
                            ?>
                            <option><?php echo $val["EventName"]; ?></option><br>
                            <?php
                        }
                        ?>
                    </select>
                    <input type="submit" name="submit" value="Submit" class="btn btn-primary" style="margin-top: 1%">
                </form>
            </div>

        <?php } // else if the event variable has been set then scoring boxes are available
        else {

        $eventtype = ($mysqli->query("SELECT * FROM events WHERE EventName='$eventname'")->fetch_assoc())["EventType"];
        $eventid = ($mysqli->query("SELECT * FROM events WHERE EventName='$eventname' AND AgeCategory = '$agecategory'")->fetch_assoc())["EventID"];
        //echo $eventid;
        $maceventperformancedetailsA = getPerformance($eventid, 1, "Mackintosh", $mysqli);
        $maceventperformancedetailsB = getPerformance($eventid, 2, "Mackintosh", $mysqli);
        $laneventperformancedetailsA = getPerformance($eventid, 1, "Langhorne", $mysqli);
        $laneventperformancedetailsB = getPerformance($eventid, 2, "Langhorne", $mysqli);
        $trieventperformancedetailsA = getPerformance($eventid, 1, "Tristram", $mysqli);
        $trieventperformancedetailsB = getPerformance($eventid, 2, "Tristram", $mysqli);
        $greeventperformancedetailsA = getPerformance($eventid, 1, "Greenlees", $mysqli);
        $greeventperformancedetailsB = getPerformance($eventid, 2, "Greenlees", $mysqli);


        $gp = "SELECT SUM(Points) FROM eventresults WHERE House='Greenlees'";
        $lp = "SELECT SUM(Points) FROM eventresults WHERE House='Langhorne'";
        $mp = "SELECT SUM(Points) FROM eventresults WHERE House='Mackintosh'";
        $tp = "SELECT SUM(Points) FROM eventresults WHERE House='Tristram'";

        $mpp = $mysqli->query($mp);
        $mpr = $mpp->fetch_all(MYSQLI_BOTH);
        $lpr = $mysqli->query($lp)->fetch_all(MYSQLI_BOTH);

        $tpr = $mysqli->query($tp)->fetch_all(MYSQLI_BOTH);

        $gpr = $mysqli->query($gp)->fetch_all(MYSQLI_BOTH);

        ?>

        <div class="row">
            <div class="col-sm-4">
                <table class="table table-sm">
                    <tbody>
                    <tr>
                        <td>Mackintosh</td>
                        <td><?= $mpr[0][0] ?></td>
                    </tr>
                    <tr>
                        <td>Langhorne</td>
                        <td><?= $lpr[0][0] ?></td>
                    </tr>
                    <tr>
                        <td>Tristram</td>
                        <td><?= $tpr[0][0] ?></td>
                    </tr>
                    <tr>
                        <td>Greenlees</td>
                        <td><?= $gpr[0][0] ?></td>
                    </tr>
                    <tbody>
                </table>
            </div>
        </div>

        <div class="row">

            <form class="form-control" action="UpdatePoints.php" method="post">
                <div>
                    <p class="h2 inlinetext"><?php echo $eventname ?></p>
                    <span style="font-size: 115%"
                          class="badge text-bg-secondary inlinetext"><?php echo test_input($_POST["AgeCategory"]) ?></span>
                    <span style="font-size: 115%"
                          class="badge text-bg-secondary inlinetext"><?php echo $eventtype ?></span>
                    <input type="text" value="<?php echo $eventname ?>"
                           name="EventName"
                           readonly hidden>
                    <input type="text"
                           value="<?php echo test_input($_POST["AgeCategory"]) ?>" name="AgeCategory" readonly hidden>
                    <a href="./points.php" class="badge text-bg-info">Edit</a>
                </div>

                <table class="table">
                    <thead>
                    <tr>
                        <th>House</th>
                        <th>Name</th>
                        <th>Points</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Mackintosh A:</td>
                        <td><input class="form-control" type="text" name="macnameA" placeholder="Name"
                                   value="<?php echo $maceventperformancedetailsA['CompetitorName'] ?>"></td>
                        <?php makePoints("macpointsA", $maceventperformancedetailsA); ?>
                    </tr>
                    <tr>
                        <td>Mackintosh B:</td>
                        <td><input class="form-control" type="text" name="macnameB" placeholder="Name"
                                   value="<?php echo $maceventperformancedetailsB['CompetitorName'] ?>"></td>
                        <?php makePoints("macpointsB", $maceventperformancedetailsB); ?>
                    </tr>

                    <tr>
                        <td>Langhorne A:</td>
                        <td><input class="form-control" type="text" name="lannameA" placeholder="Name"
                                   value="<?php echo $laneventperformancedetailsA['CompetitorName'] ?>"></td>
                        <?php makePoints("lanpointsA", $laneventperformancedetailsA); ?>
                    </tr>
                    <tr>
                        <td>Langhorne B:</td>
                        <td><input class="form-control" type="text" name="lannameB" placeholder="Name"
                                   value="<?php echo $laneventperformancedetailsB['CompetitorName'] ?>"></td>
                        <?php makePoints("lanpointsB", $laneventperformancedetailsB); ?>
                    </tr>

                    <tr>
                        <td>Tristram A:</td>
                        <td><input class="form-control" type="text" name="trinameA" placeholder="Name"
                                   value="<?php echo $trieventperformancedetailsA['CompetitorName'] ?>"></td>
                        <?php makePoints("tripointsA", $trieventperformancedetailsA); ?>
                    </tr>
                    <tr>
                        <td>Tristram B:</td>
                        <td><input class="form-control" type="text" name="trinameB" placeholder="Name"
                                   value="<?php echo $trieventperformancedetailsB['CompetitorName'] ?>"></td>
                        <?php makePoints("tripointsB", $trieventperformancedetailsB); ?>
                    </tr>

                    <tr>
                        <td>Greenlees A:</td>
                        <td><input class="form-control" type="text" name="grenameA" placeholder="Name"
                                   value="<?php echo $greeventperformancedetailsA['CompetitorName'] ?>"></td>
                        <?php makePoints("grepointsA", $greeventperformancedetailsA); ?>
                    </tr>
                    <tr>
                        <td>Greenlees B:</td>
                        <td><input class="form-control" type="text" name="grenameB" placeholder="Name"
                                   value="<?php echo $greeventperformancedetailsB['CompetitorName'] ?>"></td>
                        <?php makePoints("grepointsB", $greeventperformancedetailsB); ?>
                    </tr>
                    <tr>
                        <td>
                            <input type="submit" name="submit" value="Submit" class="btn btn-primary">
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <?php } ?>
        </div>
    </div>
    </body>
    </html>
<?php $mysqli->close(); ?>