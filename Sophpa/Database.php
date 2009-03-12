<?php

require_once 'Sophpa/Resource.php';

class Sophpa_Database implements Countable 
{
	/**
	 * Holds the injected resource
	 *
	 * @var Sophpa_Resource
	 */
	protected $resource;

	protected $name;

	/**
	 * Constructor
	 *
	 * @param Sophpa_Resource $resource
	 * @param string $name
	 */
	public function __construct(Sophpa_Resource $resource, $name = null)
	{
		$this->resource = $resource;
		// @todo verify name
		$this->name = $name;
	}

	/**
	 * Get the name of the database
	 *
	 * @return string
	 */
	public function getName()
	{
		if($this->name === null) {
			$info = $this->getInfo();
			$this->name = $info['db_name'];
		}

		return $this->name;
	}

	public function getInfo()
	{
		$response = $this->resource->get('/');
		
		return $response->getContent();
	}
	
	/**
	 * Get total number of documents in database
	 *
	 * @return int
	 */
	public function count()
	{
		$content = $this->resource->get('/')->getContent();
		
		return $content['doc_count'];
	}

	/**
	 * Get a document based on its ID
	 *
	 * @param string $id
	 * @param array $options
	 * @return Sophpa_Document
	 */
	public function get($id, array $options = array())
	{
		$content = $this->resource->get($id, array(), $options)->getContent();
		
		require_once 'Sophpa/Document.php';
		
		return new Sophpa_Document($content);
	}

	/**
	 * Query the _all_docs view
	 *
	 * @param array $options
	 * @return Sophpa_ViewResult
	 */
	public function getAll(array $options = array())
	{
		return $this->view('_all_docs', $options);
	}

	/**
	 * Update or create a document based on id
	 * 
	 * The data must contain a _id field. When used to update, a _rev field 
	 * must be present.
	 *
	 * @param Sophpa_Document|array $data
	 * @return void
	 */
	public function save($data)
	{
		$response = $this->resource->put($data['_id'], $data);
		$content = $response->getContent();

		if($data instanceof Sophpa_Document) {
			$data['_id'] = $content['id'];
			$data['_rev'] = $content['rev'];
		}
	}

	/**
	 * Update and/or create a set of documents
	 *
	 */
	public function bulkSave($documents)
	{

	}

	/**
	 * Create a new document with a server generated ID
	 *
	 * @param string|array|Sophpa_Document $data
	 * @return int id
	 */ 
	public function create($data)
	{
		// Workaround due to array('value','value') getting encoded to ["value","value"]
		// rather than {"0":"value","1":"value"}
		if(is_array($data)) {
			$data = new ArrayObject($data);
		}

		$response = $this->resource->post('/', $data)->getContent();

		return $response['id'];
	}

	/**
	 * Delete a document based on ID. 
	 *
	 * @param Sophpa_Document|string $docOrId
	 * @param string $rev
	 * @return void
	 */
	public function delete($docOrId, $rev = null)
	{
		$id = isset($docOrId['_id']) ? $docOrId['_id'] : $docOrId;
		$rev = is_null($rev) ? array() : array('rev' => $rev);
		
		$this->resource->delete($id, array(), $rev);
	}

	/**
	 * Query a permanent view.
	 * 
	 * Format of the name is design/view
	 *
	 * @todo This needs som cleaning up
	 * @param string $name
	 * @param array $options
	 * @return Sophpa_ViewResult
	 */
	public function view($name, array $options = array())
	{
		require_once 'Sophpa/Util.php';
		require_once 'Sophpa/ViewResult.php';
		require_once 'Sophpa/View/Permanent.php';
		
		if(substr($name, 0, 1) != '_') {
			$parts = explode('/', $name);
			$name = '_design/' . $parts[0] . '/_view/' . $parts[1];
		}

		$resource = new Sophpa_Resource(
			$this->resource->http,
			Sophpa_Util::uri($this->resource->uri, $name)
		);
		
		return new Sophpa_ViewResult(
			new Sophpa_View_Permanent($resource, $name),
			$options
		);
	}

	/**
	 * String representation of database.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getName();
	}
}

