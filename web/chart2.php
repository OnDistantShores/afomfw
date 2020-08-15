<?php

    $allData = json_decode(file_get_contents("../generated_data/topWordsByYear.json"), true);

    $minYear = 1959;

    $allWords = array();
    foreach ($allData as $entry) {
        if (!in_array($entry["word"], $allWords)) {
            $allWords[] = $entry["word"];
        }
    }

    //$primeMinisterRanges = array();
    $dataIndexedByYear = array();
    foreach ($allData as $entry) {

        if (!isset($dataIndexedByYear[$entry["year"]])) {
            $dataIndexedByYear[$entry["year"]] = array();
            foreach ($allWords as $word) {
                $dataIndexedByYear[$entry["year"]][] = array(
                    "word" => $word,
                    "word_ratio" => 0
                );
            }
        }

        foreach ($dataIndexedByYear[$entry["year"]] as &$yearlyData) {
            if ($yearlyData["word"] == $entry["word"]) {
                $yearlyData["word_ratio"] = $entry["word_ratio"];
            }
        }
    }

?>
<html>
<head>
<title>Chart 2</title>
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

var chart = am4core.create("chartdiv", am4charts.XYChart);
chart.padding(40, 40, 40, 40);

chart.numberFormatter.bigNumberPrefixes = [
  { "number": 1e+3, "suffix": "K" },
  { "number": 1e+6, "suffix": "M" },
  { "number": 1e+9, "suffix": "B" }
];

var label = chart.plotContainer.createChild(am4core.Label);
label.x = am4core.percent(97);
label.y = am4core.percent(95);
label.horizontalCenter = "right";
label.verticalCenter = "middle";
label.contentAlign = "right";
label.dx = -30;
label.fontSize = 40;

var playButton = chart.plotContainer.createChild(am4core.PlayButton);
playButton.x = am4core.percent(97);
playButton.y = am4core.percent(95);
playButton.dy = -2;
playButton.verticalCenter = "middle";
playButton.events.on("toggled", function(event) {
  if (event.target.isActive) {
    play();
  }
  else {
    stop();
  }
})

var stepDuration = 4000;

var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
categoryAxis.renderer.grid.template.location = 0;
categoryAxis.dataFields.category = "word";
categoryAxis.renderer.minGridDistance = 1;
categoryAxis.renderer.inversed = true;
categoryAxis.renderer.grid.template.disabled = true;

var valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
valueAxis.min = 0;
valueAxis.rangeChangeEasing = am4core.ease.linear;
valueAxis.rangeChangeDuration = stepDuration;
valueAxis.extraMax = 0.1;
valueAxis.renderer.labels.template.disabled = true;

var series = chart.series.push(new am4charts.ColumnSeries());
series.dataFields.categoryY = "word";
series.dataFields.valueX = "word_ratio";
series.tooltipText = "{valueX.value}"
series.columns.template.strokeOpacity = 0;
series.columns.template.column.cornerRadiusBottomRight = 5;
series.columns.template.column.cornerRadiusTopRight = 5;
series.interpolationDuration = stepDuration;
series.interpolationEasing = am4core.ease.linear;

chart.zoomOutButton.disabled = true;

// as by default columns of the same series are of the same color, we add adapter which takes colors from chart.colors color set
series.columns.template.adapter.add("fill", function(fill, target){
  return chart.colors.getIndex(target.dataItem.index);
});

var year = 1959;
label.text = year.toString() + "\n" + getPrimeMinisterByYear(year);

var interval;

function play() {
  interval = setInterval(function(){
    nextYear();
  }, stepDuration)
  nextYear();
}

function stop() {
  if (interval) {
    clearInterval(interval);
  }
}

function nextYear() {
  year++

  if (year > 2018) {
    year = 1959;
  }

  var newData = allData[year];
  for (var i = 0; i < chart.data.length; i++) {
    chart.data[i].word_ratio = 0;
  }
  for (var i = 0; i < newData.length; i++) {
      for (var j = 0; j < chart.data.length; j++) {
          if (newData[i].word == chart.data[j].word) {
              chart.data[j].word_ratio = newData[i].word_ratio;
          }
      }
  }

  /*console.log(chart.data);
  chart.data.sort(function(a, b) {
      if (a.word_ratio > b.word_ratio) {
          return -1;
      }
      else if (b.word_ratio > a.word_ratio) {
          return 1;
      }
      return 0;
  });
  console.log(chart.data);*/


  if (year == 1959) {
    series.interpolationDuration = stepDuration / 4;
    valueAxis.rangeChangeDuration = stepDuration / 4;
  }
  else {
    series.interpolationDuration = stepDuration;
    valueAxis.rangeChangeDuration = stepDuration;
  }

  chart.invalidateRawData();
  label.text = year.toString() + "\n" + getPrimeMinisterByYear(year);

  categoryAxis.zoom({ start: 0, end: 0.1333 });
}

function getPrimeMinisterByYear(year) {
    var data = <?php

        $primeMinistersByYear = array();
        foreach ($allData as $entry) {
            if (!isset($primeMinistersByYear[$entry["year"]])) {
                $primeMinistersByYear[$entry["year"]] = array();
            }

            $nameChunks = explode(" ", $entry["prime_minister_name"]);

            if (!in_array($nameChunks[1], $primeMinistersByYear[$entry["year"]])) {
                $primeMinistersByYear[$entry["year"]][] = $nameChunks[1];
            }
        }

        foreach ($primeMinistersByYear as $year => &$names) {
            $names = implode(" & ", $names);
        }

        echo json_encode($primeMinistersByYear);

     ?>;

     return data[year];
}

categoryAxis.sortBySeries = series;

var allData = <?php echo json_encode($dataIndexedByYear) ?>;

chart.data = JSON.parse(JSON.stringify(allData[year]));
categoryAxis.zoom({ start: 0, end: 0.1333 });

series.events.on("inited", function() {
  setTimeout(function() {
    playButton.isActive = true; // this starts interval
  }, 2000)
})

}); // end am4core.ready()
</script>

<!-- HTML -->
<div id="chartdiv"></div>

</body>
</html>
