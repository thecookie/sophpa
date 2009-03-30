<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Server.php';
require_once 'Sophpa/Response.php';

class Sophpa_ServerTest extends PHPUnit_Framework_TestCase
{
	protected $stubResource;
	
	protected $responseHeader = array();

	protected function setUp()
	{
		$http = $this->getMock('Sophpa_Http');

		$this->stubResource = $this->getMock(
			'Sophpa_Resource',
			array('delete', 'get', 'head','post', 'put', '__toString'),
			array($http, 'http://localhost:5984')
		);

		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function testShouldReturnListOfDatabases()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '["testdatabase","somedatabase"]');

		$this->stubResource	->expects($this->any())
							->method('get')
							->will($this->returnValue($response));

		$server = new Sophpa_Server($this->stubResource);
		
		$this->assertContains('testdatabase', $server->listDatabases());
	}

	public function testShouldCreateDatabase()
	{
		$response = new Sophpa_Response(201, $this->responseHeader, '{"ok":true}');

		$this->stubResource->expects($this->any())
							 ->method('put')
							 ->will($this->returnValue($response));
		
		$server = new Sophpa_Server($this->stubResource);
		
		$this->assertTrue($server->createDatabase('testdatabase') instanceof Sophpa_Database);
	}

	/**
	 * @expectedException Sophpa_Resource_ConflictException
	 */
	public function testShouldThrowExceptionIfDatabaseExists()
	{
		require_once 'Sophpa/Resource/ConflictException.php';

		$this->stubResource->expects($this->any())
							->method('put')
							->will($this->throwException(new Sophpa_Resource_ConflictException()));
		$server = new Sophpa_Server($this->stubResource);
		
		$server->createDatabase('db_name_that_already_exist');
	}

	public function testShouldReturnDatabase()
	{
		$server = new Sophpa_Server($this->stubResource);
		
		$db = $server->getDatabase('database_name');
		
		$this->assertTrue($db instanceof Sophpa_Database);
	}

	public function testShouldDeleteDatabase()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '{"ok": true}');

		$this->stubResource->expects($this->once())
							->method('delete')
							->will($this->returnValue($response));
		$server = new Sophpa_Server($this->stubResource);
		
		$server->deleteDatabase('db_name_that_exists');
	}

	public function testShouldGetVersion()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '{"couchdb":"Welcome","version":"0.9.0a749067"}');

		$this->stubResource->expects($this->any())
							 ->method('get')
							 ->will($this->returnValue($response));
		$server = new Sophpa_Server($this->stubResource);
		
		$this->assertEquals('0.9.0a749067', $server->getVersion());
	}
}
