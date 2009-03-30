<?php

class Sophpa_Util
{
	/**
	 * Assembles a url from 3 given parts
	 * 
	 * @param string $base
	 * @param array|string $path
	 * @param array $query
	 * @return string URL
	 */
	public static function uri($base, $path = null, array $query = array())
	{
		$uri = rtrim($base, '/');
		
		if(is_array($path)) {
			$uri .= '/' . implode('/', trim($path, '/'));
		} else {
			$uri .= '/' . trim($path, '/');
		}

		if($query) {
			$uri .= '?' . http_build_query($query);
		}
		
		return $uri;
	}
}
