<?php
	require_once("../wordsOfInterest.php");
?>
<!DOCTYPE HTML>
<!--
	Urban by TEMPLATED
	templated.co @templatedco
	Released for free under the Creative Commons Attribution 3.0 license (templated.co/license)
-->
<html>
	<head>
		<title>Prime Figures of Speech :: GovHack 2020</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="assets/css/main.css" />

		<script src="assets/js/jquery.min.js"></script>

		<!-- Resources -->
		<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
		<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
		<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
		<script src="https://cdn.amcharts.com/lib/4/plugins/wordCloud.js"></script>

		<style>
			.chartdiv {
			  width: 100%;
			  height: 500px;
			  margin-top: -80px;
			  margin-bottom: 60px;
			}
		</style>

	</head>
	<body>

		<!-- Header -->
			<header id="header" class="alt">
				<div class="logo"><a href="index.html">Prime <span>Figures of Speech</span></a></div>
			</header>

		<!-- Nav -->
			<!--nav id="menu">
				<ul class="links">
					<li><a href="index.html">Home</a></li>
					<li><a href="generic.html">Generic</a></li>
					<li><a href="elements.html">Elements</a></li>
				</ul>
			</nav-->

		<!-- Banner -->
			<section id="banner">
				<div class="inner">
					<header>
						<h1>Prime Figures of Speech</h1>
						<p>An exploration of the key talking points in our country over the last 60 years,<br />
							expressed through the lens of language used by our political leaders.</p>
						<p>A project for GovHack 2020.</p>
					</header>
					<a href="#main" class="button big scrolly">Explore</a>
				</div>
			</section>

		<!-- Main -->
			<div id="main">

				<!-- Section -->
					<section class="wrapper style1">
						<div class="inner">
							<!-- 2 Columns -->
								<div class="flex flex-2">
									<div class="col col1">
										<div class="image round fit">
											<img src="images/pms2.jpg" alt="" />
										</div>
									</div>
									<div class="col col2">
										<h3>The language of leadership</h3>
										<p>Today more than ever we can see the power of political leadership to make or break a country through difficult times.</p>
										<p>Australia's prime minister is elected to serve our country and speak for our people. Their words should reflect both the sentiment of the people and the cultural themes of their time, as well as set a bold vision for the Australia we all want to create.</p>
										<p>This project performs a deep dive into tens of thousands of transcripts from prime ministers over the last 60 years. The resulting analysis gives a thought-provoking snapshot through time of the issues that have impacted us as a nation, and a rich genesis of some of the critical issues we face today.</p>
									</div>
								</div>
						</div>
					</section>

				<!-- Section -->
					<section class="wrapper style2">
						<div class="inner">
							<div class="flex flex-2">
								<div class="col col2">
									<h3>An overview</h3>
									<p>This is an entry for GovHack 2020. I used <a href="https://github.com/wragge/pm-transcripts">the PM Transcripts Respository</a> as my key data source. This source captures speeches, media releases, press conferences and other official transcripts for prime ministers.</p>
									<p>I performed significant data munging and cleansing to shape the data into a form where it could be easily consumed and understood in graphical form.</a>
									<p>The code for this project is at <a href="https://github.com/OnDistantShores/prime-figures-of-speech">a GitHub repository</a>. This includes the scripts used to manipulate the data, this website, and JSON extracts of the summarised data I created, available to others for further analysis. My competition entry page is <a href="https://hackerspace.govhack.org/team_management/teams/1303">on the GovHack site</a>.</p>
									<p>Below are some samples of the analysis I created, for your browsing! Please note that due to the size of some of these charts, this site is best viewed on a desktop or laptop device.</p>
								</div>
								<div class="col col1 first">
									<div class="image round fit">
										<img src="images/pms1.jpg" alt="" />
									</div>
								</div>
							</div>
						</div>
					</section>

				<!-- Section -->
					<section class="wrapper style1">
						<div class="inner">
							<header class="align-center">
								<h2>These are a few of my favourite words</h2>
								<p>This chart shows the most commonly used word by prime minister by year. The size of the bar indicates the level of focus on that term during that year.</p>
							</header>
						</div>
					</section>

					<?php

					    $allData = json_decode(file_get_contents("../generated_data/topWordsByYear.json"), true);

					    $minYear = 1959;

					    $primeMinisterRanges = array();
					    $topWordPerYearData = array();
					    $count = 0;
					    foreach ($allData as $entry) {
					        $key = $entry["year"] . $entry["prime_minister_id"];
					        if ($entry["year"] >= $minYear && !isset($topWordPerYearData[$key])) {
					            $topWordPerYearData[$key] = $entry;

					            // Cast year variable type to prevent display weirdness
					            $topWordPerYearData[$key]["year"] = (string)$topWordPerYearData[$key]["year"];

					            // We need to be able to show the same year twice, when PMs change over
					            $yearCategoryIdentifier = (string)($topWordPerYearData[$key]["year"] . $count);
					            $topWordPerYearData[$key]["yearCategory"] = $yearCategoryIdentifier;
					            $count++;

					            // Track when each PM started & ended, for the range labels
					            if (!isset($primeMinisterRanges[$entry["prime_minister_id"]])) {
					                $primeMinisterRanges[$entry["prime_minister_id"]] = array(
					                    "name" => $entry["prime_minister_name"],
					                    "start" => $yearCategoryIdentifier
					                );
					            }
					            $primeMinisterRanges[$entry["prime_minister_id"]]["end"] = $yearCategoryIdentifier;
					        }
					    }

					    $topWordPerYearData = array_values($topWordPerYearData);

					?>

					<!-- Chart code -->
					<script>
					am4core.ready(function() {

					// Themes begin
					am4core.useTheme(am4themes_animated);
					// Themes end

					// Create chart instance
					var chart = am4core.create("topWordByYear", am4charts.XYChart);

					// Add data
					/*chart.data = [{
					  "country": "USA",
					  "visits": 2025,
					  "word": "yeehar"
					}];*/
					chart.data = <?php echo json_encode($topWordPerYearData) ?>;

					// Create axes

					var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
					categoryAxis.dataFields.category = "yearCategory";
					categoryAxis.renderer.minGridDistance = 30;
					/*categoryAxis.renderer.grid.template.location = 0;*/
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
					series.dataFields.categoryX = "yearCategory";
					series.dataFields.year = "year";
					series.dataFields.word = "word";
					series.dataFields.primeMinister = "prime_minister_name";
					series.name = "Words";
					series.columns.template.tooltipText = "Word: [bold]{word}[/]\n{primeMinister}, {year}";
					series.columns.template.fillOpacity = .8;

					var columnTemplate = series.columns.template;
					columnTemplate.strokeWidth = 2;
					columnTemplate.strokeOpacity = 1;

					let label = series.columns.template.createChild(am4core.Label);
					label.text = "{word}";
					label.align = "center";
					label.valign = "middle";
					label.zIndex = 2;
					label.fill = am4core.color("#000");
					label.strokeWidth = 0;
					label.rotation = 270;

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


					}); // end am4core.ready()
					</script>

					<!-- HTML -->
					<div class="chartdiv" id="topWordByYear"></div>

				<!-- Section -->
					<section class="wrapper style1">
						<div class="inner">
							<header class="align-center">
								<h2>Evolution, visualised</h2>
								<p>This chart shows the evolution of the most commonly used word by prime minister by year. Watch the change unfold, or pause the animation to delve deeper.</p>
							</header>
						</div>
					</section>

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

					<!-- Chart code -->
					<script>
					am4core.ready(function() {

					// Themes begin
					am4core.useTheme(am4themes_animated);
					// Themes end

					var chart = am4core.create("topWordsEvolution", am4charts.XYChart);
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
					<div class="chartdiv" id="topWordsEvolution"></div>

					<!-- Section -->
					<section class="wrapper style1">
						<div class="inner">
							<header class="align-center">
								<h2>Trace a theme</h2>
								<p>Select a term from the list below to explore the popularity of that theme over time.</p>
								<p><?php

								$sep = "";
								foreach ($wordsOfInterest as $mixed) {
									$word = null;
							        if (is_array($mixed)) {
							            $word = $mixed[0];
							        }
							        else {
							            $word = $mixed;
							        }

									//echo $sep . " <a onclick='updateChartData(\"" . $word . "\")'>" . $word . "</a> ";
									echo $sep . " <a class='themeLink'>" . $word . "</a> ";
									$sep = "|";
							    }

								?></p>
								<h3 class="themeHeading">Showing: <span></span></h3>
							</header>
						</div>
					</section>

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

					<!-- Chart code -->
					<script>
					am4core.ready(function() {

					// Themes begin
					am4core.useTheme(am4themes_animated);
					// Themes end

					// Create chart instance
					var chart = am4core.create("themeTrace", am4charts.XYChart);

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

					updateChartData("economy");

					// Create axes

					var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
					categoryAxis.dataFields.category = "year";
					categoryAxis.renderer.minGridDistance = 30;
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

					}); // end am4core.ready()

					</script>

					<!-- HTML -->
					<div class="chartdiv" id="themeTrace"></div>

					<!-- Section -->
					<section class="wrapper style1">
						<div class="inner">
							<header class="align-center">
								<h2>Get to know your favourite PM</h2>
								<p>See a word cloud for each prime minister, with the size of the word indicating the frequency of usage.</p>
							</header>
						</div>
					</section>

					<?php

					    $allData = json_decode(file_get_contents("../generated_data/topWordsByPrimeMinister.json"), true);

					    $primeMinisterIds = array(17, 12, 9, 8, 4, 14, 6, 15, 13, 11, 7, 3);
						foreach ($primeMinisterIds as $primeMinisterId) {
							$primeMinisterName = null;

						    $relevantData = array();
						    $fullTextString = "";
						    foreach ($allData as $entry) {
						        if ($entry["prime_minister_id"] == $primeMinisterId) {
						            $relevantData[] = $entry;

						            $fullTextString .= str_repeat($entry["word"] . " ", $entry["word_count"]);

									$primeMinisterName = $entry["prime_minister_name"];
						        }
						    }

					?>

						<h3 class="primeMinisterName"><?php echo $primeMinisterName; ?></h3>

						<!-- Chart code -->
						<script>
						am4core.ready(function() {

						// Themes begin
						am4core.useTheme(am4themes_animated);
						// Themes end

						var chart = am4core.create("wordCloud<?php echo $primeMinisterId; ?>", am4plugins_wordCloud.WordCloud);
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
						<div class="chartdiv wordCloud" id="wordCloud<?php echo $primeMinisterId; ?>"></div>
					<?php } ?>

			</div>

	<!-- Section -->
		<section class="wrapper style2">
			<div class="inner">
				<div class="flex flex-2">
					<div class="col col2">
						<h3>FAIR principles</h3>
						<p>For this project, the most important aspects of the data I leveraged was that it as <strong>Accessible</strong> and <strong>Interoperable</strong>.</p>
						<p>I found the data to be highly <strong>Accessible</strong>, as it was simply stored in a GitHub repository. This made access a breeze, and far easier than it might be on some of the other official sources which require complex and slow ordering processes. It was very important that I could just download the source files directly in my browser, with no wait times.</p>
						<p>The data was <strong>Interoperable</strong> in the sense that it was in a standard XML format, making manipulation easy. However the data format was not well documented, and the format that I could discern was not always reliably adhered to, which caused some headaches.</p>
						<p>Initially I searched for some other complimentary data around the prime minister transcripts, but I found this hard to find. The <a href="https://pmtranscripts.pmc.gov.au/">PM Transcripts</a> site was far harder to use than the source I used. So data in this sense was not very <strong>Findable</strong>.</p>
					</div>
					<div class="col col1 first">
						<div class="image round fit">
							<img src="images/pms4.jpg" alt="" />
						</div>
					</div>
				</div>
			</div>
		</section>

			<!-- Section -->
				<section class="wrapper style1">
					<div class="inner">
						<!-- 2 Columns -->
							<div class="flex flex-2">
								<div class="col col1">
									<div class="image round fit">
										<img src="images/pms5.jpg" alt="" />
									</div>
								</div>
								<div class="col col2">
									<h3>Links & references</h3>
									<p><ul>
										<li><a href="https://hackerspace.govhack.org/team_management/teams/1303">GovHack 2020 project page</a></li>
										<li><a href="https://github.com/OnDistantShores/prime-figures-of-speech">This project's GitHub page</a></li>
										<li><a href="https://github.com/wragge/pm-transcripts">PM Transcripts Respository</a>, my key data source</li>
										<li><a href="https://templated.co/urban">The open source webpage template used</a></li>
										<li><a href="https://www.amcharts.com/">AM Charts</a>, the library used for these charts</li>
										<li><a href="https://github.com/dwyl/english-words">English words repository</a>, to filter out valid words to analyse</li>
										<li><a href="https://gist.github.com/keithmorris/4155220">A function used to strip out "everyday" words</a></li>
									</ul></p>
								</div>
							</div>
					</div>
				</section>

		<!-- Section -->
			<section class="wrapper style2">
				<div class="inner">
					<div class="flex flex-2">
						<div class="col col2">
							<h3>Detailed notes</h3>
							<p><ul>
								<li>I took a number of steps to ensure the words displayed were highly relevant. I first stripped out "everyday" words (e.g. a, the, of, etc) as they added no analysis value. The resulting data contained more meaningful words, but still words that didn't add much to the evolving historical view I was trying to build (e.g. the leading words were "people", "australia", "strong", etc). So I then created my own list of key important terms that I looked for and analysed in more detail in the above graphs. This list can be seen in the theme tracing section.</li>
								<li>I took some liberties with the data to ensure a clean result. For example, I ignored Rudd's second term of 2 months as it made that year's data very confusing. I also ignored transcripts outside of term as these were not issued as a prime minister. I also ignored years for a prime minister if they had less than 100 transcripts in that year, to ensure significance - for example, Paul Keating only had 7 transcripts in 1991 as he was only prime minister for the last 11 days of the year.</li>
								<li>The value depicted on all graphs is the "word ratio", calculated as the total instances of that term divided by the total instances of all "important terms".</li>
								<li>Technology-wise, I have created all data analysis scripts and this site using PHP, and stored the data in a MySQL database.</li>
							</ul></p>
						</div>
						<div class="col col1 first">
							<div class="image round fit">
								<img src="images/pms6.jpg" alt="" />
							</div>
						</div>
					</div>
				</div>
			</section>

		<!-- Footer -->
			<footer id="footer">
				<div class="copyright">
					<p>&copy; Cameron Ross. All rights reserved. Design: <a href="https://templated.co">TEMPLATED</a>. Images: <a href="https://unsplash.com">Unsplash</a>.</p>
				</div>
			</footer>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.scrolly.min.js"></script>
			<script src="assets/js/jquery.scrollex.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

			<script>
				$(document).ready(function() {
					$(".themeLink")
						.click(function(event, element) {
							$(".themeLink").removeClass("themeLinkSelected");
							$(this).addClass("themeLinkSelected");

							updateChartData($(this).text());

							$(".themeHeading span").text($(this).text());
						});
					$(".themeLink:contains('economy')").click();
				});
			</script>
			<style type="text/css">
				a.themeLink {
					cursor: pointer;
					font-weight: normal;
				}
				a.themeLinkSelected {
					font-weight: bold;
					text-decoration: underline;
					cursor: default;
				}

				h3.themeHeading span {
					font-weight: bold;
					text-decoration: underline;
				}

				h3.primeMinisterName {
					margin-top: 10px;
					margin-bottom: 70px;
					text-align: center;
				}

				div.wordCloud {
					width: 80%;
					height: 400px;
					margin-left: auto;
					margin-right: auto;
				}
			</style>

	</body>
</html>
