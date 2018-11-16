<?php
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(!$update)
{
  exit;
}

$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$text = isset($message['text']) ? $message['text'] : "";

$text = trim($text);
$text = strtolower($text);


//valori presi da thingspeak
$temperatura_dth11 = file_get_contents('https://thingspeak.com/channels/241780/fields/1/last.txt');
$temperatura_bmp180 = file_get_contents('https://thingspeak.com/channels/241780/fields/3/last.txt',FALSE,NULL,0,2);
$temperatura_int= (($temperatura_dth11 + $temperatura_bmp180)/2)."°C";
$umidita = file_get_contents('https://thingspeak.com/channels/241780/fields/2/last.txt');
$pressione = file_get_contents('https://thingspeak.com/channels/241780/fields/4/last.txt')." mbar";
$gas=file_get_contents('https://thingspeak.com/channels/241780/fields/5/last.txt');
$temperatura_ext = file_get_contents('https://thingspeak.com/channels/241780/fields/6/last.txt')."°C";
//$umidita_ext = file_get_contents('https://thingspeak.com/channels/241780/fields/7/last.txt'); valore falsato dalla plafoniera
$secondi= file_get_contents('https://thingspeak.com/channels/241780/fields/1/last_data_age.txt');

//valori presi da openweather
$meteo= file_get_contents('http://api.openweathermap.org/data/2.5/weather?q=Pulsano,it&APPID=59fae07055c13760fefd4869c07ca92f&units=metric&lang=it');
$meteo_array=explode(':',$meteo);
$cielo=explode(',', $meteo_array[7])[0]; 
$temp_att=explode(',', $meteo_array[11])[0]."°C"; 
$pressure=explode(',', $meteo_array[12])[0]."mbar";
$umidita_ext =explode(',', $meteo_array[13])[0];
$temp_min=explode(',', $meteo_array[14])[0]."°C";
$temp_max=explode('}', $meteo_array[15])[0]."°C";
$vel_vento=explode(',',$meteo_array[18])[0];
$dir_vento=explode('}', $meteo_array[19])[0]."°N";

//comando luce balcone Alfred








header("Content-Type: application/json");

$response = '';
if(strpos($text, "/start") === 0 || $text=="ciao")
{
	$response = "Ciao $firstname, benvenuto!";
}
elseif($text=="/temperatura")
{  
	$response = "Ciao $firstname, la temperatura interna è " . $temperatura_int . " mentre quella esterna è " . $temperatura_ext . ". La minima prevista è " . $temp_min . " e la massima prevista è " . $temp_max;
}
elseif($text=="/umidita")
{   
	$response = "Ciao $firstname, l'umidità in casa: ". $umidita . "\r\n" . 
				"umidità esterna: " . $umidita_ext;
}
elseif($text=="/pressione")
{   
	$response = "Ciao $firstname, la pressione è " . $pressione;
}
elseif($text=="/situazione")
{   
	$response = "Ciao $firstname" . "\r\n" . 
				"Temperatura in casa: " . $temperatura_int . "\r\n" . 
				"Umidità in casa: " . $umidita . " %" . "\r\n" . 
				"Temperatura esterna: " . $temperatura_ext . "\r\n" .
				"Umidità esterna: " . $umidita_ext . " %" . "\r\n" . 
				"Pressione atm: " . $pressione . "\r\n" . "\r\n" .
				"Meteo: " . $cielo. "\r\n" .
				"Vento: " .($vel_vento*3.6) . " km/h" . " provenienza " . $dir_vento . "\r\n" . "\r\n" .
				"(aggiornato a " . round(($secondi/60), 1) . " minuti fa)"; 
								
}
elseif($text=="/vento")
{   
	$response = "Ciao $firstname, il vento è " . ($vel_vento*3.6) . " km/h" . " provenienza " . $dir_vento;
}
elseif($text=="/meteo")
{   
	$response = "Ciao $firstname, il cielo è ". $cielo .", il vento è " . $vel_vento ." provenienza " . $dir_vento. ", la pressione è di ". $pressure . " e ci sono ". $temp_att;
}
elseif($text=="\u0031\u20e3")
{   
	$link_luce_on = file_get_contents("http://casaalfred.ddns.net:8082/LED=ON");
    $luce_array_on=explode('>',$link_luce_on);
    $stato_luce_on=explode('<',$luce_array_on[6])[0];
	$response = $stato_luce_on;
}
elseif($text=="\u0030\u20e3")
{   
	$link_luce_off = file_get_contents("http://casaalfred.ddns.net:8082/LED=OFF");
	$luce_array_off=explode('>',$link_luce_off);
	$stato_luce_off=explode('<',$luce_array_off[6])[0];
	$response = $stato_luce_off;
}
elseif($text=="balcone")
{   
	$link_luce = file_get_contents("http://casaalfred.ddns.net:8082");
	$luce_array=explode('>',$link_luce);
	$stato_luce=explode('<',$luce_array[6])[0];
	$response = $stato_luce;
}
elseif($text=="1\u20e3")
{   
	$link_luce_on = file_get_contents("http://casaalfred.ddns.net:8083/LED=ON");
    $luce_array_on=explode('>',$link_luce_on);
    $stato_luce_on=explode('<',$luce_array_on[6])[0];
	$response = $stato_luce_on;
}
elseif($text=="0\u20e3")
{   
	$link_luce_off = file_get_contents("http://casaalfred.ddns.net:8083/LED=OFF");
	$luce_array_off=explode('>',$link_luce_off);
	$stato_luce_off=explode('<',$luce_array_off[6])[0];
	$response = $stato_luce_off;
}
elseif($text=="soggiorno")
{   
	$link_luce = file_get_contents("http://casaalfred.ddns.net:8083");
	$luce_array=explode('>',$link_luce);
	$stato_luce=explode('<',$luce_array[6])[0];
	$response = $stato_luce;
}
elseif($text=="/help")
{   
	$response = "Questo bot ti da alcune informazioni di CasaAlfred". "\r\n" ."Puoi chiedere al bot informazioni tipo:". "\r\n" ."/temperatura". "\r\n" ."/umidita". "\r\n" ."/pressione". "\r\n" ."/vento, /meteo e altro";
}

else
{
	$response = "comando non riconoscuiuto. \r\n" ."Prova /help";
	
}
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
// imposto la keyboard
$parameters["reply_markup"] = '{ "keyboard": [["balcone", "soggiorno"],["\u0030\u20e3", "\u0031\u20e3", "0\u20e3", "1\u20e3"],["/situazione"]], "one_time_keyboard": false, "resize_keyboard":true}';

echo json_encode($parameters);
?>