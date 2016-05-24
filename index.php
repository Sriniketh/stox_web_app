<?php
header("Access-Control-Allow-Origin: *");
if($_GET[ "req" ] == 'autocomplete'){
    $term = $_GET[ "param" ];
    $lookup_url = "http://dev.markitondemand.com/MODApis/Api/v2/Lookup/json?input=";
    $response = file_get_contents($lookup_url . $term);
    $array = json_decode( $response, true );

    $lookup_result = array();
    foreach($array as $item){
        $symbol = $item["Symbol"];
        $name = $item["Name"];
        $exchange = $item["Exchange"];
        $lookup_result[] = array(
            'value'=>$symbol,
            'label'=>$symbol.' - '.$name.' ( '.$exchange.' )'
        );
    }

    echo json_encode( $lookup_result );
}
else if($_GET[ "req" ] == 'quote'){
    $symbol = $_GET["param"];
    $quote_url = "http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol=";
    $response = file_get_contents($quote_url . $symbol);
    $quote = json_decode($response, true);
    
    $quote_result = array();
    foreach($quote as $key => $value){
        $quote_result[] = array(
            $key=>$value
        );
    }
    
    echo json_encode($quote_result);
}
else if($_GET[ "req" ] == 'chart'){
    $chart_url = "http://dev.markitondemand.com/MODApis/Api/v2/InteractiveChart/json?parameters=%7b%22Normalized%22:false,%22NumberOfDays%22:1095,%22DataPeriod%22:%22Day%22,%22Elements%22:%5b%7b%22Symbol%22:%22" . $_GET["param"] . "%22,%22Type%22:%22price%22,%22Params%22:%5b%22ohlc%22%5d%7d%5d%7d";
    $response = file_get_contents($chart_url);
    $array = json_decode($response, true);
    $chart_result = array();
    
    date_default_timezone_set('UTC');
    $dates = array();
    $dates = $array["Dates"];
    $open_values = array();
    $open_values = $array["Elements"][0]["DataSeries"]["open"]["values"];
    $high_values = array();
    $high_values = $array["Elements"][0]["DataSeries"]["high"]["values"];
    $low_values = array();
    $low_values = $array["Elements"][0]["DataSeries"]["low"]["values"];
    $close_values = array(); 
    $close_values = $array["Elements"][0]["DataSeries"]["close"]["values"];
    $i = 0;
    while($i < sizeof($open_values)){
        $each_date = $dates[$i];
        $each_time = strtotime($each_date);
        $item_array = array();
        $item_array[0] = $each_time * 1000;
        $item_array[1] = $open_values[$i];
        $item_array[2] = $high_values[$i];
        $item_array[3] = $low_values[$i];
        $item_array[4] = $close_values[$i];
        $chart_result[$i] = $item_array;
        ++$i;
    }
    echo json_encode($chart_result);
}
else if($_GET[ "req" ] == "news"){
    $accountKey = 'tRkks1s5zuR7wvhwjtZIU3t8JK1dkW/kFJV49rjctEM';

    $auth = base64_encode("$accountKey:$accountKey");

    $data = array(
      'http'            => array(
      'request_fulluri' => true,
      'ignore_errors'   => true,
      'header'          => "Authorization: Basic $auth")
    );

    $context   = stream_context_create($data);
    $request = 'https://api.datamarket.azure.com/Bing/Search/v1/News?Query=%27' . $_GET["param"] . '%27&$format=json';
    // Get the response from Bing.
    $response = file_get_contents($request, 0, $context);
    $result = json_decode($response, true);
    $result_array = array();
    foreach($result['d']['results'] as $item){ 
        $result_array[] = array(
            'title'=>$item['Title'],
            'content'=>$item['Description'],
            'url'=>$item['Url'],
            'source'=>$item['Source'],
            'date'=>$item['Date']
        );
    }
    echo json_encode($result_array);
}
?>
