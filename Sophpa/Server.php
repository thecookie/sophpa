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
	 * Get a list of databases of given server
	 *
	 * @return array
	 */	
	public function listDatabases()
	{
		$response = $this->resource->get('/_all_dbs');

		return $response->getContent();
	}

	/**
	 * Get an instance of given database name
	 *
	 * @return Sophpa_Database
	 */
	public function getDatabase($name)
	{
		require_once 'Sophpa/Database.php';
		require_once 'Sophpa/Util.php';
		
		$resource = new Sophpa_Resource(
			$this->resource->http,
			Sophpa_Util::uri($this->resource->uri, $name)
		);
		
		return new Sophpa_Database($resource, $name);
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
	 * Delete a database with the given name
	 *
	 * @param string|Sophpa_Database $database
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

	public function __toString()
	{
		return sprintf('%s %s', get_class(), $this->resource);
	}
}
