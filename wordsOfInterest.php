<?php

$wordsOfInterest = array(
    array("economy","economic"),
    array("work","working"),
    array("tax","taxation"),
    "world",
    "support",
    "heath",
    "community",
    "debate",
    "public",
    "party",
    "global",
    "business",
    "indigenous",
    "future",
    "policy",
    "energy",
    "system",
    "labor",
    "political",
    "women",
    "financial",
    "give",
    "carbon",
    "relation",
    array("banks","bank","banking"),
    "reform",
    "empire",
    "loan",
    "commonwealth",
    "commission",
    "committee",
    "war",
    "security",
    array("military","forces","army"),
    "local",
    array("state","states","territory","territories"),
    "broadcasting",
    "wool",
    "wheat",
    "meat",
    "oil",
    "gold",
    "mining",
    "export",
    "agriculture",
    "legislation",
    array("britain","british"),
    array("manufacturing","manufacture"),
    "federal",
    //"strong",
    "election",
    "customs",
    //"million",
    //"billion",
    array("foreign","international","overseas"),
    "president",
    "hope",
    "budget",
    "investment",
    "issue",
    array("trade","trading"),
    "defence",
    "services",
    "climate",
    "commitment",
    array("industry","industrial"),
    "treasurer",
    "growth",
    "money",
    "liberal",
    "regional",
    "plan",
    "building",
    array("challenges","challenge"),
    array("families","family"),
    "development",
    "education",
    "infrastructure",
    "fair",
    "change",
    "social",
    "funding",
    "peace",
    "company",
    array("jobs", "job"),
    array("employers","employer"),
    array("hospitals", "hospital"),
);

function isWordOfInterest($word) {
    global $wordsOfInterest;

    foreach ($wordsOfInterest as $mixed) {
        if (is_array($mixed)) {
            if (in_array($word, $mixed)) {
                return true;
            }
        }
        else {
            if ($word == $mixed) {
                return true;
            }
        }
    }
    return false;
}

// If this isn't a collapsible word, return False
// If it is, return an array of words it should be collapsed with
function isCollapsibleWord($word) {
    global $wordsOfInterest;

    foreach ($wordsOfInterest as $mixed) {
        if (is_array($mixed)) {
            if (in_array($word, $mixed)) {
                return $mixed;
            }
        }
    }
    return false;
}
