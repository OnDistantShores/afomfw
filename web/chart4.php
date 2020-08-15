<?php

    $allData = json_decode(file_get_contents("../generated_data/top100WordsByYear.json"), true);

    $minYear = 1959;

    $primeMinisterRanges = array();
    $topWordsPerYearData = array();
    $count = 0;
    foreach ($allData as $entry) {
        if ($entry["year"] >= $minYear) {

            // Cast year variable type to prevent display weirdness
            $entry["year"] = (string)$entry["year"];

            // We need to be able to show the same year twice, when PMs change over
            $yearCategoryIdentifier = (string)($entry["year"] . $count);
            $entry["yearCategory"] = $yearCategoryIdentifier;
            $count++;

            $topWordsPerYearData[] = $entry;

            // Track when each PM started & ended, for the range labels
            if (!isset($primeMinisterRanges[$entry["prime_minister_id"]])) {
                $primeMinisterRanges[$entry["prime_minister_id"]] = array(
                    "name" => $entry["prime_minister_name"],
                    "start" => $entry["year"]
                );
            }
            $primeMinisterRanges[$entry["prime_minister_id"]]["end"] = $entry["year"];
        }
    }

    $topWordsPerYearData = array_values($topWordsPerYearData);

?>
<html>
<head>
<title>Chart 4</title>
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

<!-- Chart code -->
<script>
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_animated);
// Themes end

// Create chart instance
var chart = am4core.create("chartdiv", am4charts.XYChart);

// Add data
/*chart.data = [{
  "country": "USA",
  "visits": 2025,
  "word": "yeehar"
}];*/
var allData = <?php echo json_encode($topWordsPerYearData) ?>;

function updateChartData(word) {
    var filteredData = new Array();

    for (var i = 0; i < allData.length; i++) {
        if (allData[i].word == word) {
            filteredData.push(allData[i]);
        }
    }

    chart.data = filteredData;
    chart.invalidateRawData();
}
window.updateChartData = updateChartData;

updateChartData("state");

// Create axes

var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
categoryAxis.dataFields.category = "year";
//categoryAxis.renderer.minGridDistance = 20;
//categoryAxis.renderer.grid.template.location = 0;
categoryAxis.dataItems.template.text = "{year}";
/*categoryAxis.renderer.labels.template.rotation = 270;
categoryAxis.renderer.labels.template.horizontalCenter = "right";
categoryAxis.renderer.labels.template.verticalCenter = "middle";*/

categoryAxis.renderer.labels.template.adapter.add("dy", function(dy, target) {
  if (target.dataItem && target.dataItem.index & 2 == 2) {
    return dy + 25;
  }
  return dy;
});

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
valueAxis.renderer.labels.template.disabled = true;

// Create series
var series = chart.series.push(new am4charts.ColumnSeries());
series.dataFields.valueY = "word_ratio";
series.dataFields.categoryX = "year";
series.dataFields.year = "year";
series.dataFields.word = "word";
series.dataFields.primeMinister = "prime_minister_name";
series.name = "Words";
series.columns.template.tooltipText = "Word: [bold]{word}[/]\n{primeMinister}, {year}";
series.columns.template.fillOpacity = .8;
series.interpolationDuration = 1000;
series.interpolationEasing = am4core.ease.linear;

var columnTemplate = series.columns.template;
columnTemplate.strokeWidth = 2;
columnTemplate.strokeOpacity = 1;

var rangeTemplate = categoryAxis.axisRanges.template;
/*rangeTemplate.tick.disabled = false;
rangeTemplate.tick.location = 0;
rangeTemplate.tick.strokeOpacity = 0.6;
rangeTemplate.tick.length = 60;
rangeTemplate.grid.strokeOpacity = 0.5;*/
rangeTemplate.label.tooltip = new am4core.Tooltip();
rangeTemplate.label.tooltip.dy = -10;
rangeTemplate.label.cloneTooltip = false;

<?php foreach ($primeMinisterRanges as $primeMinisterId => $range) { ?>
    var range = categoryAxis.axisRanges.create();
    range.category = <?php echo "\"" . $range["start"] . "\""; ?>;
    range.endCategory = <?php echo "\"" . $range["end"] . "\""; ?>;;
    range.label.text = <?php
        $nameChunks = explode(" ", $range["name"]);
        echo "\"" . $nameChunks[0] . "\\n" . $nameChunks[1] . "\"";
    ?>;
    //range.label.dy = 30;
    range.label.truncate = true;
    range.label.fontWeight = "bold";

    <?php if ($range["name"] == "William McMahon" || $range["name"] == "John Gorton") { ?>
        range.label.fontSize = "10";
    <?php } ?>

    //range.label.rotation = 270;
    //range.label.tooltipText = <?php echo "\"" . $range["name"] . " (" . $range["start"] . "-" . $range["end"] . ")" . "\""; ?>;;
<?php } ?>

console.log(window);

}); // end am4core.ready()

console.log(window);
</script>

<!-- HTML -->
<div id="chartdiv"></div>

<button onclick="updateChartData('economy')">Economy</button>
<button onclick="updateChartData('world')">World</button>

</body>
</html>
