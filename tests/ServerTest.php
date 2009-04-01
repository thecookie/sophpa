<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Server.php';
require_once 'Sophpa/Resource.php';
require_once 'Sophpa/Response.php';

class Sophpa_ServerTest extends PHPUnit_Framework_TestCase
{
	protected $mockResource;
	protected $mockResponse;
	
	protected $responseHeader = array();

	protected function setUp()
	{
		$http = $this->getMock('Sophpa_Http');

		$this->mockResource = $this->getMock(
			'Sophpa_Resource',
			array('delete', 'get', 'head','post', 'put', '__toString'),
			array($http, 'http://localhost:5984')
		);

		$this->mockResponse = $this->getMock(
			'Sophpa_Response',
			array('getContent'),
			array(),
			'',
			false
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

	public function testInitializesRestart()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '{"ok":true}');
		$this->mockResource->expects($this->once())
						   ->method('post')
						   ->with($this->equalTo('_restart'))
						   ->will($this->returnValue($response));
		$server = new Sophpa_Server($this->mockResource);

		$this->assertTrue($server->restart());
	}

	public function testAquiresCorrectNumberOfUuidsWhenEmptyCache()
	{
		$response = array('uuids' => array(
			'1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
		));
		
		$this->mockResponse->expects($this->once())
						   ->method('getContent')
						   ->will($this->returnValue($response));

		$this->mockResource->expects($this->once())
						   ->method('get')
						   ->with('_uuids')
						   ->will($this->returnValue($this->mockResponse));

		$server = new Sophpa_Server($this->mockResource);
		$uuids = $server->getUuid(2);

		$this->assertEquals(array('1', '2'), $uuids);
	}

	public function testAquiresCorrectNumberOfCachedUuids()
	{
		$response = array('uuids' => array(
			'1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
		));
		
		$this->mockResponse->expects($this->once())
						   ->method('getContent')
						   ->will($this->returnValue($response));

		$this->mockResource->expects($this->once())
						   ->method('get')
						   ->with('_uuids')
						   ->will($this->returnValue($this->mockResponse));

		$server = new Sophpa_Server($this->mockResource);
		$uuids = $server->getUuid(2);
		$uuids2 = $server->getUuid(10);

		$this->assertEquals(array('1', '2'), $uuids);
		$this->assertEquals(array('3', '4', '5', '6', '7', '8', '9', '10', '11', '12'), $uuids2);
	}
}
