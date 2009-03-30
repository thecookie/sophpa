<?php

class Sophpa_Resource
{
	/**
	 * Holds the injected Sophpa_Http
	 *
	 * @var Sophpa_Http
	 */
	public $http;

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
	 * @param string $path
	 * @param array $header
	 * @param array $param
	 * @return Sophpa_Response 
	 */
	public function delete($path, array $param = array(), array $header = array())
	{
		return $this->request('DELETE', $path, null, $header, $param);
	}

	/**
	 * Perform a get request
	 *
	 * @param string $path
	 * @param array $header
	 * @param array $param
	 * @return Sophpa_Response
	 */
	public function get($path, array $param = array(), array $header = array())
	{
		return $this->request('GET', $path, null, $header, $param);
	}

	/**
	 * Perform a head request
	 *
	 * @param string $path
	 * @param array $header
	 * @param array $param
	 * @return Sophpa_Response
	 */
	public function head($path, array $param = array(), array $header = array())
	{
		return $this->request('HEAD', $path, null, $header, $param);
	}

	/**
	 * Perform a post request
	 *
	 * @param string $path
	 * @param string $content
	 * @param array $header
	 * @param array $param
	 * @return Sophpa_Response
	 */
	public function post($path, $content, array $param = array(), array $header = array())
	{
		return $this->request('POST', $path, $content, $header, $param);
	}

	/**
	 * Perform a put request
	 *
	 * @param string $path
	 * @param string $content
	 * @param array $header
	 * @param array $param
	 * @return Sophpa_Response
	 */
	public function put($path, $content = null, array $param = array(), array $header = array())
	{
		return $this->request('PUT', $path, $content, $header, $param);
	}

	protected function request($method, $path, $content, $header, $param)
	{
		require_once 'Sophpa/Util.php';
		
		$url = Sophpa_Util::uri($this->uri, $path, $param);

		if(!is_string($content)) {
			$body = json_encode($content);
		} else {
			$body = $content;
		}

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
}

