<?php

ini_set('max_execution_time', 300);

require_once("../dbSetup.php");

$dataPath = "../../data/pm-transcripts-master/transcripts/";

$globalValidWords = json_decode(file_get_contents("../../english-words-master/words_dictionary.json"), true);
function checkValidWord($word) {
    global $globalValidWords;
    return (isset($globalValidWords[$word]) && !isEverydayWord($word));
}

function cleanWord($word) {
    return strtolower(trim($word));
}

// From https://gist.github.com/keithmorris/4155220
function isEverydayWord($word) {

    // Words from source referenced above
    $commonWords = array('a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero');

    // My additions
    $commonWords = array_merge($commonWords, array('time','lot','put','years','part'));

    return (in_array($word, $commonWords));
}

// Load up the IDs of all the words we have so far

$globalSeenWords = array();
$result = $conn->query("SELECT id, text FROM word");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $globalSeenWords[$row["text"]] = $row["id"];
    }
}

// Find all the files in the directory & loop through them
$filenames = array_diff(scandir($dataPath), array('.', '..'));
shuffle($filenames);
$fileCount = 0;
foreach ($filenames as $filename) {
    if (pathinfo($filename, PATHINFO_EXTENSION) == "xml") {
        echo "Loading $filename...<br />";

        $fileContents = file_get_contents($dataPath . $filename);
        if (strlen(trim($fileContents)) > 0) {
            //echo "fileContents = " . $fileContents . "<br />";

            $xml = new SimpleXMLElement($fileContents);
            if ($xml && $xml->transcript && $xml->transcript->content && strlen(trim($xml->transcript->{'prime-minister'})) > 0) {
                $transcriptId = $xml->transcript->{'transcript-id'};

                // Confirm we haven't alredy ingested this transcript

                $result = $conn->query("SELECT id FROM transcript WHERE id = " . $transcriptId);
                if ($result->num_rows == 0) {

                    $content = $xml->transcript->content;
                    $contentElements = explode(" ", htmlentities($content));
                    $validWordsFound = array();
                    $invalidWordsFound = array();
                    foreach ($contentElements as $contentElement) {
                        $word = cleanWord($contentElement);
                        if (checkValidWord($word)) {
                            $validWordsFound[] = $word;
                            //echo $word . "<br />";
                        }
                        else {
                            $invalidWordsFound[] = $word;
                        }
                    }

                    $countValidWords = array_count_values($validWordsFound);

                    /*arsort($countValidWords);
                    echo "<pre>";
                    print_r($countValidWords);
                    echo "validWordsFound = " . count($validWordsFound) . "<br />";
                    echo "invalidWordsFound = " . count($invalidWordsFound) . "<br />";*/

                    // Create the PM record if necessary

                    $primeMinisterId = null;
                    $result = $conn->query("SELECT id FROM prime_minister WHERE name = '" . $conn->real_escape_string($xml->transcript->{'prime-minister'}) . "'");
                    if ($result->num_rows == 0) {
                        $conn->query("INSERT INTO prime_minister (name) VALUES ('" . $conn->real_escape_string($xml->transcript->{'prime-minister'}) . "')");
                        $primeMinisterId = $conn->insert_id;
                    }
                    else {
                        $row = $result->fetch_assoc();
                        $primeMinisterId = $row["id"];
                    }

                    //echo "primeMinisterId = " . $primeMinisterId;

                    // Create the transcript record

                    $result = $conn->query(
                        "INSERT INTO transcript (id, date, prime_minister_id, release_type, title, filename, total_word_count) "
                        . " VALUES (" . $transcriptId . ", "
                                . "'" . $conn->real_escape_string($xml->transcript->{'release-date'}) . "', "
                                . $primeMinisterId . ", "
                                . "'" . $conn->real_escape_string($xml->transcript->{'release-type'}) . "', "
                                . "'" . $conn->real_escape_string($xml->transcript->title) . "', "
                                . "'" . $filename . "', "
                                . count($validWordsFound) . ")"
                    );

                    // Insert all the words we found and their counts

                    foreach ($countValidWords as $word => $count) {

                        if (!isset($globalSeenWords[$word])) {
                            $conn->query("INSERT INTO word (text) VALUES ('" . $conn->real_escape_string($word) . "')");
                            $globalSeenWords[$word] = $conn->insert_id;
                        }

                        $countRatio = round($count / count($validWordsFound), 10);

                        $result = $conn->query(
                            "INSERT INTO word_count (word_id, transcript_id, text_type, count, count_ratio) "
                            . " VALUES (" . $globalSeenWords[$word] . ", "
                                    . $transcriptId . ", "
                                    . "'content', "
                                    . $count . ", "
                                    . $countRatio . ")"
                        );
                    }

                    echo "Saved data for " . count($countValidWords) . " words for transcript " . $transcriptId . "<br />";

                    $fileCount++;
                    //if ($fileCount > 100) {
                    //    exit;
                    //}
                }
            }
        }
    }
}
