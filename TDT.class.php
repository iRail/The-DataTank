<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 * 
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 * 
 * Helper classes that are specifically designed for TDT. When developing modules you can use these for better performance
 */

include_once("error/Exceptions.class.php");

class TDT{
     
/**
 * The HttpRequest stolen from Drupal. Drupal is licensed GPLv2 or later. This is compatible with our AGPL license.
 * Use this function to get some content
 */
     public static function HttpRequest($url, $headers = array(), $method = 'GET', $data = NULL, $retry = 3) {
	  $result = new stdClass();

	  // Parse the URL and make sure we can handle the schema.
	  $uri = parse_url($url);

	  if ($uri == FALSE) {
	       throw new CouldNotParseUrlTDTException($url);
	  }

	  if (!isset($uri['scheme'])) {
	       throw new CouldNotParseUrlTDTException("Forgot to add http(s)? " . $url);
	  }

	  switch ($uri['scheme']) {
	  case 'http':
	  case 'feed':
	       $port = isset($uri['port']) ? $uri['port'] : 80;
	       $host = $uri['host'] . ($port != 80 ? ':' . $port : '');
	       $fp = @fsockopen($uri['host'], $port, $errno, $errstr, 15);
	       break;
	  case 'https':
	       // Note: Only works for PHP 4.3 compiled with OpenSSL.
	       $port = isset($uri['port']) ? $uri['port'] : 443;
	       $host = $uri['host'] . ($port != 443 ? ':' . $port : '');
	       $fp = @fsockopen('ssl://' . $uri['host'], $port, $errno, $errstr, 20);
	       break;
	  default:
	       $result->error = 'invalid schema ' . $uri['scheme'];
	       $result->code = -1003;
	       return $result;
	  }

	  // Make sure the socket opened properly.
	  if (!$fp) {
	       throw new HttpOutException($url);
	  }

	  // Construct the path to act on.
	  $path = isset($uri['path']) ? $uri['path'] : '/';
	  if (isset($uri['query'])) {
	       $path .= '?' . $uri['query'];
	  }

	  // Create HTTP request.
	  $defaults = array(
	       // RFC 2616: "non-standard ports MUST, default ports MAY be included".
	       // We don't add the port to prevent from breaking rewrite rules checking the
	       // host that do not take into account the port number.
	       'Host' => "Host: $host", 
	       'User-Agent' => 'User-Agent: The DataTank', //TODO - dynamic user agent
	       );

	  // Only add Content-Length if we actually have any content or if it is a POST
	  // or PUT request. Some non-standard servers get confused by Content-Length in
	  // at least HEAD/GET requests, and Squid always requires Content-Length in
	  // POST/PUT requests.
	  $content_length = strlen($data);
	  if ($content_length > 0 || $method == 'POST' || $method == 'PUT') {
	       $defaults['Content-Length'] = 'Content-Length: ' . $content_length;
	  }

	  // If the server url has a user then attempt to use basic authentication
	  if (isset($uri['user'])) {
	       $defaults['Authorization'] = 'Authorization: Basic ' . base64_encode($uri['user'] . (!empty($uri['pass']) ? ":" . $uri['pass'] : ''));
	  }

	  foreach ($headers as $header => $value) {
	       $defaults[$header] = $header . ': ' . $value;
	  }

	  $request = $method . ' ' . $path . " HTTP/1.0\r\n";
	  $request .= implode("\r\n", $defaults);
	  $request .= "\r\n\r\n";
	  $request .= $data;

	  $result->request = $request;

	  fwrite($fp, $request);

	  // Fetch response.
	  $response = '';
	  while (!feof($fp) && $chunk = fread($fp, 1024)) {
	       $response .= $chunk;
	  }
	  fclose($fp);

	  // Parse response.
	  list($split, $result->data) = explode("\r\n\r\n", $response, 2);
	  $split = preg_split("/\r\n|\n|\r/", $split);

	  list($protocol, $code, $status_message) = explode(' ', trim(array_shift($split)), 3);
	  $result->protocol = $protocol;
	  $result->status_message = $status_message;

	  $result->headers = array();

	  // Parse headers.
	  while ($line = trim(array_shift($split))) {
	       list($header, $value) = explode(':', $line, 2);
	       if (isset($result->headers[$header]) && $header == 'Set-Cookie') {
		    // RFC 2109: the Set-Cookie response header comprises the token Set-
		    // Cookie:, followed by a comma-separated list of one or more cookies.
		    $result->headers[$header] .= ',' . trim($value);
	       }
	       else {
		    $result->headers[$header] = trim($value);
	       }
	  }

	  $responses = array(
	       100 => 'Continue', 
	       101 => 'Switching Protocols', 
	       200 => 'OK', 
	       201 => 'Created', 
	       202 => 'Accepted', 
	       203 => 'Non-Authoritative Information', 
	       204 => 'No Content', 
	       205 => 'Reset Content', 
	       206 => 'Partial Content', 
	       300 => 'Multiple Choices', 
	       301 => 'Moved Permanently', 
	       302 => 'Found', 
	       303 => 'See Other', 
	       304 => 'Not Modified', 
	       305 => 'Use Proxy', 
	       307 => 'Temporary Redirect', 
	       400 => 'Bad Request', 
	       401 => 'Unauthorized', 
	       402 => 'Payment Required', 
	       403 => 'Forbidden', 
	       404 => 'Not Found', 
	       405 => 'Method Not Allowed', 
	       406 => 'Not Acceptable', 
	       407 => 'Proxy Authentication Required', 
	       408 => 'Request Time-out', 
	       409 => 'Conflict', 
	       410 => 'Gone', 
	       411 => 'Length Required', 
	       412 => 'Precondition Failed', 
	       413 => 'Request Entity Too Large', 
	       414 => 'Request-URI Too Large', 
	       415 => 'Unsupported Media Type', 
	       416 => 'Requested range not satisfiable', 
	       417 => 'Expectation Failed', 
	       500 => 'Internal Server Error', 
	       501 => 'Not Implemented', 
	       502 => 'Bad Gateway', 
	       503 => 'Service Unavailable', 
	       504 => 'Gateway Time-out', 
	       505 => 'HTTP Version not supported',
	       );
	  // RFC 2616 states that all unknown HTTP codes must be treated the same as the
	  // base code in their class.
	  if (!isset($responses[$code])) {
	       $code = floor($code / 100) * 100;
	  }

	  switch ($code) {
	  case 200: // OK
	  case 304: // Not modified
	       break;
	  case 301: // Moved permanently
	  case 302: // Moved temporarily
	  case 307: // Moved temporarily
	       $location = $result->headers['Location'];

	       if ($retry) {
		    $result = self::HttpRequest($result->headers['Location'], $headers, $method, $data, --$retry);
		    $result->redirect_code = $result->code;
	       }
	       $result->redirect_url = $location;

	       break;
	  default:
	       $result->error = $status_message;
	  }

	  $result->code = $code;
	  return $result;
     }

}



?>