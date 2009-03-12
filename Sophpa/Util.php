<?php

class Sophpa_Util
{
	/**
	 * Assembles a url from 3 given parts
	 * 
	 * @todo Add support for unicode
	 * @param string $base
	 * @param string $path
	 * @param array $query
	 * @return string URL
	 */
	public static function uri($base, $path = null, $query = null)
	{
		$uri = rtrim($base, '/');
		
		$uri .= '/' . trim($path, '/');
		
		if($query) {
			$uri .= '?' . http_build_query($query);
		}
		
		return $uri;
	}
}
