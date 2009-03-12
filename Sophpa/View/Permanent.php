<?php

require_once 'Sophpa/View.php';

class Sophpa_View_Permanent extends Sophpa_View
{
	protected $name;

	public function __construct(Sophpa_Resource $resource, $name = null)
	{
		parent::__construct($resource);
		$this->name = $name;
	}

	/**
	 * Query a permanent view
	 *
	 * @param array $options
	 * @return array
	 */
	public function query(array $options = array())
	{
		if(!count($options)) {
			return $this->resource->get('/');
		}

		if(!array_key_exists(self::OPTION_KEYS, $options)) {
			return $this->resource->get('/', array(), $this->encodeOptions($options));
		}
			
		$keys[self::OPTION_KEYS] = $options[self::OPTION_KEYS];
		unset($options[self::OPTION_KEYS]);

		return $this->resource->post('/', $keys, array(), $this->encodeOptions($options));
	}
}

