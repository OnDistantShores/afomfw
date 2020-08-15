<?php

require_once("../dbSetup.php");

$result = $conn->query("SELECT id, date FROM transcript WHERE day IS NULL OR month IS NULL OR year IS NULL");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $dateChunks = explode("/", $row["date"]);

        $conn->query("UPDATE transcript SET day = " . $dateChunks[0] . ", month = " . $dateChunks[1] . ", year = " . $dateChunks[2] . " WHERE id = " . $row["id"]);
    }
}
