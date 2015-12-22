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
  $text = strtolower($message['text']);
  $from = $message['from']['first_name'];
  $response = handleText($text, $from);
  if ($response && !isMuted())
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

function handleTextWords($words, $from)
{
  $needles = array (
    '%mood%' => array ('como', 'te', 'sentis'),
    '%setmood%' => array ('/setmood'),
    'forro sos vos hijode putea' => array ('oso', 'forro'),
    'dale men, hasta yo tengo foto de perfil' => array ('belilos', 'foto'),
    'yo me re prendo a una hackathon eh' => array ('hackathon'),
    'belilos es un cagón' => array ('belilos'),
    "\xE2\x9D\xA4" => array ('tkm'),
    'de nada ameo' => array ('gracias'),
    'viva el mct' => array ('mct'),
    '%hello%' => array ('bimbo'),
    '%song%' => array ('cantate'),
    '%love%' => array ('te', 'amo'),
    'que te pasa con fargo pelotudo' => array ('fargo'),
    'https://github.com/servani/bimbot' => array ('repo'),
    '%pija%' => array ('pija'),
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
    'sabes donde te podes meter el corazoncito?' => array ('❤'),
    'nada re tranki aca en pija' => array ('que', 'pasando'),
    'ni bigote ni manco, CRISTINA LOCO PORQUE NESTOR NO SE MURIO LO LLEVO EN EL CORAZON VIVA PERON HIJOS DE PUTA' => array ('macri', 'scioli'),
    'no soy un oso de mierda, soy un oso barrilete y vendehumo iiiiiiiiiiiiaja' => array ('oso', 'mierda')
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
      elseif ($message === "%love%")
      {
        $message = 'yo te amo a vos ' . strtolower($from);
      }
      elseif ($message === "%pija%")
      {
        $message = 'pija? a ' . $names[0] . ' le gusta la pija';
      }
      elseif ($message === "%setmood%" && $words[0] === '/setmood')
      {
        unset($words[0]);
        $mood = implode(' ', $words);
        setMood($mood);
        $message = 'ok';
      }
      elseif ($message === "%mood%")
      {
        $message = getMood();
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
          'shicos hishe un shcript en corshetes adivinen quein soy',
          'dale loco mentanle mano al codigo que me hinche los huevos de decir siempre lo mismo'
        );
        shuffle($hellos);
        $message = $hellos[0];
      }
      elseif ($message === "%song%")
      {
        $hellos = array (
          'tu novia puta con mi pingo se ahooga y vos no decis nada pporque sooo un toga recatate giiiiil',
          'vamoooos vamoos a bailaaaaar vamoos a bailar hasta la madrugada dejate llevaaaar',
          'tetereré tere tete tetereré tere re re',
          'tchê tcherere tchê tchê, tcherere tchê tchê, tcherere tchê tchê, tchereretchê tchê, tchê, tchê, el oso bimbo e você',
          'nossa, nossa assim você me mata ai se eu te pego, ai ai se eu te pego',
          'chora, me liga implora o meu beijo de novo me pede socorro'
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
    'jajaja' => '%haha%',
    'JAJA' => 'JAJAJAJAJJA',
    'bimbo' => 'que te pasa pelotudo',
    'gracias' => 'de nada ameo',
    'mct' => 'viva el mct',
    'hola' => 'holis',
    'holi' => 'holis',
    '/unmute' => 'SOY LIBREEEEEEE',
    '/mute' => '',
    'nada' => 'AH BUENJO mejor asi hijo deputa',
    'pelado' => 'sticker:260429632665289106',
    'tkm' => "\xE2\x9D\xA4",
  );
  foreach ($magic_words as $needle => $message)
  {
    if (strpos($word, $needle) !== FALSE)
    {
      if ($needle === "/mute")
      {
        mute();
      }
      elseif ($needle === "/unmute")
      {
        unmute();
      }
      elseif ($message === "%haha%")
      {
        $haha = array (
          'jajajaja',
          'jjajajajaj',
          'hahahahaha',
          'jiji',
          'JAJAJAJAJ AH RE LOKO',
          'jajajaj ah re',
          'haha',
          'jajajjaj',
          'basat',
          'basta no es gracioso',
        );
        shuffle($haha);
        $message = $haha[0];
      }
      return $message;
    }
  }
  return false;
}

function handleText($text, $from)
{
  if (strpos($text, "simon dice") === 0)
  {
    return substr($text, 11);
  }
  $words = explode(' ', $text);
  if (count($words))
  {
    $response = handleTextWords($words, $from);
  }
  if (!$response)
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

function isMuted()
{
  $mem = new Memcached();
  $mem->addServer("127.0.0.1", 11211);
  return $mem->get('mute');
}

function mute()
{
  $mem = new Memcached();
  $mem->addServer("127.0.0.1", 11211);
  return $mem->set('mute', 1);
}

function unmute()
{
  $mem = new Memcached();
  $mem->addServer("127.0.0.1", 11211);
  return $mem->set('mute', 0);
}

function setMood($mood)
{
  $mem = new Memcached();
  $mem->addServer("127.0.0.1", 11211);
  $mem->set('mood', $mood);
}

function getMood()
{
  $mem = new Memcached();
  $mem->addServer("127.0.0.1", 11211);
  return $mem->get('mood') ?: 'bien';
}