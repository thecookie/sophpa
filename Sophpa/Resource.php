<?php

class Sophpa_Resource
{
	/**
	 * Holds the injected Sophpa_Http
	 *
	 * @var Sophpa_Http
	 */
	public $http;

	/**
	 * @var string
	 */
	public $uri;

	/**
	 * Constructor
	 *
	 * @param Sophpa_Http $http
	 * @param string $uri
	 */
	public function __construct(Sophpa_Http $http, $uri)
	{
		$this->http = $http;
		$this->uri = $uri;
	}

	/**
	 * Perform a delete request
	 *
	 * @param string|array $path
	 * @param array $param
	 * @param array $header
	 * 
	 * @return Sophpa_Response 
	 */
	public function delete($path, array $param = array(), array $header = array())
	{
		return $this->request('DELETE', $path, null, $header, $param);
	}

	/**
	 * Perform a get request
	 *
	 * @param string|array $path
	 * @param array $param
	 * @param array $header
	 * 
	 * @return Sophpa_Response
	 */
	public function get($path, array $param = array(), array $header = array())
	{
		return $this->request('GET', $path, null, $header, $param);
	}

	/**
	 * Perform a head request
	 *
	 * @param string|array $path
	 * @param array $param
	 * @param array $header
	 * 
	 * @return Sophpa_Response
	 */
	public function head($path, array $param = array(), array $header = array())
	{
		return $this->request('HEAD', $path, null, $header, $param);
	}

	/**
	 * Perform a post request
	 *
	 * @param string|array $path
	 * @param string $content
	 * @param array $param
	 * @param array $header
	 * 
	 * @return Sophpa_Response
	 */
	public function post($path, $content = null, array $param = array(), array $header = array())
	{
		return $this->request('POST', $path, $content, $header, $param);
	}

	/**
	 * Perform a put request
	 *
	 * @param string|array $path
	 * @param string $content
	 * @param array $param
	 * @param array $header
	 * 
	 * @return Sophpa_Response
	 */
	public function put($path, $content = null, array $param = array(), array $header = array())
	{
		return $this->request('PUT', $path, $content, $header, $param);
	}

	protected function request($method, $path, $content, $header, $param)
	{		
		$url = $this->assembleUrl($this->uri, $path, $param);

		$body = is_string($content) ? $content : json_encode($content);

		$response = $this->http->request($method, $url, $body, $header);
	
		if(!$response->isOk()) {
			$status = $response->getStatus();
			$error = $response->getContent();
			
			if($status == 404) {
				require_once 'Sophpa/Resource/NotFoundException.php';
				throw new Sophpa_Resource_NotFoundException($error['reason'], $status);
			} elseif($status == 409) {
				require_once 'Sophpa/Resource/ConflictException.php';
				throw new Sophpa_Resource_ConflictException($error['reason'], $status);
			} elseif($status == 412) {
				require_once 'Sophpa/Resource/ConflictException.php';
				throw new Sophpa_Resource_ConflictException($error['reason'], $status);
			} else {
				require_once 'Sophpa/Exception.php';
				throw new Sophpa_Exception($error['reason'], $status);
			}
		}

		return $response;
	}

	/**
	 * Assembles a url from 3 given parts
	 * 
	 * @param string $base
	 * @param string|array $path
	 * @param array $query
	 * 
	 * @return string URL
	 */
	protected function assembleUrl($base, $paths = null, array $query = array())
	{
		$uri = rtrim($base, '/');

		$paths = (array)$paths;
		foreach($paths as &$path) {
			$path = trim($path, '/');
		}
		$uri .= '/' . implode($paths, '/');
		
		if($query) {
			$uri .= '?' . http_build_query($query);
		}
		
		return $uri;
	}
}

