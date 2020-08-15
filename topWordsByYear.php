<?php

require_once("dbSetup.php");
require_once("wordsOfInterest.php");

$data = array();
$uninterestingWords = array();

// Load the list of all PM names
$primeMinisterNames = array();
$result = $conn->query(
    "SELECT id, name"
    . " FROM prime_minister");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $primeMinisterNames[$row["id"]] = $row["name"];
    }
}

// Early years are very low on data which is misleading
for ($year = 1945; $year <= 2020; $year++) {

    $wordsByPrimeMinister = array();

    // Get the total words from all transcripts by all prime ministers that year
    $result = $conn->query(
        "SELECT prime_minister_id, SUM(total_word_count) AS word_count"
        . " FROM transcript"
        . " WHERE year = " . $year
        . " GROUP BY prime_minister_id");
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $wordsByPrimeMinister[$row["prime_minister_id"]] = $row["word_count"];
        }
    }

    // Get the top words by prime minister that year
    $result = $conn->query(
        "SELECT word.text AS word, transcript.prime_minister_id AS prime_minister_id, SUM(word_count.count) AS word_count, COUNT(word_count.count) AS word_transcripts"
        . " FROM word_count"
        . " INNER JOIN word ON word.id = word_count.word_id"
        . " INNER JOIN transcript ON transcript.id = word_count.transcript_id"
        . " WHERE transcript.year = " . $year
        . " GROUP BY word.text, transcript.prime_minister_id"
        . " ORDER BY SUM(word_count.count) DESC");
    if ($result->num_rows > 0) {
        $recordsForThisYearByPrimeMinister = array_fill_keys(array_keys($wordsByPrimeMinister), 0);

        $dataForYear = array();

        while($row = $result->fetch_assoc()) {
            if (isWordOfInterest($row["word"])) {

                // If this is a word that should be collapsed with others, then do so instead of adding a new word
                $didCollapseWord = false;
                $collapsibleWords = isCollapsibleWord($row["word"]);
                if ($collapsibleWords !== false && is_array($collapsibleWords) && count($collapsibleWords) > 0) {

                    foreach ($dataForYear as &$entry) {
                        // Is this an entry we should add to? Have to match the word & the prime minister
                        if (in_array($entry["word"], $collapsibleWords)
                            && $entry["prime_minister_id"] == $row["prime_minister_id"]
                        ) {
                            // Update the word count + ratio
                            $entry["word_count"] = $entry["word_count"] + $row["word_count"];
                            $entry["word_ratio"] = number_format((float)(round($entry["word_count"] / $wordsByPrimeMinister[$row["prime_minister_id"]], 10)), 10);

                            // Replace the word (if necessary) with the first in the list (the "headline" of the group)
                            $entry["word"] = $collapsibleWords[0];

                            $didCollapseWord = true;
                            break;
                        }
                    }

                }

                if (!$didCollapseWord) {

                    // This is a new, unique word of interest
                    $recordsForThisYearByPrimeMinister[$row["prime_minister_id"]]++;

                    $wordRatio = number_format((float)(round($row["word_count"] / $wordsByPrimeMinister[$row["prime_minister_id"]], 10)), 10);

                    $dataForYear[] = array(
                        "year" => $year,
                        "word" => $row["word"],
                        "word_count" => $row["word_count"],
                        "word_ratio" => $wordRatio,
                        "prime_minister_id" => $row["prime_minister_id"],
                        "prime_minister_name" => $primeMinisterNames[$row["prime_minister_id"]],
                    );

                    $allRecordsFound = true;
                    foreach ($recordsForThisYearByPrimeMinister as $primeMinisterId => $recordCount) {
                        if ($recordCount < 10) {
                            $allRecordsFound = false;
                        }
                    }
                    if ($allRecordsFound) {
                        break;
                    }
                }
            }
            else {
                $uninterestingWords[] = $row["word"];
            }
        }

        $data = array_merge($data, $dataForYear);
    }
}

//echo "<pre>";
//print_r($data);
//echo "uninteresting words:";
//print_r($uninterestingWords);
//echo "JSON:";
echo json_encode($data);
