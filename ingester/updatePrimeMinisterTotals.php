<?php

require_once("../dbSetup.php");

$result = $conn->query("SELECT id FROM prime_minister");
if ($result->num_rows > 0) {
    while($primeMinisterRow = $result->fetch_assoc()) {
        $wordCountResult = $conn->query("SELECT SUM(count) AS total_word_count "
                            . " FROM word_count "
                            . " INNER JOIN transcript ON transcript.id = word_count.transcript_id"
                            . " WHERE transcript.prime_minister_id = " . $primeMinisterRow["id"]);
        if ($wordCountResult->num_rows > 0 && $wordCountRow = $wordCountResult->fetch_assoc()) {
            $conn->query("UPDATE prime_minister SET total_word_count = " . $wordCountRow["total_word_count"] . " WHERE id = " . $primeMinisterRow["id"]);
        }
    }
}
