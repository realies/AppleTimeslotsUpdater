<?php
session_start();
// Directory for logging changes
// $logFolder = './log';
// Frequency of reloading in seconds
$refreshInterval = 120;
// A cURL query Chrome has generated for loading store data
$cURLQuery = <<<EOF
EOF;
// Extracting headers
preg_match_all("/-H '(?<headerData>.*?)'/", $cURLQuery, $matches['headers']);
// Extracting posted data
preg_match_all("/--data-binary '(?<storeData>.*?)'/", $cURLQuery, $matches['data']);
// Count header matches
$matches['headers']['count'] = count($matches['headers']['headerData']);
// Count store data matches
$matches['data']['count'] = count($matches['data']['storeData']);
// Ensure sufficient parsed data
if($matches['headers']['count'] == 0 or $matches['data']['count'] == 0)
	$error = "Unsupported cURL query\n";
if($matches['headers']['count'] == 0)
	$error .= "Missing query header data\n";
if($matches['data']['count'] == 0)
	$error .= "Missing query store data\n";
// If storing changes is enabled, ensure a folder for log files or try and create it
if(isset($logFolder))
	if(!is_dir($logFolder))
		if(!@mkdir($logFolder, 777))
			@$error .= "Unable to create log folder, check permissions or create manually\n";
if(!isset($error)) {
	// Timeslots API Url
	$url = 'https://getsupport.apple.com/web/v2/takein/timeslots';
	// Initiate cURL
	$ch = curl_init($url);
	// Tell cURL that we want to send a POST request
	curl_setopt($ch, CURLOPT_POST, true);
	// Set headers as they were from the browser request
	curl_setopt($ch, CURLOPT_HTTPHEADER, $matches['headers']['headerData']); 
	// Set data as it was from the browser request
	curl_setopt($ch, CURLOPT_POSTFIELDS, $matches['data']['storeData'][0]);
	// Return to string instead of printing
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// Execute request and decode json to array
	$result = json_decode(curl_exec($ch));
	// Check for available days
	if(@count($result->data->timeslots->days) > 0) {
			// Iterate through days
			foreach ($result->data->timeslots->days as $day) {
				// Name of day in localized format
				@$output .= "{$day->localizedDayName}: ";
				// Check for timeslots
				if(@count($day->timeSlots) > 0) {
					// Iterate through timeslots
					$timeslots = 0;
					foreach ($day->timeSlots as $key => $timeslot) {
						// Increase timeslots amount
						$timeslots++;
						// Parse timeslot duration
						if($timeslot->epochTime)
							$duration = explode('-', $timeslot->epochTime);
						// If sliced in two strings, treat as timestamps
						if(count($duration) == 2)
							@$schedule .= "\n".date('H:i:s', $duration[0])."-".date('H:i:s', $duration[1]);
					}
					// Join output with timeslots amount and generated schedule
					$output .= $timeslots.$schedule;
				}
				else {
					$output .= "None";
				}
				$output .= "\n";
			}
	}
	else {
		// Couldn't find timeslots in retrieved data
		$output = "Invalid response, couldn't find timeslots";
	}
	// Store changes if logging folder is set
	if(isset($logFolder)) {
		// Catching changes since last use
		if(@$_SESSION['output'] != $output) {
			// Attempt to save the output to a file
			if(!@file_put_contents($logFolder."/".date("Y-m-d_His", time()).".log", $output)) {
				// Add error data
				$output = "<b>Unable to write to log file, check permissions...</b>\n\n".$output;
			}
			else {
				// Save output state in session
				$_SESSION['output'] = $output;
				// Add status data
				$output = "<b>Changes saved...</b>\n\n".$output;
			}
		}
	}
} else {
	$output = $error;
}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta http-equiv="refresh" content="<?php echo $refreshInterval; ?>">
  <style>html {background:black;font-size:24px;color:white;}</style>
 </head>
 <body>
<?php echo "<pre>".$output."</pre>"; ?>

 </body>
</html>