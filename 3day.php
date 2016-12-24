<?php

$command = $_POST['command'];
$text = $_POST['text'];
$token = $_POST['token'];

function get_api_response($city) {
    
    $user_agent = "3DayForSlack/0.1; rjkeck2@yahoo.com";

    $url_to_check = "http://api.wunderground.com/api/e6bbd971c5d7cbc5/forecast/q/" . $city . ".json";

    $ch = curl_init($url_to_check);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    $ch_response = curl_exec($ch);
    curl_close($ch);

    return $ch_response;
}

function get_weather($ch_response) {
    
    $response_array = json_decode($ch_response,true);

    if($ch_response === FALSE) {
        $reply = "Wunderground could not be reached.";
        echo $reply;
    } elseif(empty($response_array["forecast"]["simpleforecast"]["forecastday"][0]["high"]["fahrenheit"]) == True) {
        echo "Please enter a valid zip code!";
    } else {
        for ($x = 0; $x < 4; $x++) {
          $high = $response_array["forecast"]["simpleforecast"]["forecastday"][$x]["high"]["fahrenheit"];
          $low = $response_array["forecast"]["simpleforecast"]["forecastday"][$x]["low"]["fahrenheit"];
          $condition = $response_array["forecast"]["simpleforecast"]["forecastday"][$x]["conditions"];
          $day = $response_array["forecast"]["simpleforecast"]["forecastday"][$x]["date"]["weekday_short"];
          $reply = "*" . $day . "* -  High: " . $high . ", Low: " . $low . ", " . $condition . "\n";
          echo ">" . $reply; 
       }
    }
}

function get_precip($ch_response) {
    
    $response_array = json_decode($ch_response,true); 

    if($ch_response === FALSE) {
        $reply = "Wunderground could not be reached.";
        echo $reply;
    } elseif(empty($response_array["forecast"]["simpleforecast"]["forecastday"][0]["high"]["fahrenheit"]) == True) {
        echo "Please enter a valid zip code!";
    } else {
        for ($x = 0; $x < 4; $x++) {
          $condition = $response_array["forecast"]["simpleforecast"]["forecastday"][$x]["icon"];
          if($condition == "chancerain"){
            $precip = ":rain_cloud:";
          } elseif($condition == "snow"){
            $precip = ":snowflake:";
          } else {
            $precip = "No precip forecasted!";
          }
          $day = $response_array["forecast"]["simpleforecast"]["forecastday"][$x]["date"]["weekday_short"];
          $reply = "*" . $day . "* -  " . $precip . "\n";
          echo ">" . $reply; 
       }
    }
}

function check_slack_token($token){
    if($token != '***'){ 
      $msg = "The token for the slash command doesn't match. Check your script.";
      die($msg);
      echo $msg;
    }
}

function find_precip($text){

  $textlen = strlen($text);
  for ($x = 1; $x < 7; $x++) {
    $eos[$x] = str_split($text)[$textlen - $x];
  }

  $precip = $eos[6] . $eos[5] . $eos[4] . $eos[3] . $eos[2] . $eos[1];

  if ($precip == 'precip'){
    return 1;
  } else {
    return 0;
  }
}

function print_forecast($text) {

    if(str_split($text, 4)[0] == 'indy'){
        $city = '46203';
        $name = 'Indianapolis';
    } elseif(str_split($text, 4)[0] == 'cbus'){
        $city = '43215';
        $name = 'Columbus';
    } else {
        $city = str_split($text, 5)[0];
        $name = $city;
    }

    if(str_split($text, 4)[0] == 'help'){
        echo " Type `/3day [zip code]` to get current weather and 3 day forecast or `/3day [zip code] precip` to get chance of rain or snow.";
    } elseif (find_precip($text) == 1){
        echo "_*Precipitation for $name:*_ \n";
        $ch_response = get_api_response($city);
        get_precip($ch_response);
    } else {  
        echo "_*Forecast for $name:*_ \n";
        $ch_response = get_api_response($city);
        get_weather($ch_response);
    }
}

check_slack_token($token);
print_forecast($text);

?>
