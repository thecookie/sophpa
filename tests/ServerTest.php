<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Server.php';
require_once 'Sophpa/Response.php';

class Sophpa_ServerTest extends PHPUnit_Framework_TestCase
{
	protected $mockResource;
	
	protected $responseHeader = array();

	protected function setUp()
	{
		$http = $this->getMock('Sophpa_Http');

		$this->mockResource = $this->getMock(
			'Sophpa_Resource',
			array('delete', 'get', 'head','post', 'put', '__toString'),
			array($http, 'http://localhost:5984')
		);
	}

	public function testGetsListOfDatabases()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '["testdatabase","somedatabase"]');

		$this->mockResource->expects($this->once())
						   ->method('get')
						   ->with($this->equalTo('_all_dbs'))
						   ->will($this->returnValue($response));

		$server = new Sophpa_Server($this->mockResource);
		
		$this->assertContains('testdatabase', $server->listDatabases());
	}

	public function testCreatesDatabase()
	{
		$response = new Sophpa_Response(201, $this->responseHeader, '{"ok":true}');

		$this->mockResource->expects($this->once())
						   ->method('put')
						   ->with($this->equalTo('testdatabase'))
						   ->will($this->returnValue($response));
		
		$server = new Sophpa_Server($this->mockResource);
		$db = $server->createDatabase('testdatabase');

		$this->assertTrue($db instanceof Sophpa_Database);
		$this->assertEquals('testdatabase', $db->getName());
	}

	/**
	 * @expectedException Sophpa_Resource_ConflictException
	 */
	public function testThrowsExceptionIfDatabaseExists()
	{
		require_once 'Sophpa/Resource/ConflictException.php';

		$this->mockResource->expects($this->once())
							->method('put')
							->with($this->equalTo('db_name_that_already_exist'))
							->will($this->throwException(new Sophpa_Resource_ConflictException()));
		$server = new Sophpa_Server($this->mockResource);
		
		$server->createDatabase('db_name_that_already_exist');
	}

	public function testShouldReturnDatabase()
	{
		$server = new Sophpa_Server($this->mockResource);
		
		$db = $server->getDatabase('database_name');
		
		$this->assertTrue($db instanceof Sophpa_Database);
		$this->assertEquals('database_name', $db->getName());
	}

	public function testShouldDeleteDatabase()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '{"ok": true}');

		$this->mockResource->expects($this->once())
							->method('delete')
							->with($this->equalTo('db_name_that_exists'))
							->will($this->returnValue($response));
		$server = new Sophpa_Server($this->mockResource);
		
		$server->deleteDatabase('db_name_that_exists');
	}

	public function testShouldGetVersion()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '{"couchdb":"Welcome","version":"0.9.0a749067"}');

		$this->mockResource->expects($this->once())
							 ->method('get')
							 ->with($this->equalTo('/'))
							 ->will($this->returnValue($response));
		$server = new Sophpa_Server($this->mockResource);
		
		$this->assertEquals('0.9.0a749067', $server->getVersion());
	}

	public function testGetsUuids()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '{"uuids":["67a1b79689f7af242fbb7e5bec97a722"]}');
		$this->mockResource->expects($this->once())
							 ->method('get')
							 ->with($this->equalTo('_uuids'), $this->equalTo(array('count' => 1)))
							 ->will($this->returnValue($response));
		$server = new Sophpa_Server($this->mockResource);
		$uuids = $server->getUuids();

		$this->assertEquals('67a1b79689f7af242fbb7e5bec97a722', $uuids[0]);
	}
}
