<?php

interface Sophpa_Http
{
	/**
	 * Perform a HTTP request
	 *
	 * @param string $method
	 * @param string $url
	 * @param string $content
	 * @param array $header
	 * @return Sophpa_Response
	 */
	public function request($method, $url, $content, $header);
}
