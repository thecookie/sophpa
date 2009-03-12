<?php

class Sophpa_Response
{
	protected $status;
	protected $header;
	protected $content;

	public function __construct($status, $header, $content)
	{
		$this->status = $status;
		$this->header = $header;
		$this->content = json_decode($content, true);
	}
	
	/**
	 * Create a response object from raw http response
	 *
	 * @param string $rawResponse
	 * @return Sophpa_Response
	 */
	public static function createFromRaw($rawResponse)
	{
		list($rawHeaders, $body) = explode("\r\n\r\n", $rawResponse);

		$rawHeaders = explode("\r\n", $rawHeaders);

		$status = (int) substr(array_shift($rawHeaders), 9, 3);

		$headers = array();
        
		foreach ($rawHeaders as $header) {
			list($key, $value) = explode(': ', $header);
			$headers[$key] = $value;
		}
		
		return new self($status, $header, $body);
	}

	/**
	 * Get the response status code
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}	

	public function getHeaders()
	{
		return $this->header;
	}

	public function getHeader($header)
	{
		$header = ucwords(strtolower($header));

		if(isset($this->header[$header])) {
			return $this->header[$header];
		}

		return null;
	}
	
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Whether or not the response is considered ok
	 *
	 * @return bool
	 */
	public function isOk()
	{
		return $this->status >= 200 && $this->status <= 299;
	}
}
