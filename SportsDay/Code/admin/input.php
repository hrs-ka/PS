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
        <title>Score Entry</title>
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
    } elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
        $eventname = $_GET["EventName"];
        $agecategory = $_GET["AgeCategory"];
        $eventype = $_GET["EventType"];
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
            "SELECT events.EventName, events.EventID, eventresults.EventID, eventresults.ResultID, events.AgeCategory, eventresults.CompetitorName, eventresults.CompetitorNumber, eventresults.Place, eventresults.House, performances.PerformanceID, performances.Performance, performances.IsFail
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

    function getPerformanceAttempt($performanceevent, $competitornumber, $performancehouse, $attemptnumber, $funcsql)
    {
        // alter to have competitor name for all to be returned as for the first attempt
        $rslt = ($funcsql->query(
            "SELECT events.EventName, events.EventID, eventresults.EventID, eventresults.ResultID, eventresults.CompetitorNumber, eventresults.CompetitorName, eventresults.House, performances.PerformanceID, performances.Performance, performances.AttemptNumber, performances.IsFail
            FROM events
            INNER JOIN eventresults ON events.EventID = eventresults.EventID
            INNER JOIN performances ON eventresults.ResultID = performances.ResultID
            WHERE events.EventID = '$performanceevent' and eventresults.House = '$performancehouse' and eventresults.CompetitorNumber = $competitornumber and performances.AttemptNumber = $attemptnumber;")->fetch_assoc());
        if (is_null($rslt)) {
            $rslt = array("EventName" => "", "eventresults" => "", "CompetitorName" => "", "CompetitorNumber" => "", "House" => "", "Performance" => ".");
        }

        $competitorname = ($funcsql->query("SELECT events.EventName, events.EventID, eventresults.EventID, eventresults.ResultID, eventresults.CompetitorNumber, eventresults.CompetitorName, eventresults.House, performances.PerformanceID, performances.Performance, performances.AttemptNumber, performances.IsFail
                    FROM events
                    INNER JOIN eventresults ON events.EventID = eventresults.EventID
                    INNER JOIN performances ON eventresults.ResultID = performances.ResultID
                    WHERE events.EventID = '$performanceevent' and eventresults.House = '$performancehouse' and eventresults.CompetitorNumber = $competitornumber and performances.AttemptNumber = 1;")->fetch_assoc());
        if (isset($competitorname["CompetitorName"])) {
            $rslt["CompetitorName"] = $competitorname["CompetitorName"];
        }


        return $rslt;
    }

    function makeCheckbox($checkname, $dropver)
    {
        $fail = "";
        if (isset($dropver['IsFail'])) {
            if ($dropver['IsFail'] == "true") {
                $fail = "checked";
            }
        }
        ?>
        <div class="col-sm-1">
            <div class="form-check form-check-inlinfe">
                <input class="form-check-input" type="checkbox" id="<?php echo $checkname ?>" value="true"
                       name="<?php echo $checkname ?>" <?php echo $fail ?>>
            </div>
        </div>
        <?php
    }

    function makeDropdownsMetersCentimetres($dropnameA, $dropnameB, $dropver)
    {
        ?>
        <div class="col-sm-2">
            <select name="<?php echo $dropnameA ?>" class="form-select">
                <option value="" disabled selected>Meters</option>
                <?php for ($x = 0; $x <= 60; $x++) { ?>
                    <option <?php echo ((explode(".", $dropver['Performance'])[0]) == $x) ? 'selected' : ''; ?>>
                        <?php echo $x ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-2">
            <select name="<?php echo $dropnameB ?>" class="form-select">
                <option value="" disabled selected>Centimetres</option>
                <?php for ($x = 0; $x <= 99; $x++) {
                    if (strlen(strval($x)) < 2) {
                        $cur = "0" . strval($x);
                    } else {
                        $cur = $x;
                    } ?>

                    <option <?php echo ((explode(".", $dropver['Performance'])[1]) == $x) ? 'selected' : ''; ?>>
                        <?php echo $cur ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <?php
    }

    function makeAttempts($dropver)
    {
        ?>
        <td>
            <?php echo $dropver['Performance'] ?>
        </td>
        <?php
    }

    function makeDropdownsMinsSecs($dropnameA, $dropnameB, $dropver)
    {
        ?>
        <div class="col-sm-2">
            <select name="<?php echo $dropnameA ?>" class="form-select">
                <option value="" disabled selected>Minutes</option>
                <?php for ($x = 0; $x <= 90; $x++) { ?>
                    <option <?php echo ((explode(".", $dropver['Performance'])[0]) == $x) ? 'selected' : ''; ?>>
                        <?php echo $x ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-2">
            <select name="<?php echo $dropnameB ?>" class="form-select">
                <option value="" disabled selected>Seconds</option>
                <?php for ($x = 0; $x <= 99; $x++) {
                    if (strlen(strval($x)) < 2) {
                        $cur = "0" . strval($x);
                    } else {
                        $cur = $x;
                    } ?>
                    <option <?php echo ((explode(".", $dropver['Performance'])[1]) == $x) ? 'selected' : ''; ?>>
                        <?php echo $cur ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <?php
    }

    function makePlace($placename, $dropver)
    {
        ?>
        <div class="col-sm-2">
            <select name="<?php echo $placename ?>" class="form-select">
                <option value="" disabled selected>Place</option>
                <?php for ($x = 1; $x <= 8; $x++) { ?>
                    <option <?php echo ($dropver['Place'] == $x) ? 'selected' : ''; ?>>
                        <?php echo $x ?>
                    </option>
                <?php } ?>
            </select>
        </div>
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
        elseif ($eventtype != "" and array_key_exists('EventName', $_POST) != 1 and $eventname == "") {

            $eventquery = "SELECT * FROM events WHERE EventType='$eventtype' AND AgeCategory='$agecategory'";
            $events = $mysqli->query($eventquery);
            ?>
            <div class="row">

                <form class="form-control" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div>
                        <p class="h5 inlinetext">Selected:</p>
                        <span style="font-size: 115%"
                              class="badge text-bg-secondary inlinetext"><?php echo $agecategory ?></span>
                        <span style="font-size: 115%"
                              class="badge text-bg-secondary inlinetext"><?php echo $eventtype ?></span>
                        <input type="text"
                               value="<?php echo $agecategory ?>" name="AgeCategory" readonly
                               hidden>
                        <input type="text" value="<?php echo $eventtype ?>"
                               name="EventType" readonly hidden>
                        <a href="/admin/input.php" class="badge text-bg-info">Edit</a>
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

            <form class="form-control" action="UpdateDB.php" method="post">
                <div>
                    <p class="h2 inlinetext"><?php echo $eventname ?></p>
                    <span style="font-size: 115%"
                          class="badge text-bg-secondary inlinetext"><?php echo $agecategory ?></span>
                    <span style="font-size: 115%"
                          class="badge text-bg-secondary inlinetext"><?php echo $eventtype ?></span>
                    <input type="text" value="<?php echo $eventname ?>"
                           name="EventName"
                           readonly hidden>
                    <input type="text"
                           value="<?php echo $agecategory ?>" name="AgeCategory" readonly hidden>
                    <input type="text" value="<?php echo $eventtype ?>"
                           name="EventType"
                           readonly hidden>
                    <a href="/admin/input.php" class="badge text-bg-info">Edit</a>
                </div>


                <?php if ($eventtype == "Track") { ?>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">House</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Name</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Place</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Mins/Secs</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Secs/Msecs</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Fail</label>
                        </div>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Mackintosh A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="macnameA" placeholder="Name"
                                   value="<?= $maceventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("macplaceA", $maceventperformancedetailsA);
                        makeDropdownsMinsSecs("macminsA", "macsecsA", $maceventperformancedetailsA);
                        makeCheckbox("macAinvalid", $maceventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Mackintosh B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="macnameB" placeholder="Name"
                                   value="<?= $maceventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("macplaceB", $maceventperformancedetailsB);
                        makeDropdownsMinsSecs("macminsB", "macsecsB", $maceventperformancedetailsB);
                        makeCheckbox("macBinvalid", $maceventperformancedetailsB) ?>
                    </div>

                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Langhorne A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="lannameA" placeholder="Name"
                                   value="<?= $laneventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("lanplaceA", $laneventperformancedetailsA);
                        makeDropdownsMinsSecs("lanminsA", "lansecsA", $laneventperformancedetailsA);
                        makeCheckbox("lanAinvalid", $laneventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Langhorne B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="lannameB" placeholder="Name"
                                   value="<?= $laneventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("lanplaceB", $laneventperformancedetailsB);
                        makeDropdownsMinsSecs("lanminsB", "lansecsB", $laneventperformancedetailsB);
                        makeCheckbox("lanBinvalid", $laneventperformancedetailsB) ?>
                    </div>

                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Tristram A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="trinameA" placeholder="Name"
                                   value="<?= $trieventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("triplaceA", $trieventperformancedetailsA);
                        makeDropdownsMinsSecs("triminsA", "trisecsA", $trieventperformancedetailsA);
                        makeCheckbox("triAinvalid", $trieventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Tristram B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="trinameB" placeholder="Name"
                                   value="<?= $trieventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("triplaceB", $trieventperformancedetailsB);
                        makeDropdownsMinsSecs("triminsB", "trisecsB", $trieventperformancedetailsB);
                        makeCheckbox("triBinvalid", $trieventperformancedetailsB) ?>
                    </div>

                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Greenlees A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="grenameA" placeholder="Name"
                                   value="<?= $greeventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("greplaceA", $greeventperformancedetailsA);
                        makeDropdownsMinsSecs("greminsA", "gresecsA", $greeventperformancedetailsA);
                        makeCheckbox("greAinvalid", $greeventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Greenlees B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="grenameB" placeholder="Name"
                                   value="<?= $greeventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("greplaceB", $greeventperformancedetailsB);
                        makeDropdownsMinsSecs("greminsB", "gresecsB", $greeventperformancedetailsB);
                        makeCheckbox("greBinvalid", $greeventperformancedetailsB) ?>
                    </div>

                    <div class="row p-2">
                        <div class="col-sm-2">
                            <input type="submit" name="submit" value="Submit" class="btn btn-primary">
                        </div>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <button name="EventFinished" class="btn btn-secondary" style="margin-top: 1%"
                                    formaction="UpdateDB.php">End Event
                            </button>
                        </div>
                    </div>

                <?php } elseif ($eventtype == "High Jump") {
                    ?>

                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">House</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Name</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Place</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Meters</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Centimetres</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Fail</label>
                        </div>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Mackintosh A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="macnameA" placeholder="Name"
                                   value="<?= $maceventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("macplaceA", $maceventperformancedetailsA);
                        makeDropdownsMetersCentimetres("macmetresA", "maccentimetresA", $maceventperformancedetailsA);
                        makeCheckbox("macAinvalid", $maceventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Mackintosh B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="macnameB" placeholder="Name"
                                   value="<?= $maceventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("macplaceB", $maceventperformancedetailsB);
                        makeDropdownsMetersCentimetres("macmetresB", "maccentimetresB", $maceventperformancedetailsB);
                        makeCheckbox("macBinvalid", $maceventperformancedetailsB) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Langhorne A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="lannameA" placeholder="Name"
                                   value="<?= $laneventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("lanplaceA", $laneventperformancedetailsA);
                        makeDropdownsMetersCentimetres("lanmetresA", "lancentimetresA", $laneventperformancedetailsA);
                        makeCheckbox("lanAinvalid", $laneventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Langhorne B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="lannameB" placeholder="Name"
                                   value="<?= $laneventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("lanplaceB", $laneventperformancedetailsB);
                        makeDropdownsMetersCentimetres("lanmetresB", "lancentimetresB", $laneventperformancedetailsB);
                        makeCheckbox("lanBinvalid", $laneventperformancedetailsB) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Tristram A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="trinameA" placeholder="Name"
                                   value="<?= $trieventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("triplaceA", $trieventperformancedetailsA);
                        makeDropdownsMetersCentimetres("trimetresA", "tricentimetresA", $trieventperformancedetailsA);
                        makeCheckbox("triAinvalid", $trieventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Tristram B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="trinameB" placeholder="Name"
                                   value="<?= $trieventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("triplaceB", $trieventperformancedetailsB);
                        makeDropdownsMetersCentimetres("trimetresB", "tricentimetresB", $trieventperformancedetailsB);
                        makeCheckbox("triBinvalid", $trieventperformancedetailsB) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Greenlees A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="grenameA" placeholder="Name"
                                   value="<?= $greeventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("greplaceA", $greeventperformancedetailsA);
                        makeDropdownsMetersCentimetres("gremetresA", "grecentimetresA", $greeventperformancedetailsA);
                        makeCheckbox("greAinvalid", $greeventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Greenlees B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="grenameB" placeholder="Name"
                                   value="<?= $greeventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makePlace("greplaceB", $greeventperformancedetailsB);
                        makeDropdownsMetersCentimetres("gremetresB", "grecentimetresB", $greeventperformancedetailsB);
                        makeCheckbox("greBinvalid", $greeventperformancedetailsB) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <input type="submit" name="submit" value="Submit" class="btn btn-primary">
                        </div>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <button name="EventFinished" class="btn btn-secondary" style="margin-top: 1%"
                                    formaction="UpdateDB.php">End Event
                            </button>
                        </div>
                    </div>

                <?php } // Field input boxes for specific attempt number
                elseif ($attemptno != "") {

                    $maceventperformancedetailsA = getPerformanceAttempt($eventid, 1, "Mackintosh", $attemptno, $mysqli);
                    $maceventperformancedetailsB = getPerformanceAttempt($eventid, 2, "Mackintosh", $attemptno, $mysqli);
                    $laneventperformancedetailsA = getPerformanceAttempt($eventid, 1, "Langhorne", $attemptno, $mysqli);
                    $laneventperformancedetailsB = getPerformanceAttempt($eventid, 2, "Langhorne", $attemptno, $mysqli);
                    $trieventperformancedetailsA = getPerformanceAttempt($eventid, 1, "Tristram", $attemptno, $mysqli);
                    $trieventperformancedetailsB = getPerformanceAttempt($eventid, 2, "Tristram", $attemptno, $mysqli);
                    $greeventperformancedetailsA = getPerformanceAttempt($eventid, 1, "Greenlees", $attemptno, $mysqli);
                    $greeventperformancedetailsB = getPerformanceAttempt($eventid, 2, "Greenlees", $attemptno, $mysqli);
                    ?>
                    <input type="text" value="<?php echo $attemptno ?>"
                           name="AttemptNum"
                           readonly hidden>

                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">House</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Name</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Metres</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Centimetres</label>
                        </div>
                        <div class="col-sm-2">
                            <label class="form-label">Fail</label>
                        </div>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Mackintosh A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="macnameA" placeholder="Name"
                                   value="<?= $maceventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makeDropdownsMetersCentimetres("macmetresA", "maccentimetresA", $maceventperformancedetailsA);
                        makeCheckbox("macAinvalid", $maceventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Mackintosh B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="macnameB" placeholder="Name"
                                   value="<?= $maceventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makeDropdownsMetersCentimetres("macmetresB", "maccentimetresB", $maceventperformancedetailsB);
                        makeCheckbox("macBinvalid", $maceventperformancedetailsB) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Langhorne A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="lannameA" placeholder="Name"
                                   value="<?= $laneventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makeDropdownsMetersCentimetres("lanmetresA", "lancentimetresA", $laneventperformancedetailsA);
                        makeCheckbox("lanAinvalid", $laneventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Langhorne B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="lannameB" placeholder="Name"
                                   value="<?= $laneventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makeDropdownsMetersCentimetres("lanmetresB", "lancentimetresB", $laneventperformancedetailsB);
                        makeCheckbox("lanBinvalid", $laneventperformancedetailsB) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Tristram A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="trinameA" placeholder="Name"
                                   value="<?= $trieventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makeDropdownsMetersCentimetres("trimetresA", "tricentimetresA", $trieventperformancedetailsA);
                        makeCheckbox("triAinvalid", $trieventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Tristram B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="trinameB" placeholder="Name"
                                   value="<?= $trieventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makeDropdownsMetersCentimetres("trimetresB", "tricentimetresB", $trieventperformancedetailsB);
                        makeCheckbox("triBinvalid", $trieventperformancedetailsB) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Greenlees A:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="grenameA" placeholder="Name"
                                   value="<?= $greeventperformancedetailsA['CompetitorName'] ?>">
                        </div>
                        <?php makeDropdownsMetersCentimetres("gremetresA", "grecentimetresA", $greeventperformancedetailsA);
                        makeCheckbox("greAinvalid", $greeventperformancedetailsA) ?>
                    </div>
                    <div class="row p-2">
                        <div class="col-sm-2">
                            <label class="form-label">Greenlees B:</label>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="grenameB" placeholder="Name"
                                   value="<?= $greeventperformancedetailsB['CompetitorName'] ?>">
                        </div>
                        <?php makeDropdownsMetersCentimetres("gremetresB", "grecentimetresB", $greeventperformancedetailsB);
                        makeCheckbox("greBinvalid", $greeventperformancedetailsB) ?>
                    </div>

                    <div class="row p-2">
                        <div class="col-sm-2">
                            <input type="submit" name="submit" value="Submit" class="btn btn-primary">
                        </div>
                    </div>

                <?php }
                }
                ?>
            </form>

            <?php

            // Field specific attempt number selection
            if ($eventtype == "Field" and $eventname != "" and $attemptno == "") {

                $maceventperformancedetailsA1 = getPerformanceAttempt($eventid, 1, "Mackintosh", 1, $mysqli);
                $maceventperformancedetailsA2 = getPerformanceAttempt($eventid, 1, "Mackintosh", 2, $mysqli);
                $maceventperformancedetailsA3 = getPerformanceAttempt($eventid, 1, "Mackintosh", 3, $mysqli);
                $maceventperformancedetailsB1 = getPerformanceAttempt($eventid, 2, "Mackintosh", 1, $mysqli);
                $maceventperformancedetailsB2 = getPerformanceAttempt($eventid, 2, "Mackintosh", 2, $mysqli);
                $maceventperformancedetailsB3 = getPerformanceAttempt($eventid, 2, "Mackintosh", 3, $mysqli);

                $laneventperformancedetailsA1 = getPerformanceAttempt($eventid, 1, "Langhorne", 1, $mysqli);
                $laneventperformancedetailsA2 = getPerformanceAttempt($eventid, 1, "Langhorne", 2, $mysqli);
                $laneventperformancedetailsA3 = getPerformanceAttempt($eventid, 1, "Langhorne", 3, $mysqli);
                $laneventperformancedetailsB1 = getPerformanceAttempt($eventid, 2, "Langhorne", 1, $mysqli);
                $laneventperformancedetailsB2 = getPerformanceAttempt($eventid, 2, "Langhorne", 2, $mysqli);
                $laneventperformancedetailsB3 = getPerformanceAttempt($eventid, 2, "Langhorne", 3, $mysqli);

                $trieventperformancedetailsA1 = getPerformanceAttempt($eventid, 1, "Tristram", 1, $mysqli);
                $trieventperformancedetailsA2 = getPerformanceAttempt($eventid, 1, "Tristram", 2, $mysqli);
                $trieventperformancedetailsA3 = getPerformanceAttempt($eventid, 1, "Tristram", 3, $mysqli);
                $trieventperformancedetailsB1 = getPerformanceAttempt($eventid, 2, "Tristram", 1, $mysqli);
                $trieventperformancedetailsB2 = getPerformanceAttempt($eventid, 2, "Tristram", 2, $mysqli);
                $trieventperformancedetailsB3 = getPerformanceAttempt($eventid, 2, "Tristram", 3, $mysqli);

                $greeventperformancedetailsA1 = getPerformanceAttempt($eventid, 1, "Greenlees", 1, $mysqli);
                $greeventperformancedetailsA2 = getPerformanceAttempt($eventid, 1, "Greenlees", 2, $mysqli);
                $greeventperformancedetailsA3 = getPerformanceAttempt($eventid, 1, "Greenlees", 3, $mysqli);
                $greeventperformancedetailsB1 = getPerformanceAttempt($eventid, 2, "Greenlees", 1, $mysqli);
                $greeventperformancedetailsB2 = getPerformanceAttempt($eventid, 2, "Greenlees", 2, $mysqli);
                $greeventperformancedetailsB3 = getPerformanceAttempt($eventid, 2, "Greenlees", 3, $mysqli);

                ?>

                <form class="form-control" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div>
                    <span style="font-size: 115%" class="badge text-bg-secondary inlinetext"
                          hidden><?php echo $agecategory ?></span>
                        <span style="font-size: 115%" class="badge text-bg-secondary inlinetext"
                              hidden><?php echo $eventtype ?></span>
                        <input class="form-control inlineclass" type="text" value="<?php echo $eventname ?>"
                               name="Events"
                               readonly hidden>
                        <input class="form-control inlineclass" type="text"
                               value="<?php echo $agecategory ?>" name="AgeCategory" readonly
                               hidden>
                        <input class="form-control inlineclass" type="text" value="<?php echo $eventtype ?>"
                               name="EventType" readonly hidden>
                        <a href="/admin/input.php" class="badge text-bg-info" hidden>Edit</a>
                    </div>
                    <table class="table table-borderless">
                        <tr>
                            <th>House</th>
                            <th>Name</th>
                            <th>Attempt 1</th>
                            <th>Attempt 2</th>
                            <th>Attempt 3</th>
                        </tr>
                        <tr>
                            <td>Mackintosh A:</td>
                            <td>
                                <?php echo $maceventperformancedetailsA['CompetitorName'] ?>
                            </td>
                            <?php makeAttempts($maceventperformancedetailsA1);
                            makeAttempts($maceventperformancedetailsA2);
                            makeAttempts($maceventperformancedetailsA3); ?>
                        </tr>
                        <tr>
                            <td>Mackintosh B:</td>
                            <td><label>
                                    <?php echo $maceventperformancedetailsB['CompetitorName'] ?>
                                </label></td>
                            <?php makeAttempts($maceventperformancedetailsB1);
                            makeAttempts($maceventperformancedetailsB2);
                            makeAttempts($maceventperformancedetailsB3); ?>
                        </tr>

                        <tr>
                            <td>Langhorne A:</td>
                            <td><label>
                                    <?php echo $laneventperformancedetailsA['CompetitorName'] ?>
                                </label></td>
                            <?php makeAttempts($laneventperformancedetailsA1);
                            makeAttempts($laneventperformancedetailsA2);
                            makeAttempts($laneventperformancedetailsA3); ?>
                        </tr>
                        <tr>
                            <td>Langhorne B:</td>
                            <td><label>
                                    <?php echo $laneventperformancedetailsB['CompetitorName'] ?>
                                </label></td>
                            <?php makeAttempts($laneventperformancedetailsB1);
                            makeAttempts($laneventperformancedetailsB2);
                            makeAttempts($laneventperformancedetailsB3); ?>
                        </tr>

                        <tr>
                            <td>Tristram A:</td>
                            <td><label>
                                    <?php echo $trieventperformancedetailsA['CompetitorName'] ?>
                                </label></td>
                            <?php makeAttempts($trieventperformancedetailsA1);
                            makeAttempts($trieventperformancedetailsA2);
                            makeAttempts($trieventperformancedetailsA3); ?>
                        </tr>
                        <tr>
                            <td>Tristram B:</td>
                            <td><label>
                                    <?php echo $trieventperformancedetailsB['CompetitorName'] ?>
                                </label></td>
                            <?php makeAttempts($trieventperformancedetailsB1);
                            makeAttempts($trieventperformancedetailsB2);
                            makeAttempts($trieventperformancedetailsB3); ?>
                        </tr>

                        <tr>
                            <td>Greenlees A:</td>
                            <td><label>
                                    <?php echo $greeventperformancedetailsA['CompetitorName'] ?>
                                </label></td>
                            <?php makeAttempts($greeventperformancedetailsA1);
                            makeAttempts($greeventperformancedetailsA2);
                            makeAttempts($greeventperformancedetailsA3); ?>
                        </tr>
                        <tr>
                            <td>Greenlees B:</td>
                            <td><label>
                                    <?php echo $greeventperformancedetailsB['CompetitorName'] ?>
                                </label></td>
                            <?php makeAttempts($greeventperformancedetailsB1);
                            makeAttempts($greeventperformancedetailsB2);
                            makeAttempts($greeventperformancedetailsB3); ?>
                        </tr>


                        <tr>
                            <td></td>
                            <td></td>
                            <td><input type="submit" name="Attempt1" value="Edit" class="btn btn-info btn-sm"
                                       style="margin-top: 1%"></td>
                            <td><input type="submit" name="Attempt2" value="Edit" class="btn btn-info btn-sm"
                                       style="margin-top: 1%"></td>
                            <td><input type="submit" name="Attempt3" value="Edit" class="btn btn-info btn-sm"
                                       style="margin-top: 1%"></td>
                        </tr>

                        <tr>
                            <td>
                                <button name="EventFinished" class="btn btn-secondary" style="margin-top: 1%"
                                        formaction="UpdateDB.php">End Event
                                </button>
                            </td>
                        </tr>

                    </table>

                </form>

            <?php }

            ?>
        </div>
    </div>
    </body>
    </html>
<?php $mysqli->close(); ?>