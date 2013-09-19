<?php
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

	function sales_graph($itemID, $days) {
		global $mainurl;
		$uniqueid = uniqid();
		$itemsales = grab_data_from_url($mainurl.'items?itemid='.$itemID.'&period='.($days));
		//if we got fewer results, adjust
		if (count($itemsales)-1 < $days) {
			$days = count($itemsales)-1;
		}
		//var_dump($itemsales);
		?>
		<div id="<?= $uniqueid ?>" style="height:200px"></div>
		<script type="text/javascript">
			jQuery( document ).ready(function() {
  			// Use Morris.Area instead of Morris.Line
			// Docs: http://www.oesmith.co.uk/morris.js/lines.html
			var sales_data_<?= $uniqueid ?> = [
		<?php
			for ($x = 0; $x < $days; $x++) {
				$str = '{"day": "'.date('Y-m-d',strtotime($itemsales[$x+1]['timestamp'])).'", "sales": '.($itemsales[$x+1]['sales'] - $itemsales[$x]['sales']).'}';
				if ($x < $days-1) {
					$str .= ',';
				};
				echo $str;
			} ?>
				];

				Morris.Area({
				  element: '<?= $uniqueid ?>',
				  behaveLikeLine: true,
				  data: sales_data_<?= $uniqueid ?>,
				  xkey: 'day',
				  xLabels: 'day',
				  labels: ['Sales'],
				  ykeys: ['sales'],
				  parseTime: false,
				});
			});
			</script>
		<?php

	}

	function best_sellers_for_category($category, $days) {
		global $mainurl;
		return grab_data_from_url($mainurl.'items?category='.$category.'&dayspast='.$days);
	}

	function all_categories() {
		global $mainurl;
		return grab_data_from_url($mainurl.'categories?all');
	}

	//to get the start and end dates of a week with a given date
	function x_week_range($date) {
		$ts = strtotime($date);
		$start = (date('N', $ts) == 1) ? $ts : strtotime('last monday', $ts);
		return array(date('Y-m-d', $start),
					 date('Y-m-d', strtotime('next sunday', $start)));
	}