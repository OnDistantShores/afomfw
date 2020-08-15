<?php

require_once("dbSetup.php");
require_once("wordsOfInterest.php");

$data = array();
$uninterestingWords = array();

// Load the list of all PM data
$primeMinisterData = array();
$result = $conn->query(
    "SELECT id, name, total_word_count"
    . " FROM prime_minister");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $primeMinisterData[$row["id"]] = array(
            "name" => $row["name"],
            "total_word_count" => $row["total_word_count"],
        );
    }
}

// Get the top words by prime minister
$result = $conn->query(
    "SELECT word.text AS word, transcript.prime_minister_id AS prime_minister_id, SUM(word_count.count) AS word_count, COUNT(word_count.count) AS word_transcripts"
    . " FROM word_count"
    . " INNER JOIN word ON word.id = word_count.word_id"
    . " INNER JOIN transcript ON transcript.id = word_count.transcript_id"
    . " GROUP BY word.text, transcript.prime_minister_id"
    . " ORDER BY transcript.prime_minister_id, SUM(word_count.count) DESC");
if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {
        if (isWordOfInterest($row["word"])) {

            // If this is a word that should be collapsed with others, then do so instead of adding a new word
            $didCollapseWord = false;
            $collapsibleWords = isCollapsibleWord($row["word"]);
            if ($collapsibleWords !== false && is_array($collapsibleWords) && count($collapsibleWords) > 0) {

                foreach ($data as &$entry) {
                    // Is this an entry we should add to? Have to match the word & the prime minister
                    if (in_array($entry["word"], $collapsibleWords)
                        && $entry["prime_minister_id"] == $row["prime_minister_id"]
                    ) {
                        // Update the word count + ratio
                        $entry["word_count"] = $entry["word_count"] + $row["word_count"];
                        $entry["word_ratio"] = number_format((float)(round($entry["word_count"] / $primeMinisterData[$row["prime_minister_id"]]["total_word_count"], 10)), 10);

                        // Replace the word (if necessary) with the first in the list (the "headline" of the group)
                        $entry["word"] = $collapsibleWords[0];

                        $didCollapseWord = true;
                        break;
                    }
                }

            }

            if (!$didCollapseWord) {

                $wordRatio = number_format((float)(round($row["word_count"] / $primeMinisterData[$row["prime_minister_id"]]["total_word_count"], 10)), 10);

                $data[] = array(
                    "word" => $row["word"],
                    "word_count" => $row["word_count"],
                    "word_ratio" => $wordRatio,
                    "prime_minister_id" => $row["prime_minister_id"],
                    "prime_minister_name" => $primeMinisterData[$row["prime_minister_id"]]["name"],
                );
            }
        }
        else {
            $uninterestingWords[] = $row["word"];
        }
    }
}

//echo "<pre>";
//print_r($data);
//echo "uninteresting words:";
//print_r($uninterestingWords);
//echo "JSON:";
echo json_encode($data);
