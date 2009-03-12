<?php

class Sophpa_Row extends ArrayObject
{
	protected $data;

	public function __construct($data)
	{
		$this->data = $data;
		parent::__construct($data);
	}
}

