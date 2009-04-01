<?php

require_once 'Sophpa/Resource.php';

class Sophpa_Server
{
	/**
	 * Holds the injected resource handler
	 *
	 * @var Sophpa_Resource
	 */
	protected $resource;

	/**
	 * Cache of server generated uuids
	 *
	 * @var array
	 */
	protected $uuidCache = array();

	/**
	 * Constructor
	 *
	 * @param Sophpa_Resource $resource
	 */
	public function __construct(Sophpa_Resource $resource)
	{
		$this->resource = $resource;
	}

	/**
	 * Get the injected resource
	 *
	 * @return Sophpa_Resource
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * Get a list of databases
	 *
	 * @return array
	 */	
	public function listDatabases()
	{
		return $this->resource->get('_all_dbs')->getContent();
	}

	/**
	 * Get an instance of given database
	 *
	 * @param string $name
	 * @return Sophpa_Database
	 */
	public function getDatabase($name)
	{
		require_once 'Sophpa/Database.php';
		
		return new Sophpa_Database($this, $name);
	}

	/**
	 * Create a new database
	 *
	 * @param string $name
	 * @return Sophpa_Database
	 */
	public function createDatabase($name)
	{
		$this->resource->put($name);

		return $this->getDatabase($name);
	}

	/**
	 * Delete a database
	 *
	 * @param Sophpa_Database|string $db
	 * @return void
	 */
	public function deleteDatabase($db)
	{
		$this->resource->delete((string)$db);
		unset($db);
	}
	
	/**
	 * Get the version of the server
	 *
	 * @return string
	 */
	public function getVersion()
	{
		$content = $this->resource->get('/')->getContent();
		
		return $content['version'];
	}

	/**
	 * Retrive a list of server generated UUIDs
	 *
	 * @param int $count
	 * @return array
	 */
	public function getUuid($count = 1)
	{
		if(count($this->uuidCache) < $count) {
			$content = $this->resource->get('_uuids', array('count' => 10 + $count))->getContent();
			$this->uuidCache += $content['uuids'];
		}

		return array_splice($this->uuidCache, 0, $count);
	}

	/**
	 * Restart the CouchDB instance
	 *
	 * @return bool
	 */
	public function restart()
	{
		$content = $this->resource->post('_restart')->getContent();
		
		return (bool)$content['ok'];
	}
}
