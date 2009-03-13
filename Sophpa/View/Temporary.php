<?php

require_once 'Sophpa/View.php';

class Sophpa_View_Temporary extends Sophpa_View
{
	/**
	 * Holds the view body
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor
	 *
	 * @param Sophpa_Resource $resource
	 * @param string $map
	 * @param string $reduce
	 * @param string $language
	 */
	public function __construct(Sophpa_Resource $resource, $map, $reduce = null, $language = 'javascript')
	{
		parent::__construct($resource);
		$this->data['map'] = $map;
		$this->data['reduce'] = $reduce;
		$this->data['language'] = $language;
	}

	/**
	 * Query a temporary view
	 *
	 * @param array $options
	 * @return array
	 */
	public function query(array $options = array())
	{
		$body = $this->data;

		if(array_key_exists(self::OPTION_KEYS, $options)) {
			$body[self::OPTION_KEYS] = $options[self::OPTION_KEYS];
			unset($options[self::OPTION_KEYS]);
		}

		return $this->resource->post('/', $body, array('Content-Type' => 'application/json'), $this->encodeOptions($options));
	}
}

