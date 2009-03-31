<?php

require_once 'Sophpa/Resource.php';

class Sophpa_Database implements Countable 
{
	const VIEW_OPTION_DESCENDING = 'descending';
	const VIEW_OPTION_ENDKEY = 'endkey';
	const VIEW_OPTION_ENDKEY_DOCID = 'endkey_docid';
	const VIEW_OPTION_GROUP = 'group';
	const VIEW_OPTION_GROUP_LEVEL = 'group_level';
	const VIEW_OPTION_INCLUDE_DOCS = 'include_docs';
	const VIEW_OPTION_KEY = 'key';
	const VIEW_OPTION_KEYS = 'keys';
	const VIEW_OPTION_LIMIT = 'limit';
	const VIEW_OPTION_REDUCE = 'reduce';
	const VIEW_OPTION_SKIP = 'skip';
	const VIEW_OPTION_STALE = 'stale';
	const VIEW_OPTION_STARTKEY = 'startkey';
	const VIEW_OPTION_STARTKEY_DOCID = 'startkey_docid';

	/**
	 * Contains options to be JSON encoded, used in encodeOptions()
	 *
	 * @var array
	 */
	protected static $encode = array(
		self::VIEW_OPTION_KEY,
		self::VIEW_OPTION_STARTKEY,
		self::VIEW_OPTION_ENDKEY
	);

	/**
	 * Holds the injected server
	 *
	 * @var Sophpa_Server
	 */
	protected $server;

	/**
	 * @var Sophpa_Resource
	 */
	protected $resource;

	/**
	 * The name of the database
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Constructor
	 *
	 * @param Sophpa_Server $server
	 * @param string $name
	 */
	public function __construct(Sophpa_Server $server, $name)
	{
		$this->server = $server;
		$this->resource = $server->getResource();
		$this->name = $name;
	}

	/**
	 * Get the name of the database
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get info about the database
	 *
	 * @return array
	 */
	public function getInfo()
	{
		return $this->resource->get($this->name)->getContent();
	}
	
	/**
	 * Get total number of documents in database
	 *
	 * @return int
	 */
	public function count()
	{
		$content = $this->resource->get($this->name)->getContent();
		
		return $content['doc_count'];
	}

	/**
	 * Get a document based on its _id
	 *
	 * @param string $id
	 * @param array $options
	 * @return array
	 */
	public function get($id, array $options = array())
	{
		return $this->resource->get(array($this->name, $id), $options)->getContent();
	}

	/**
	 * Query the _all_docs view to get all documents in the database.
	 * All options available to regular views can be used.
	 *
	 * @param array $options
	 * @return array
	 */
	public function getAll(array $options = array())
	{
		return $this->view('_all_docs', $options);
	}

	/**
	 * Update or create a document. If no _id field is set, an _id will be generated
	 * by CouchDB. When used to update, a _rev field must be present.
	 *
	 * @param array $data
	 * @return array
	 */
	public function save(array $data)
	{
		if(!isset($data['_id'])) {
			$uuids = $this->server->getUuids();
			$data['_id'] = $uuids[0];
		}
		
		return $this->resource->put(array($this->name, $data['_id']), $data)->getContent();
	}

	/**
	 * Update and/or create a set of documents
	 *
	 * @param array $docs
	 */
	public function bulkSave(array $docs)
	{

	}

	/**
	 * Delete a document 
	 *
	 * @param array $doc
	 * @return array
	 */
	public function delete(array $doc)
	{
		return $this->resource->delete($doc['_id'], array('rev' => $doc['_rev']));
	}

	/**
	 * Query a permanent view.
	 * 
	 * Format of the name is design/view
	 *
	 * @param string $name
	 * @param array $options
	 * @return array
	 */
	public function view($name, array $options = array())
	{
		if(substr($name, 0, 1) != '_') {
			$parts = explode('/', $name);
			$name = '_design/' . $parts[0] . '/_view/' . $parts[1];
		}

		if(!count($options)) {
			return $this->resource->get($name)->getContent();
		}

		if(!array_key_exists(self::VIEW_OPTION_KEYS, $options)) {
			return $this->resource->get($name, $this->encodeOptions($options))->getContent();
		}
			
		$keys[self::VIEW_OPTION_KEYS] = $options[self::VIEW_OPTION_KEYS];
		unset($options[self::VIEW_OPTION_KEYS]);

		return $this->resource->post($name, $keys, $this->encodeOptions($options))->getContent();
	}

	/**
	 * Query a temporary view
	 *
	 * @param $map string
	 * @param $reduce string
	 * @param $language string
	 * @return array
	 */
	public function query($map, $reduce = null, array $options = array(), $language = 'javascript')
	{
		$body = array(
			'map' => $map,
			'reduce' => $reduce,
			'language' => $language
		);

		if(array_key_exists(self::VIEW_OPTION_KEYS, $options)) {
			$body[self::VIEW_OPTION_KEYS] = $options[self::VIEW_OPTION_KEYS];
			unset($options[self::VIEW_OPTION_KEYS]);
		}

		$response = $this->resource->post(
			array($this->name, '_temp_view'),
			$body,
			$this->encodeOptions($options),
			array('Content-Type' => 'application/json')
		);

		return $response->getContent();
	}

	/**
	 * CouchDB wants some query string options json encoded
	 *
	 * @param array $options
	 * @return array
	 */
	public function encodeOptions(array $options)
	{
		foreach($options as $key => &$value) {
			if(in_array($key, self::$encode)) {
				$value = json_encode($value);
			}
		}

		return $options;
	}

	/**
	 * The string representation of the database object is the database name
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getName();
	}
}

