<?php
	date_default_timezone_set('Australia/Melbourne');

	//all the good stuff
	require_once 'functions.php';

	//here we go

	//config
	$mainurl = 'http://cgwizz.com/tf-insights/api/v1/';
//	$mainurl = 'http://tf-insights.localhost/api/v1/';

	//our themes that we want displayed
	$ouritems = array(
		'senna' => array (
			'id' => '4609270',
			'name' => 'Senna',
		),
		'fuse' => array (
			'id' => '5136837',
			'name' => 'Fuse',
		),
		'cityhub' => array (
			'id' => '5425258',
			'name' => 'CityHub',
		),
		'bliv' => array (
			'id' => '4141443',
			'name' => 'B:Liv',
		),
		'salient' => array (
			'id' => '4363266',
			'name' => 'Salient',
		),
	);

	//all time stats
	// get all the entries info
	$entries = grab_data_from_url($mainurl.'items?all');

	//get all authors
	$authors = grab_data_from_url($mainurl.'authors?all');
	// var_dump($authors[0]);
	$total_authors = count($authors);
	$total_authors_sales = mysum($authors,'level','','sales');

	$totals = grab_data_from_url($mainurl.'items?totals');
//	 var_dump($totals);

	//grab items by price
	$totals_35 = grab_data_from_url($mainurl.'items?totals&cost=35');
	$totals_40 = grab_data_from_url($mainurl.'items?totals&cost=40');
	$totals_45 = grab_data_from_url($mainurl.'items?totals&cost=45');
	$totals_50 = grab_data_from_url($mainurl.'items?totals&cost=50');
	$totals_55 = grab_data_from_url($mainurl.'items?totals&cost=55');
	$totals_60 = grab_data_from_url($mainurl.'items?totals&cost=60');

	//determine the day of the week with most accepted themes
	$common_acceptance_day = grab_data_from_url($mainurl.'items?common_acceptance_day');
	//we use mysql WEEKDAY() to get the day so its 0=Monday..6=Sunday numbered
	$dowMap = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

	//last 30 days stats
	$themes_accepted_30days = grab_data_from_url($mainurl.'items?accepted=30&all');

	$total_sales_30days = mysum($themes_accepted_30days,'category_name','','sales');
	$total_themes_30days = count($themes_accepted_30days);
	$total_income_30days = myincome($themes_accepted_30days,'category_name','');

	//get all authors
	$authors_1monthago = grab_data_from_url($mainurl.'authors?all&date='.strtotime("-1 month"));
	$authors_now = $authors;
	foreach ($authors_1monthago as $key => $item) {
		if ($authors_now[$key]['sales'] == $item['sales']) {
			unset($authors_now[$key]);
			unset($authors_1monthago[$key]);
		}
	}
	$total_authors_now = count($authors_now);
	$total_authors_1monthago = count($authors_1monthago);
	$total_authors_sales_lastmonth = mysum($authors_now,'level','','sales') - mysum($authors_1monthago,'level','','sales');

	$thisweek = array(); //store the data bot our themes for this week
	$this_week_range = x_week_range(date("Y-m-d H:i:s"));

	$lastweek = array();
	$last_week_range = x_week_range("Tuesday last week");

	//set this week's statistics
	if (date('w', time()) == 0) { //today is Sunday(first week day) so no data yet

	} else {
		foreach ($ouritems as $key => $item) {
			//grab the sales prior to the start of this week
			$tempstats = grab_data_from_url($mainurl.'items?itemid='.$item['id'].'&date='.strtotime('-1 day',strtotime($this_week_range[0])));

			//substract them from the sales to the day
			$thisweek[$key] = grab_data_from_url($mainurl.'items?itemid='.$item['id'])['sales'];
			$thisweek[$key] -= $tempstats['sales'];
		}
	}

	//set last weeks statistics
	foreach ($ouritems as $key => $item) {
		//grab the sales prior to the start of last week
		$tempstats = grab_data_from_url($mainurl.'items?itemid='.$item['id'].'&date='.strtotime('-1 day',strtotime($last_week_range[0])));

		//substract them from the sales to the day
		$lastweek[$key] = grab_data_from_url($mainurl.'items?itemid='.$item['id'].'&date='.strtotime($last_week_range[1]))['sales'];
		$lastweek[$key] -= $tempstats['sales'];
	}

	//items sales 30 days ago
	//$items_sales_30daysago = grab_data_from_url($mainurl.'items?salesonly=on&date='.strtotime('-30 days'));
	//var_dump($items_sales_30daysago);
	//stats for tags

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>ThemeForest Insights - Pixelgrade</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style type="text/css">
body {
	padding-top: 20px;
	padding-bottom: 60px;
}
/* Custom container */
.container {
	margin: 0 auto;
	max-width: 1300px;
}
.container > hr {
	margin: 60px 0;
}
/* Main marketing message and sign up button */
.jumbotron {
	margin: 80px 0;
	text-align: center;
}
.jumbotron h1 {
	font-size: 100px;
	line-height: 1;
}
.jumbotron .lead {
	font-size: 24px;
	line-height: 1.25;
}
.jumbotron .btn {
	font-size: 21px;
	padding: 14px 24px;
}
/* Supporting marketing content */
.marketing {
	margin: 60px 0;
}
.marketing p + h4 {
	margin-top: 28px;
}
/* Customize the navbar links to be fill the entire space of the .navbar */
.navbar .navbar-inner {
	padding: 0;
}
.navbar .nav {
	margin: 0;
	display: table;
	width: 100%;
}
.navbar .nav li {
	display: table-cell;
	width: 1%;
	float: none;
}
.navbar .nav li a {
	font-weight: bold;
	text-align: center;
	border-left: 1px solid rgba(255,255,255,.75);
	border-right: 1px solid rgba(0,0,0,.1);
}
.navbar .nav li:first-child a {
	border-left: 0;
	border-radius: 3px 0 0 3px;
}
.navbar .nav li:last-child a {
	border-right: 0;
	border-radius: 0 3px 3px 0;
}
</style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="ico/favicon.png">
	<script src="js/jquery-2.0.3.min.js"></script>
    </head>

    <body>
<div class="container">
      <div class="masthead">
    <h4 class="alert alert-error pull-left">
    The FBI Files on ThemeForest
    </h4>
    <h4 class="alert pull-right">TOP SECRET DOCUMENT</h4>
  </div>


      <div class="jumbotron">
       <p class="lead">&nbsp;</p>
    <h1>ThemeForest Insights</h1>
	<p class="text-center">The current date and time: <?php echo date("D M d, Y G:i a"); ?> (Envato time - Australia/Melbourne)</p>
  </div>
      <hr>

      <h2 class="well">All time Stats</h2>
  <div class="row-fluid">
    <div class="span2">
          <h2>Ratings</h2>
          <h2 class="alert alert-info pagination-centered">1 / <?php echo round($totals['sales'] / $totals['votes']) ?></h2>
          <p><strong>1 Rating</strong> received for each <strong><?php echo round($totals['sales'] / $totals['votes']) ?> Sales</strong></p>
        </div>
    <div class="span2">
          <h2>Comments</h2>
          <h2 class="alert alert-info pagination-centered">1 / <?php echo round($totals['sales'] / $totals['comments']) ?></h2>
          <p><strong>1 Comment</strong> received for each <strong><?php echo round($totals['sales'] / $totals['comments']) ?> Sales</strong></p>
        </div>
    <div class="span5">
          <h2>Price Sales</h2>
          <div class="progress">
        <div class="bar" style="width: <?php echo round($totals_45['sales'] / $totals['sales'] * 100 )?>%;"><strong>$45 - </strong><?php echo round($totals_45['sales'] / $totals['sales'] * 100)?>%</div>
      </div>
          <div class="progress">
        <div class="bar" style="width: <?php echo round($totals_40['sales'] / $totals['sales'] * 100 )?>%;"> <strong>$40 - </strong><?php echo round($totals_40['sales'] / $totals['sales'] * 100 )?>%</div>
      </div>
      <div class="progress">
        <div class="bar" style="width: <?php echo round($totals_35['sales'] / $totals['sales'] * 100 )?>%;"> <strong>$35 - </strong><?php echo round($totals_35['sales'] / $totals['sales'] * 100 )?>%</div>
      </div>
	   <div class="progress">
        <div class="bar" style="width: <?php echo round($totals_50['sales'] / $totals['sales'] * 100 )?>%;"> <strong>$50 - </strong><?php echo round($totals_50['sales'] / $totals['sales'] * 100 )?>%</div>
      </div>
		  <div class="progress">
        <div class="bar" style="width: <?php echo round($totals_55['sales'] / $totals['sales'] * 100 )?>%;"> <strong>$55 - </strong><?php echo round($totals_55['sales'] / $totals['sales'] * 100 )?>%</div>
      </div>
		  <div class="progress">
        <div class="bar" style="width: <?php echo round($totals_60['sales'] / $totals['sales'] * 100 )?>%;"> <strong>$60 - </strong><?php echo round($totals_60['sales'] / $totals['sales'] * 100 )?>%</div>
      </div>
        </div>
    <div class="span3">
          <h2>Fun Fact</h2>
          <h2 class="alert alert-info pagination-centered"><?php echo $dowMap[$common_acceptance_day['day']] ?> <?php echo round($common_acceptance_day['count'] / count($entries) * 100) ?>%</h2>
          <p>The <strong>day of the week </strong>with <strong>most accepted themes.</strong></p>
        </div>
  </div>
	<div class="row-fluid">
   	<div class="span4">
		<h2>Authors Number</h2>
		<strong>Power Elite Authors </strong>
		<div class="progress">
			<div class="bar" style="width: <?php echo round(mycount($authors,'level','Power Elite', true) / $total_authors * 100) ?>%;"><?php echo mycount($authors,'level','Power Elite', true) ?></div>
		</div>
		<strong>Elite Authors </strong>
		<div class="progress">
			<div class="bar" style="width: <?php echo round(mycount($authors,'level','Elite', true) / $total_authors * 100) ?>%;"><?php echo mycount($authors,'level','Elite', true) ?></div>
		</div>
		<strong>Regular Authors</strong>
		<div class="progress">
			<div class="bar" style="width: <?php echo round(mycount($authors,'level','Regular', true) / $total_authors * 100) ?>%;"><?php echo mycount($authors,'level','Regular', true) ?></div>
		</div>
    </div>
	<div class="span4">
		<h2>Authors Sales</h2>
		<strong>Power Elite Authors </strong><small> <span class="label"><?php echo round(mysum($authors,'level','Power Elite', 'sales', true) / mycount($authors,'level','Power Elite', 'sales', true)) ?></span>  Sales per Author (average)</small>
		<div class="progress">
			<div class="bar  bar-warning" style="width: <?php echo round(mysum($authors,'level','Power Elite', 'sales', true) / $total_authors_sales * 100) ?>%;"><?php echo mysum($authors,'level','Power Elite', 'sales', true) ?></div>
		</div>
		<strong>Elite Authors </strong><small> <span class="label"><?php echo round(mysum($authors,'level','Elite', 'sales', true) / mycount($authors,'level','Elite', true)) ?></span></small>
		<div class="progress">
			<div class="bar  bar-warning" style="width: <?php echo round(mysum($authors,'level','Elite', 'sales', true) / $total_authors_sales * 100) ?>%;"><?php echo mysum($authors,'level','Elite', 'sales', true) ?></div>
		</div>
		<strong>Regular Authors</strong><small> <span class="label"><?php echo round(mysum($authors,'level','Regular', 'sales', true) / mycount($authors,'level','Regular', true)) ?></span></small>
		<div class="progress">
			<div class="bar  bar-warning" style="width: <?php echo round(mysum($authors,'level','Regular', 'sales', true) / $total_authors_sales * 100) ?>%;"><?php echo mysum($authors,'level','Regular', 'sales', true) ?></div>
		</div>
	</div>
        <div class="span4">
		<h2>Authors Income</h2>
		<strong>Power Elite Authors </strong><small> <span class="label">$<?php echo round(mysum($authors,'level','Power Elite', 'income', true) / mycount($authors,'level','Power Elite', true) * 0.7) ?></span>  Based on 70% Rate(average)</small>
		<div class="progress">
			<div class="bar  bar-success" style="width: <?php echo round(mysum($authors,'level','Power Elite', 'income', true) / mysum($authors,'level','', 'income') * 100) ?>%;"> $<?php echo round(mysum($authors,'level','Power Elite', 'income', true) * 0.7) ?></div>
		</div>
		<strong>Elite Authors </strong><small> <span class="label">$<?php echo round(mysum($authors,'level','Elite', 'income', true) / mycount($authors,'level','Elite', true) * 0.7) ?></span>  Based on 70% Rate(average)</small>
		<div class="progress">
			<div class="bar  bar-success" style="width: <?php echo round(mysum($authors,'level','Elite', 'income', true) / mysum($authors,'level','', 'income') * 100) ?>%;"> $<?php echo round(mysum($authors,'level','Elite', 'income', true) * 0.7) ?></div>
		</div>
		<strong>Regular Authors </strong><small> <span class="label">$<?php echo round(mysum($authors,'level','Regular', 'income', true) / mycount($authors,'level','Regular', true) * 0.6) ?></span>  Based on 60% Rate(average)</small>
		<div class="progress">
			<div class="bar  bar-success" style="width: <?php echo round(mysum($authors,'level','Regular', 'income', true) / mysum($authors,'level','', 'income') * 100) ?>%;"> $<?php echo round(mysum($authors,'level','Regular', 'income', true) * 0.6) ?></div>
		</div>

        </div>
  </div>
      <h2 class="well">Last 30 Days Stats <small>(<?php echo date('F dS', strtotime("-1 month")); ?> - <?php echo date('F dS'); ?>)</small></h2>

      <div class="row-fluid">
    <div class="span4">
          <h2>New Themes</h2>
          <strong>Creative - </strong>Portfolio
          <div class="progress">
        <div class="bar" style="width: <?php echo round(mycount($themes_accepted_30days,'category_slug','creative') / $total_themes_30days * 100) ?>%;"> <?php echo mycount($themes_accepted_30days,'category_slug','creative') ?></div>
      </div>
          <strong>Corporate</strong> - Business
          <div class="progress">
        <div class="bar" style="width: <?php echo round(mycount($themes_accepted_30days,'category_slug','corporate') / $total_themes_30days * 100) ?>%;"> <?php echo mycount($themes_accepted_30days,'category_slug','corporate') ?></div>
      </div>
          <strong>Blog/ Magazine</strong>
          <div class="progress">
        <div class="bar" style="width: <?php echo round(mycount($themes_accepted_30days,'category_slug','blog-magazine') / $total_themes_30days * 100) ?>%;"> <?php echo mycount($themes_accepted_30days,'category_slug','blog-magazine') ?></div>
      </div>
          <strong>eCommerce</strong>
          <div class="progress">
        <div class="bar" style="width: <?php echo round(mycount($themes_accepted_30days,'category_slug','ecommerce') / $total_themes_30days * 100) ?>%;"><?php echo mycount($themes_accepted_30days,'category_slug','ecommerce') ?></div>
      </div>
        </div>
    <div class="span4">
          <h2>New Theme Sales</h2>
		<strong>Creative</strong> <small>- <span class="label label-info"><?php echo round((mysum($themes_accepted_30days,'category_slug','creative','sales') / mycount($themes_accepted_30days,'category_slug','creative')),2) ?></span> Sales per Theme (average)</small>
		<div class="progress">
		  <div class="bar bar-warning" style="width: <?php echo round(mysum($themes_accepted_30days,'category_slug','creative','sales') / $total_sales_30days *100) ?>%;"> <?php echo mysum($themes_accepted_30days,'category_slug','creative','sales') ?> <span class="muted1"></span></div>
		</div>
        <strong>Corporate</strong> <small>- <span class="label label-info"><?php echo round((mysum($themes_accepted_30days,'category_slug','corporate','sales') / mycount($themes_accepted_30days,'category_slug','corporate')),2) ?></span></small>
		<div class="progress">
		  <div class="bar bar-warning" style="width: <?php echo round(mysum($themes_accepted_30days,'category_slug','corporate','sales') / $total_sales_30days *100) ?>%;"> <?php echo mysum($themes_accepted_30days,'category_slug','corporate','sales') ?> <span class="muted1"></span></div>
		</div>
		<strong>Blog / Magazine</strong> <small>- <span class="label label-info"><?php echo round((mysum($themes_accepted_30days,'category_slug','blog-magazine','sales') / mycount($themes_accepted_30days,'category_slug','blog-magazine')),2) ?></span></small>
		<div class="progress">
		  <div class="bar bar-warning" style="width: <?php echo round(mysum($themes_accepted_30days,'category_slug','blog-magazine','sales') / $total_sales_30days *100) ?>%;"> <?php echo mysum($themes_accepted_30days,'category_slug','blog-magazine','sales') ?> <span class="muted1"></span></div>
		</div>
		<strong>eCommerce</strong> <small>- <span class="label label-info"><?php echo round((mysum($themes_accepted_30days,'category_slug','ecommerce','sales') / mycount($themes_accepted_30days,'category_slug','ecommerce')),2) ?></span></small>
		<div class="progress">
		  <div class="bar bar-warning" style="width: <?php echo round(mysum($themes_accepted_30days,'category_slug','ecommerce','sales') / $total_sales_30days *100) ?>%;"> <?php echo mysum($themes_accepted_30days,'category_slug','ecommerce','sales') ?> <span class="muted1"></span></div>
		</div>
        </div>
    <div class="span4">
          <h2>New Themes Income <small>Based on 60% rate</small></h2>
		<strong>Creative</strong> <small>- <span class="label label-info">$<?php echo round(myincome($themes_accepted_30days,'category_slug','creative') / mycount($themes_accepted_30days,'category_slug','creative') * 0.6) ?></span> income per Theme (average)</small>
		<div class="progress">
			<div class="bar bar-success" style="width: <?php echo round(myincome($themes_accepted_30days,'category_slug','creative') / $total_income_30days * 100) ?>%;"> $<?php echo round(myincome($themes_accepted_30days,'category_slug','creative') * 0.6) ?> <span class="muted1"></span></div>
		</div>
		<strong>Corporate</strong> <small>- <span class="label label-info">$<?php echo round(myincome($themes_accepted_30days,'category_slug','corporate') / mycount($themes_accepted_30days,'category_slug','corporate') * 0.6) ?></span></small>
		<div class="progress">
			<div class="bar bar-success" style="width: <?php echo round(myincome($themes_accepted_30days,'category_slug','corporate') / $total_income_30days * 100) ?>%;"> $<?php echo round(myincome($themes_accepted_30days,'category_slug','corporate') * 0.6) ?> <span class="muted1"></span></div>
		</div>
        <strong>Blog/ Magazine</strong> <small>- <span class="label label-info">$<?php echo round(myincome($themes_accepted_30days,'category_slug','blog-magazine') / mycount($themes_accepted_30days,'category_slug','blog-magazine') * 0.6) ?></span></small>
		<div class="progress">
			<div class="bar bar-success" style="width: <?php echo round(myincome($themes_accepted_30days,'category_slug','blog-magazine') / $total_income_30days * 100) ?>%;"> $<?php echo round(myincome($themes_accepted_30days,'category_slug','blog-magazine') * 0.6) ?> <span class="muted1"></span></div>
		</div>
        <strong>eCommerce</strong> <small>- <span class="label label-info">$<?php echo round(myincome($themes_accepted_30days,'category_slug','ecommerce') / mycount($themes_accepted_30days,'category_slug','ecommerce') * 0.6) ?></span></small>
		<div class="progress">
			<div class="bar bar-success" style="width: <?php echo round(myincome($themes_accepted_30days,'category_slug','ecommerce') / $total_income_30days * 100) ?>%;"> $<?php echo round(myincome($themes_accepted_30days,'category_slug','ecommerce') * 0.6) ?> <span class="muted1"></span></div>
		</div>
        </div>
  </div>
  <div span="8"><span>Only authors that have had at least a sale in the last 30 days</span></div>
  <div class="row-fluid">
	<div class="span4">
	<h2>Authors Number</h2>
		<strong>Power Elite Authors </strong>
		<div class="progress">
			<div class="bar" style="width: <?php echo round(mycount($authors_now,'level','Power Elite', true) / $total_authors_now * 100) ?>%;"><?php echo mycount($authors_now,'level','Power Elite', true) ?></div>
		</div>
		<strong>Elite Authors </strong>
		<div class="progress">
			<div class="bar" style="width: <?php echo round(mycount($authors_now,'level','Elite', true) / $total_authors_now * 100) ?>%;"><?php echo mycount($authors_now,'level','Elite', true) ?></div>
		</div>
		<strong>Regular Authors</strong>
		<div class="progress">
			<div class="bar" style="width: <?php echo round(mycount($authors_now,'level','Regular', true) / $total_authors_now * 100) ?>%;"><?php echo mycount($authors_now,'level','Regular', true) ?></div>
		</div>
	</div>
	<div class="span4">
		<h2>Authors Sales</h2>
		<strong>Power Elite Authors </strong><small> <span class="label"><?php echo round((mysum($authors_now,'level','Power Elite', 'sales', true) - mysum($authors_1monthago,'level','Power Elite', 'sales', true)) / mycount($authors,'level','Power Elite', true)) ?></span>  Sales per Author (average)</small>
		<div class="progress">
			<div class="bar  bar-warning" style="width: <?php echo round((mysum($authors_now,'level','Power Elite', 'sales', true) - mysum($authors_1monthago,'level','Power Elite', 'sales', true)) / $total_authors_sales_lastmonth * 100) ?>%;"><?php echo (mysum($authors_now,'level','Power Elite', 'sales', true) - mysum($authors_1monthago,'level','Power Elite', 'sales', true)) ?></div>
		</div>
		<strong>Elite Authors </strong><small> <span class="label"><?php echo round((mysum($authors_now,'level','Elite', 'sales', true) - mysum($authors_1monthago,'level','Elite', 'sales', true)) / mycount($authors,'level','Elite', true)) ?></span></small>
		<div class="progress">
			<div class="bar  bar-warning" style="width: <?php echo round((mysum($authors_now,'level','Elite', 'sales', true) - mysum($authors_1monthago,'level','Elite', 'sales', true)) / $total_authors_sales_lastmonth * 100) ?>%;"><?php echo (mysum($authors_now,'level','Elite', 'sales', true) - mysum($authors_1monthago,'level','Elite', 'sales', true)) ?></div>
		</div>
		<strong>Regular Authors </strong><small> <span class="label"><?php echo round((mysum($authors_now,'level','Regular', 'sales', true) - mysum($authors_1monthago,'level','Regular', 'sales', true)) / mycount($authors,'level','Regular', true)) ?></span></small>
		<div class="progress">
			<div class="bar  bar-warning" style="width: <?php echo round((mysum($authors_now,'level','Regular', 'sales', true) - mysum($authors_1monthago,'level','Regular', 'sales', true)) / $total_authors_sales_lastmonth * 100) ?>%;"><?php echo (mysum($authors_now,'level','Regular', 'sales', true) - mysum($authors_1monthago,'level','Regular', 'sales', true)) ?></div>
		</div>
	</div>
        <div class="span4">
		<h2>Authors Income</h2>
		<strong>Power Elite Authors </strong><small> <span class="label">$<?php echo round((mysum($authors_now,'level','Power Elite', 'income', true) - mysum($authors_1monthago,'level','Power Elite', 'income', true)) / mycount($authors_now,'level','Power Elite', true) * 0.7) ?></span>  Based on 70% Rate(average)</small>
		<div class="progress">
			<div class="bar  bar-success" style="width: <?php echo round((mysum($authors_now,'level','Power Elite', 'income', true) - mysum($authors_1monthago,'level','Power Elite', 'income', true)) / (mysum($authors_now,'level','', 'income') - mysum($authors_1monthago,'level','', 'income')) * 100) ?>%;"> $<?php echo round((mysum($authors_now,'level','Power Elite', 'income', true) - mysum($authors_1monthago,'level','Power Elite', 'income', true)) * 0.7) ?></div>
		</div>
		<strong>Elite Authors </strong><small> <span class="label">$<?php echo round((mysum($authors_now,'level','Elite', 'income', true) - mysum($authors_1monthago,'level','Elite', 'income', true)) / mycount($authors_now,'level','Elite', true) * 0.7) ?></span>  Based on 70% Rate(average)</small>
		<div class="progress">
			<div class="bar  bar-success" style="width: <?php echo round((mysum($authors_now,'level','Elite', 'income', true) - mysum($authors_1monthago,'level','Elite', 'income', true)) / (mysum($authors_now,'level','', 'income') - mysum($authors_1monthago,'level','', 'income')) * 100) ?>%;"> $<?php echo round((mysum($authors_now,'level','Elite', 'income', true) - mysum($authors_1monthago,'level','Elite', 'income', true)) * 0.7) ?></div>
		</div>
		<strong>Regular Authors </strong><small> <span class="label">$<?php echo round((mysum($authors_now,'level','Regular', 'income', true) - mysum($authors_1monthago,'level','Regular', 'income', true)) / mycount($authors_now,'level','Regular', true) * 0.6) ?></span>  Based on 60% Rate(average)</small>
		<div class="progress">
			<div class="bar  bar-success" style="width: <?php echo round((mysum($authors_now,'level','Regular', 'income', true) - mysum($authors_1monthago,'level','Regular', 'income', true)) / (mysum($authors_now,'level','', 'income') - mysum($authors_1monthago,'level','', 'income')) * 100) ?>%;"> $<?php echo round((mysum($authors_now,'level','Regular', 'income', true) - mysum($authors_1monthago,'level','Regular', 'income', true)) * 0.6) ?></div>
		</div>
        </div>
  </div>
   <hr>
   <div class="row-fluid">
   	<div class="span3">
		<?php
			$count = mycountadv($themes_accepted_30days, ['tags' => ['responsive'],'item' => ['Responsive']]);
			$total_count = count($themes_accepted_30days);
			$percent = round($count / $total_count * 100 );
			$sales = mysumadv($themes_accepted_30days, ['tags' => ['responsive'],'item' => ['Responsive']],'sales');
			$sales_avg = round($sales / $count);
			$income = round (myincomeadv($themes_accepted_30days, ['tags' => ['responsive'],'item' => ['Responsive']]) * 0.6);
			$income_avg = round($income / $count * 0.6);
		?>
		<h3>Responsive</h3>
		<div class="progress">
			<div class="bar" style="width: <?php echo $percent ?>%;"><?php echo $count ?> / <?php echo $total_count ?> themes</div>
		</div>
		<div class="alert alert-info pagination-centered"><strong><?php echo $percent ?>%</strong> - <strong><?php echo $sales ?></strong> s. (<?php echo $sales_avg ?> av) -<strong> $<?php echo $income_avg ?></strong> av</div>
		<p><strong><?php echo $percent ?>% </strong>of themes added last 30 days are <strong>responsive</strong> with<strong> <?php echo $sales ?> sales (<?php echo $sales_avg ?> avg)</strong>and <strong>$<?php echo $income_avg ?></strong> income (avg) with 60% rate.</p>
	</div>
	<div class="span3">
		<?php
			$count = mycountadv($themes_accepted_30days, ['tags' => ['localization','wpml','multilingual','translation']]);
			$total_count = count($themes_accepted_30days);
			$percent = round($count / $total_count * 100 );
			$sales = mysumadv($themes_accepted_30days, ['tags' => ['localization','wpml','multilingual','translation']],'sales');
			$sales_avg = round($sales / $count);
			$income = round (myincomeadv($themes_accepted_30days, ['tags' => ['localization','wpml','multilingual','translation']]) * 0.6);
			$income_avg = round($income / $count * 0.6);
		?>
		<h3>Localization ready</h3>
		<div class="progress">
			<div class="bar" style="width: <?php echo $percent ?>%;"><?php echo $count ?> / <?php echo $total_count ?> themes</div>
		</div>
		<div class="alert alert-info pagination-centered"><strong><?php echo $percent ?>%</strong> - <strong><?php echo $sales ?></strong> s. (<?php echo $sales_avg ?> av) -<strong> $<?php echo $income_avg ?></strong> av</div>
		<p><strong><?php echo $percent ?>% </strong>of themes added last 30 days are <strong>localization ready</strong> with<strong> <?php echo $sales ?> sales (<?php echo $sales_avg ?> avg)</strong>and <strong>$<?php echo $income_avg ?></strong> income (avg) with 60% rate.</p>
	</div>
    <div class="span3">
		<?php
			$count = mycount($themes_accepted_30days, 'tags', 'parallax');
			$total_count = count($themes_accepted_30days);
			$percent = round($count / $total_count * 100 );
			$sales = mysum($themes_accepted_30days, 'tags', 'parallax','sales');
			$sales_avg = round($sales / $count);
			$income = round (myincome($themes_accepted_30days, 'tags', 'parallax') * 0.6);
			$income_avg = round($income / $count * 0.6);
		?>
		<h3>Parallax</h3>
		<div class="progress">
			<div class="bar" style="width: <?php echo $percent ?>%;"><?php echo $count ?> / <?php echo $total_count ?> themes</div>
		</div>
		<div class="alert alert-info pagination-centered"><strong><?php echo $percent ?>%</strong> - <strong><?php echo $sales ?></strong> s. (<?php echo $sales_avg ?> av) -<strong> $<?php echo $income_avg ?></strong> av</div>
		<p><strong><?php echo $percent ?>% </strong>of themes added last 30 days are <strong>using parallax</strong> with<strong> <?php echo $sales ?> sales (<?php echo $sales_avg ?> avg)</strong>and <strong>$<?php echo $income_avg ?></strong> income (avg) with 60% rate.</p>
	</div>
	<div class="span3">
		<?php
			$percent = round(mycount($themes_accepted_30days, 'tags', 'builder') / count($themes_accepted_30days) * 100 );
			$count = mycount($themes_accepted_30days, 'tags', 'builder');
			$total_count = count($themes_accepted_30days);
			$sales = mysum($themes_accepted_30days, 'tags', 'builder','sales');
			$sales_avg = round($sales / $count);
			$income = round (myincome($themes_accepted_30days, 'tags', 'builder') * 0.6);
			$income_avg = round($income / $count * 0.6);
		?>
		<h3>Page/Layout Builder</h3>
		<div class="progress">
			<div class="bar" style="width: <?php echo $percent ?>%;"><?php echo $count ?> / <?php echo $total_count ?> themes</div>
		</div>
		<div class="alert alert-info pagination-centered"><strong><?php echo $percent ?>%</strong> - <strong><?php echo $sales ?></strong> s. (<?php echo $sales_avg ?> av) -<strong> $<?php echo $income_avg ?></strong> av</div>
		<p><strong><?php echo $percent ?>% </strong>of themes added last 30 days are using a <strong>page/layout Builder</strong> with<strong> <?php echo $sales ?> sales (<?php echo $sales_avg ?> avg)</strong>and <strong>$<?php echo $income_avg ?></strong> income (avg) with 60% rate.</p>
	</div>

  </div>
   <hr>
   <h2 class="well">Theme Specific Stats</h2>
   <?php if (!empty($ouritems)):
	   foreach ($ouritems as $key => $item): ?>
  <div class="row-fluid">
   	<div class="span4">
		<h3><?= $item['name'] ?> Sales - This week</h3>
		<?php if (!empty($thisweek[$key])): ?>
		<p><strong><?= $thisweek[$key] ?></strong> times sold </p>
		<?php else: ?>
		<p> No data yet. Wait a day!</p>
		<?php endif; ?>
    </div>
	<div class="span4">
		<h3><?= $item['name'] ?> Sales - Last week</h3>
		<?php if (!empty($lastweek[$key])): ?>
		<p><strong><?= $lastweek[$key] ?></strong> times sold </p>
		<?php else: ?>
		<p> No data. Bummer!</p>
		<?php endif; ?>
    </div>
  </div>
 <hr>
 <?php endforeach;
	endif;
	?>

<? foreach (array(30) as $days): ?>
	<h3>Best sellers in the last <?= $days ?> days</h3>
	<? $categories = all_categories() ?>
	<? if ( ! empty($categories)): ?>
		<div class="row-fluid">
			<? $counter = 0; ?>
			<? foreach ($categories as $category): ?>
				<? if ($counter == 3): ?>
					</div><div class="row-fluid">
					<? $counter = 1; ?>
				<? else: # coutner < 4 ?>
					<? $counter++ ?>
				<? endif; ?>
				<div class="span4">
					<h4><?= $category['title'] ?></h4>
					<ol>
						<? foreach (best_sellers_for_category($category['id'], $days) as $item): ?>
							<li><?= $item['title'] ?> - <?= $item['sales'] ?> sales</li>
						<? endforeach; ?>
					</ol>
				</div>
			<? endforeach; ?>


		</div>
	<? else: # no categories ?>
		<p>There are currently no categories available.</p>
	<? endif; ?>
<? endforeach; ?>
<hr>

  <!-- Graphs -->
  <div class="row-fluid">
  	<div class="span6">
  	<h4><i>Senna</i> Sales (last 30 days) </h4>
  		<?php sales_graph(4609270, 30); ?>
  	</div>
  	<div class="span6">
  	<h4><i>Fuse</i> Sales (last 30 days) </h4>
  		<?php sales_graph(5136837, 30); ?>
  	</div>
  </div>

  <div class="row-fluid">
  	<div class="span6">
  	<h4><i>Salient</i> Sales (last 30 days) </h4>
  		<?php sales_graph(4363266, 30); ?>
  	</div>
  	<div class="span6">
  	<h4><i>StartUP</i> Sales (last 30 days) </h4>
  		<?php sales_graph(5445413, 30); ?>
  	</div>
  </div>
	   <hr>
      <div class="footer">
    <p>&copy; PixelGrade 2013</p>
  </div>
    </div>
<!-- /container -->

<!-- Le javascript
    ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/bootstrap-transition.js"></script>
<script src="js/bootstrap-alert.js"></script>
<script src="js/bootstrap-modal.js"></script>
<script src="js/bootstrap-dropdown.js"></script>
<script src="js/bootstrap-scrollspy.js"></script>
<script src="js/bootstrap-tab.js"></script>
<script src="js/bootstrap-tooltip.js"></script>
<script src="js/bootstrap-popover.js"></script>
<script src="js/bootstrap-button.js"></script>
<script src="js/bootstrap-collapse.js"></script>
<script src="js/bootstrap-carousel.js"></script>

<script src="js/charts/raphael.min.js"></script>
<script src="js/charts/morris.min.js"></script>

</body>
</html>