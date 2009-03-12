<?php

require_once 'Sophpa/Row.php';

class Sophpa_ViewResult implements Countable, Iterator
{
	/**
	 * Holds injected view reference
	 *
	 * @var Sophpa_View
	 */
	protected $view;
	protected $options;

	protected $rawResult;
	protected $totalRows;
	
	protected $pointer;
	protected $rowObjects;

	public function __construct(Sophpa_View $view, array $options = array())
	{
		$this->view = $view;
		$this->options = $options;
	}

	protected function fetch()
	{
		$this->rawResult = $this->view->query($this->options)->getContent();
		$this->totalRows = count($this->rawResult['rows']);
	}

	/**
	 * Lazy load a specific row.
	 * 
	 * @todo Hmm. Is this really useful?
	 * @param int $id
	 * @return Sophpa_Row|null
	 */
	private function getRow($id)
	{
		if(!$this->rawResult) {
			$this->fetch();
		}
		
		if($id >= $this->totalRows || $id < 0) {
			return null;
		}

		if(isset($this->rowObjects[$id])) {
			return $this->rowObjects[$id];
		}

		if(isset($this->rawResult['rows'][$id])) {
			$this->rowObjects[$id] = new Sophpa_Row($this->rawResult['rows'][$id]);
			return $this->rowObjects[$id];
		}
	}

	/**
	 * 
	 * @return int
	 */
	public function count()
	{
		if(!$this->rawResult) {
			$this->fetch();
		}

		return $this->totalRows;
	}

	public function current()
	{
		return $this->getRow($this->pointer);
	}
	
	public function rewind()
	{
		$this->pointer = 0;
	}
	
	public function key()
	{
		return $this->pointer;
	}
	
	public function next()
	{
		$row = $this->getRow($this->pointer);
		if($row) {
			$this->pointer++;
		}

		return $row;
	}
	
	public function valid()
	{
		return $this->current() !== null;
	}
}

