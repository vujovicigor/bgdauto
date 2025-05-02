<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("engine.php");
require_once 'vendor/autoload.php';

use starfederation\datastar\enums\EventType;
use starfederation\datastar\enums\FragmentMergeMode;
use starfederation\datastar\ServerSentEventGenerator;
$sse = new ServerSentEventGenerator();// Creates a new `ServerSentEventGenerator` instance.
$in_signals = ServerSentEventGenerator::readSignals();

$currentPath = $_SERVER['PHP_SELF']; 
$pathInfo = pathinfo($currentPath);     
$BASE = dirname($pathInfo['dirname']);
//setlocale(LC_TIME, 'sr_CS');

function getBaseUrl() 
{
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF']; 
    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
    $pathInfo = pathinfo($currentPath);     
    // output: localhost
    $hostName = $_SERVER['HTTP_HOST']; 
    // output: http://

    $isSecure = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $isSecure = true;
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
        $isSecure = true;
    }
    $protocol = $isSecure ? 'https://' : 'http://';

    // return: http://localhost/myproject/
    //echo $protocol.$hostName;
    //echo dirname($pathInfo['dirname']);
    $dir = '/';
    if ( dirname($pathInfo['dirname']) !='/') $dir = dirname($pathInfo['dirname']).'/';
    return $protocol.$hostName.$dir;
}
//$templates_assoc = fetch_twig_templates();
//print_r($templates_assoc);
// todo: 404 if req name not in templates_assoc
//$loader = new Twig_Loader_Array( $templates_assoc);

$TwigTemplatesDir = dirname( dirname(__FILE__) ).'/twigtemplates';
//$loader = new Twig_Loader_Filesystem( $TwigTemplatesDir );
$loader = new \Twig\Loader\FilesystemLoader($TwigTemplatesDir);
$twig = new \Twig\Environment($loader, array(
    'cache' => 'twigcache',
    'auto_reload' => true
));

$f = new \Twig\TwigFunction('fetch', function ($name, $params=array()) {
  return fetch($name, $params);
});
$twig->addFunction($f);

$f2 = new \Twig\TwigFunction('fetch2', function ($url, $data=array(), $method='GET') {
    //url-ify the data for the POST
    $fields_string = http_build_query($data);
    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    //curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

    //So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

    //execute post
    $result = curl_exec($ch);
    //echo $result;    
    /*
    // use key 'http' even if you send the request to https
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => $method,
            'content' => http_build_query($data),
            'timeout' => 30,
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === false) {
        return;
    }
    
    //var_dump($result);    
    //$json = file_get_contents($name);
    */
    $obj = json_decode($result);
    return $obj;
});
$twig->addFunction($f2);

$h = new \Twig\TwigFunction('header', function ($header, $replace = TRUE) {
    return header ( $header, $replace ); // add $http_response_code param if needed
  });
  $twig->addFunction($h);

$d = new \Twig\TwigFunction('dump', function ($var) {
return print_r( $var ); // add $http_response_code param if needed
});
$twig->addFunction($d);

$je = new \Twig\TwigFunction('json_encode', function ($var) {
    return json_encode($var, JSON_PRETTY_PRINT);
});
$twig->addFunction($je);

$signals = null;
$selector = null;

$d2 = new \Twig\TwigFunction('mergeSignals', function ($var=null) {
    //$sse->mergeSignals($var);
    global $signals;
    $signals = $var;
    return;
});
$twig->addFunction($d2);

$s2 = new \Twig\TwigFunction('selector', function ($var=null) {
    //$sse->mergeSignals($var);
    global $selector;
    $selector = $var;
    return;
});
$twig->addFunction($s2);

/* transform /filename/param1/param2/... to ['filename', 'param1', 'param2'] and assign it to $_GET */
//print_r($_SERVER);
$p = parse_url($_SERVER['REQUEST_URI']);
if (!isset($p['query'])) $p['query']='';
if (!isset($p['path']))  $p['path']='';
//list($path, $query) =
//echo $p['path'];

if (substr($p['path'], 0, strlen($BASE)) == $BASE) {
    $p['path'] = substr($p['path'], strlen($BASE));
} 

$pathArr = array_filter(explode('/', $p['path']));
//print_r( $pathArr );
//$queryArr = array_filter(explode('&', $p['query']));
parse_str($p['query'], $queryArr);
//print_r( $queryArr );

$index = 0;
foreach($pathArr as $key) {
    $queryArr[$index] = urldecode($key);
    $index++;
}

$_GET = array_merge($_GET, $queryArr);
$_GET['twig_file_name'] = isset($_GET[0])?$_GET[0]:'index';
//print_r( $_GET );

$twig->addGlobal('_GET', $_GET);
$twig->addGlobal('_POST', $_POST);// todo add SESSION var
$twig->addGlobal('_BASE', getBaseUrl());
$twig->addGlobal('_SERVER', $_SERVER);
$twig->addGlobal('_SIGNALS', $in_signals);
//$twig->addGlobal('_HEADER', getallheaders());
$twig->addGlobal('_ISDATASTAR', isset(getallheaders()['Datastar-Request'])?true:false);


$templatename = isset($_GET['twig_file_name'])?$_GET['twig_file_name'].'.twig':'index.twig'; // from fs
if ( !file_exists($TwigTemplatesDir.'/'.$templatename) ) {
    http_response_code(404);
    return;
}
if (isset(getallheaders()['Datastar-Request'])){
    $sse->sendHeaders();
    $sse->mergeFragments($twig->render($templatename), [
        'selector' => ($selector ?: ''),
        //'mergeMode' => FragmentMergeMode::Append,
        'useViewTransition' => true,
    ]);
    if (isset($signals)) $sse->mergeSignals($signals);
}
else {
    echo $twig->render($templatename);
}
//datastar-request:
//$sse->mergeFragments()
?>