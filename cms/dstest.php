<?php
require_once 'vendor/autoload.php';
use starfederation\datastar\enums\EventType;
use starfederation\datastar\enums\FragmentMergeMode;
use starfederation\datastar\ServerSentEventGenerator;

// Creates a new `ServerSentEventGenerator` instance.
$sse = new ServerSentEventGenerator();

// Sends the response headers. 
// If your framework has its own way of sending response headers, manually send the headers returned by `ServerSentEventGenerator::headers()` instead.
$sse->sendHeaders();

// Merges HTML fragments into the DOM.
$sse->mergeFragments('<div></div>
<br>', [
    'selector' => '#my-div',
    'mergeMode' => FragmentMergeMode::Append,
    'useViewTransition' => true,
]);

// Removes HTML fragments from the DOM.
$sse->removeFragments('#my-div');

// Merges signals.
$sse->mergeSignals('{foo: 123}', [
    'onlyIfMissing' => true,
]);

// Removes signals.
$sse->removeSignals(['foo', 'bar']);

// Executes JavaScript in the browser.
$sse->executeScript('console.log("Hello, world!")');

// Redirects the browser by setting the location to the provided URI.
//$sse->location('/guide');
?>