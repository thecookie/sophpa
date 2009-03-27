<?php

require_once 'Sophpa/Http.php';

class Sophpa_Http_Curl implements Sophpa_Http
{
	public function __construct()
	{
		// @todo verify that curl is installed?
	}

	public function request($method, $url, $content, $header)
	{
		// Set up curl
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_HEADER, true);
		curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 3);

		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($handle, CURLOPT_URL, $url);
		
		// Default values for header
		//curl_setopt($handle, CURLOPT_USERAGENT, '');

		// Custom header
		$headers = array();
		foreach ($header as $key => $value) {
			$headers[] = $key.': '.$value;
		}
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
		
		// @todo add support for array key=>value stuff
		curl_setopt($handle, CURLOPT_POSTFIELDS, $content);

		$response = curl_exec($handle);

		if(curl_errno($handle)) {
			require_once 'Sophpa/Exception.php';
			throw new Sophpa_Exception(curl_error($handle), curl_errno($handle));			
		}

		curl_close($handle);

		if (stripos($response, "HTTP/1.1 100 Continue\r\n\r\n") !== false) {
			$response = str_ireplace("HTTP/1.1 100 Continue\r\n\r\n", '', $response);
		}

		require_once 'Sophpa/Response.php';
		return Sophpa_Response::createFromRaw($response);
	}
}
