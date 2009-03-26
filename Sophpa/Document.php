<?php

class Sophpa_Document extends ArrayObject
{
	/**
	 * Document data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor
	 *
	 * @param array|string $data
	 * @throws InvalidArgumentException
	 */
	public function __construct($data)
	{
		if(is_string($data)) {
			$this->data = json_decode($data, true);
			if($this->data === null) {
				throw new InvalidArgumentException('JSON not valid');
			}
		} elseif(is_array($data)) {
			$this->data = $data;
		} else {
			throw new InvalidArgumentException('Given data could not be used to create a document');
		}

		parent::__construct($this->data);
	}

	/**
	 * Merge
	 *
	 * @param array $data
	 */
	public function merge(array $data)
	{
		$this->exchangeArray(array_merge($this->data, $data));
	}
}
