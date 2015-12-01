<?php

define('TOKEN', '152945078:AAHRok2HuvSYRXxs55RLvVWoa0t3Org8u9c');
define('API_URL', 'https://api.telegram.org/bot' . TOKEN . '/');
define('TEST', 0);
define('TEST_MESSAGE', 'hola');

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (TEST)
{
  $update = array (
    'message' => array (
      'text' => TEST_MESSAGE,
      'message_id' => 1,
      'chat' => array ('id' => 1)
    ),
  );
}

if (isset($update['message']))
{
  $message = $update['message'];
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  $text = $message['text'];
  $response = handleText($text);
  if ($response)
  {
    if (TEST)
    {
      echo $response;
    }
    else
    {
      $aux = explode(':', $response);
      if (count($aux) === 2 && $aux[0] === 'sticker')
      {
        // NO ANDA
        apiRequestJson("sendSticker", array (
          'chat_id' => $chat_id,
          'sticker' => $aux[1] // no se como mierda hay que pasarle el file_id del sticker ni de donde sacarlo
        ));
      }
      else
      {
        apiRequestJson("sendMessage", array (
          'chat_id' => $chat_id,
          'text' => $response
        ));
      }
    }
  }
}

function handleTextWords($words)
{
  $needles = array (
    'dale men, hasta yo tengo foto de perfil' => array ('belilos', 'foto'),
    'yo me re prendo a una hackathon eh' => array ('hackathon'),
    'belilos es un cagón' => array ('belilos'),
    'de nada ameo' => array ('gracias'),
    'viva el mct' => array ('mct'),
    '%hello%' => array ('bimbo'),
    'que te pasa con fargo pelotudo' => array ('fargo'),
    'https://github.com/servani/bimbot' => array ('repo'),
    'la que le gusta al pelado miola' => array ('pija'),
    'vamo a lo de piche a fumar unos' => array ('droga'),
    'vamo a lo de pichettoooo a fumar unos' => array ('porro'),
    'denme un ak47 ' => array ('faso'),
    'estoy re manija vamo a lo de facu a fumanchea' => array ('fumar'),
    'la que le gusta a tu hermana' => array ('marihuana'),
    '%name%' => array ('el', 'mas', 'puto'),
    'mmmmmnnnnnnnnnmusaaaraña musaraña musaraña musaraña' => array ('musa'),
    'que sera que sera de la vida del gran yamid' => array ('que', 'sera'),
    'el tunel de monroe es una maaaaaasssssssaaa' => array ('monroe'),
    'ipad? si el que se gano el hijo de puta de belilos' => array ('ipad'),
    'aguante bluesmart' => array ('bluesmart'),
    'con esta pelotudo' => array ('con', 'que'),
  );
  foreach ($needles as $message => $needle)
  {
    if (count(array_intersect($needle, $words)) === count($needle))
    {
      $names = array (
        'beli', 'miola', 'pato', 'guille', 'santi',
        'facu', 'fede', 'la musa', 'luigi', 'erni',
        'el forro de schattenhofer'
      );
      shuffle($names);
      if ($message === "%name%")
      {
        $message = $names[0];
      }
      elseif ($message === "%hello%")
      {
        $hellos = array (
          'que onda wachos',
          'que onda loco',
          'que pasa',
          'me llamaste? hijo de puta?',
          'me tienen los huevos por el piso',
          'uhmmm',
          'que pesados q estan loco vayansen a la concha de su hermana',
          'estaba pensando en ' . $names[0],
          'ke',
          'basta loco',
          'che ' . $names[0] . ' necesito que me des una mano con algo',
          'bimbbobim',
          'dale loco mentanle mano al codigo que me hinche los huevos de decir siempre lo mismo'
        );
        shuffle($hellos);
        $message = $hellos[0];
      }
      return $message;
    }
  }
  return false;
}

function handleTextSingleWord($word)
{
  $magic_words = array (
    'jajaja' => 'jajaajajja',
    'JAJA' => 'JAJAJAJAJJA',
    'bimbo' => 'que te pasa pelotudo',
    'gracias' => 'de nada ameo',
    'mct' => 'viva el mct',
    'hola' => 'holis',
    'holi' => 'holis',
    'nada' => 'AH BUENJO mejor asi hijo deputa',
    'pelado' => 'sticker:260429632665289106'
  );
  foreach ($magic_words as $needle => $message)
  {
    if (strpos($word, $needle) !== FALSE)
    {
      return $message;
    }
  }
  return false;
}

function handleText($text)
{
  $words = explode(' ', $text);
  if (count($words) > 1)
  {
    $response = handleTextWords($words);
  }
  else
  {
    $response = handleTextSingleWord($text);
  }
  return $response;
}

function apiRequestJson($method, $parameters)
{
  if (!is_string($method))
  {
    error_log("Method name must be a string\n");
    return false;
  }
  if (!$parameters)
  {
    $parameters = array();
  }
  else if (!is_array($parameters))
  {
    error_log("Parameters must be an array\n");
    return false;
  }
  $parameters["method"] = $method;
  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
  return exec_curl_request($handle);
}

function exec_curl_request($handle)
{
  $response = curl_exec($handle);
  if ($response === false)
  {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500)
  {
    sleep(10);
    return false;
  }
  else if ($http_code != 200)
  {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401)
    {
      throw new Exception('Invalid access token provided');
    }
    return false;
  }
  else
  {
    $response = json_decode($response, true);
    if (isset($response['description']))
    {
      error_log("Request was successfull: {$response['description']}\n");
    }
    $response = $response['result'];
  }
  return $response;
}