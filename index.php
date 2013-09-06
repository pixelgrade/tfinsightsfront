<?php
	date_default_timezone_set('Australia/Melbourne');

	//here we go
	
	function grab_data_from_url($json_url) {
//		var_dump($json_url);

		//$username = 'your_username';  // authentication
		//$password = 'your_password';  // authentication
		
		// jSON String for request
		//$json_string = '[your json string here]';
		 
		// Initializing curl
		$ch = curl_init( $json_url );
		 
		// Configuring curl options
		$options = array(
		CURLOPT_RETURNTRANSFER => true,
		//CURLOPT_USERPWD => $username . ":" . $password,   // authentication
		CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
		//CURLOPT_POSTFIELDS => $json_string
		);
		 
		// Setting curl options
		curl_setopt_array( $ch, $options );
		 
		// Getting results
		$result =  curl_exec($ch); // Getting jSON result string
		
		curl_close($ch);
		
		return json_decode($result,true);
	}
	
	function mycount($items,$key,$string, $strict = false) {
		$counter = 0;
		foreach ($items as $item) {
			if ($strict) {
				if ($item[$key] == $string) {
					$counter++;
				}
			} elseif (strpos($item[$key], $string) !== false) {
				$counter++;
			}
		}
		
		return $counter;
	}
	
	function mycountadv($items,$strings) {
		$counter = 0;
		foreach ($items as $item) {
			$found = false;
			foreach ($strings as $key => $valuearr) {
				foreach ($valuearr as $string) {
					if (strpos($item[$key], $string) !== false) {
						$counter++;
						$found = true;
						break;
					}
				}
				
				if ($found) {
					break;
				}
			}
		}
		
		return $counter;
	}
	
	function mysum($items,$key,$string,$sumkey, $strict = false) {
		$sum = 0;
		foreach ($items as $item) {
			if (empty($string)) {
				$sum += $item[$sumkey];
			} elseif ($strict) {
				if ($item[$key] == $string) {
					$sum += $item[$sumkey];
				}
			} elseif (strpos($item[$key], $string) !== false) {
				$sum += $item[$sumkey];
			}
		}
		
		return $sum;
	}
	
	function mysumadv($items,$strings,$sumkey) {
		$sum = 0;		
		foreach ($items as $item) {
			$found = false;
			foreach ($strings as $key => $valuearr) {
				foreach ($valuearr as $string) {
					if (strpos($item[$key], $string) !== false) {
						$sum += $item[$sumkey];
						$found = true;
						break;
					}
				}
				
				if ($found) {
					break;
				}
			}
		}
		return $sum;
	}
	
	function myincome($items,$key,$string, $strict = false) {
		$income = 0;
		foreach ($items as $item) {
			if (empty($string)) {
				$income += $item['sales']*$item['cost'];
			} elseif ($strict) {
				if ($item[$key] == $string) {
					$income += $item['sales']*$item['cost'];
				}
			} elseif (strpos($item[$key], $string) !== false) {
				$income += $item['sales']*$item['cost'];
			}
		}
		
		return $income;
	}
	
	function myincomeadv($items,$strings) {
		$income = 0;
		foreach ($items as $item) {
			$found = false;
			foreach ($strings as $key => $valuearr) {
				foreach ($valuearr as $string) {
					if (strpos($item[$key], $string) !== false) {
						$income += $item['sales']*$item['cost'];
						$found = true;
						break;
					}
				}
				
				if ($found) {
					break;
				}
			}
		}
		return $income;
	}
	
	//config
	$mainurl = 'http://cgwizz.com/tf-insights/api/v1/';
//	$mainurl = 'http://tf-insights.localhost/api/v1/';
	
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
	$dowMap = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	
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
	
	if (date('N', time()) == 1) { //today is monday so no data yet
		$senna_thisweek = null;
		$fusethisweek = null;
	} else {
		$senna_thisweek = grab_data_from_url($mainurl.'items?itemid=4609270');
		$tempstats = grab_data_from_url($mainurl.'items?itemid=4609270&date='.strtotime("Monday this week"));
		$senna_thisweek['sales'] -= $tempstats['sales'];
		
		$fuse_thisweek = grab_data_from_url($mainurl.'items?itemid=5136837');
		$tempstats = grab_data_from_url($mainurl.'items?itemid=5136837&date='.strtotime("Monday this week"));
		$fuse_thisweek['sales'] -= $tempstats['sales'];
		unset($tempstats);
	}
	
	$senna_lastweek = grab_data_from_url($mainurl.'items?itemid=4609270&date='.strtotime("Sunday last week"));
	$tempstats = grab_data_from_url($mainurl.'items?itemid=4609270&date='.strtotime("Monday last week"));
	$senna_lastweek['sales'] -= $tempstats['sales'];
	
	$fuse_lastweek = grab_data_from_url($mainurl.'items?itemid=5136837&date='.strtotime("Sunday last week"));
	$tempstats = grab_data_from_url($mainurl.'items?itemid=5136837&date='.strtotime("Monday last week"));
	$fuse_lastweek['sales'] -= $tempstats['sales'];
	unset($tempstats);
	
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
    </head>

    <body>
<div class="container">
      <div class="masthead">
    <h4 class="alert alert-error pull-left">
    The FBI Files on ThemeForest
    </h4>
    <h4 class="alert pull-right">TOP SECRET DOCUMENT</h4>
  </div>
      
      <!-- Jumbotron -->
      <div class="jumbotron">
       <p class="lead">&nbsp;</p>
    <h1>ThemeForest Insights</h1>
	<p class="text-center">The current data and time: <?php echo date("D M d, Y G:i a"); ?></p>
  </div>
      <hr>
      
      <!-- Example row of columns -->
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
  <div class="row-fluid">
   	<div class="span4">
		<h3>Senna Sales - This week</h3>
		<?php if (!empty($senna_thisweek)): ?>
		<p><strong><?php echo $senna_thisweek['sales'] ?></strong> times sold </p>
		<?php else: ?>
		<p> No data yet. Wait a day!</p>
		<?php endif; ?>
    </div>
	<div class="span4">
		<h3>Senna Sales - Last week</h3>
		<?php if (!empty($senna_lastweek)): ?>
		<p><strong><?php echo $senna_lastweek['sales'] ?></strong> times sold </p>
		<?php else: ?>
		<p> No data. Bummer!</p>
		<?php endif; ?>
    </div>
  </div>
 <hr>
	<div class="row-fluid">
   	<div class="span4">
		<h3>Fuse Sales - This week</h3>
		<?php if (!empty($fusethisweek)): ?>
		<p><strong><?php echo $fuse_thisweek['sales'] ?></strong> times sold </p>
		<?php else: ?>
		<p> No data yet. Wait a day!</p>
		<?php endif; ?>
    </div>
	<div class="span4">
		<h3>Fuse Sales - Last week</h3>
		<?php if (!empty($fuse_lastweek)): ?>
		<p><strong><?php echo $fuse_lastweek['sales'] ?></strong> times sold </p>
		<?php else: ?>
		<p> No data. Bummer!</p>
		<?php endif; ?>
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
<script src="js/jquery.js"></script> 
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
<script src="js/bootstrap-typeahead.js"></script>
</body>
</html>
