<?php

    $allData = json_decode(file_get_contents("../generated_data/topWordsByPrimeMinister.json"), true);

    $primeMinisterId = 15;

    $relevantData = array();
    $fullTextString = "";
    foreach ($allData as $entry) {
        if ($entry["prime_minister_id"] == $primeMinisterId) {
            $relevantData[] = $entry;

            $fullTextString .= str_repeat($entry["word"] . " ", $entry["word_count"]);
        }
    }

?>
<html>
<head>
<title>Chart 3</title>
</head>
<body>

<!-- Styles -->
<style>
#chartdiv {
  width: 100%;
  height: 500px;
  font-family: helvetica, sans-serif;
  font-size: 12px;
}
</style>

<!-- Resources -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
<script src="https://cdn.amcharts.com/lib/4/plugins/wordCloud.js"></script>

<!-- Chart code -->
<script>
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_animated);
// Themes end

var chart = am4core.create("chartdiv", am4plugins_wordCloud.WordCloud);
var series = chart.series.push(new am4plugins_wordCloud.WordCloudSeries());

series.accuracy = 4;
series.step = 15;
series.rotationThreshold = 0.7;
series.maxCount = 200;
series.minWordLength = 2;
series.labels.template.tooltipText = "{word}: {value}";
series.fontFamily = "helvetica, sans-serif";
series.maxFontSize = am4core.percent(30);

series.text = "<?php echo $fullTextString; ?>";

series.colors = new am4core.ColorSet();
series.colors.passOptions = {}; // makes it loop

series.fontWeight = "700"

}); // end am4core.ready()
</script>

<!-- HTML -->
<div id="chartdiv"></div>

</body>
</html>
