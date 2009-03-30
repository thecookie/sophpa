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

	public function __construct(Sophpa_Resource $resource)
	{
		$this->resource = $resource;
	}

	/**
	 * Get a list of databases
	 *
	 * @return array
	 */	
	public function listDatabases()
	{
		return $this->resource->get('/_all_dbs')->getContent();
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
//		require_once 'Sophpa/Util.php';
//		
//		$resource = new Sophpa_Resource(
//			$this->resource->http,
//			Sophpa_Util::uri($this->resource->uri, $name)
//		);
		
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
		$this->resource->delete($db);
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
	public function getUuids($count = 1)
	{
		$content = $this->resource->get('_uuids', array('count' => $count))->getContent();

		return $content['uuids'];
	}
}
