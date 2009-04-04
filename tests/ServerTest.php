<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Server.php';

class Sophpa_ServerTest extends PHPUnit_Framework_TestCase
{
	protected $mockResource;
	protected $mockResponse;

	protected function setUp()
	{
		$this->mockResource = $this->getMock('Sophpa_Resource', array(), array(), '', false);
		$this->mockResponse = $this->getMock('Sophpa_Response', array(), array(), '', false);
	}

	public function testGetsListOfDatabases()
	{
		$databases = array('testdatabase', 'somedatabase');
		$this->setUpResourceAndResponse('get', '_all_dbs', $databases);

		$server = new Sophpa_Server($this->mockResource);
		
		$this->assertEquals($databases, $server->listDatabases());
	}

	public function testCreatesDatabase()
	{
		$this->setUpResourceAndResponse('put', 'db_name');
		
		$server = new Sophpa_Server($this->mockResource);
		$db = $server->createDatabase('db_name');

		$this->assertTrue($db instanceof Sophpa_Database);
		$this->assertEquals('db_name', $db->getName());
	}

	/**
	 * @todo move to resource test
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

	public function testCreatesCorrectDatabaseInstance()
	{
		$server = new Sophpa_Server($this->mockResource);
		
		$db = $server->getDatabase('db_name');
		
		$this->assertTrue($db instanceof Sophpa_Database);
		$this->assertEquals('db_name', $db->getName());
	}

	public function testShouldDeleteDatabase()
	{
		$this->setUpResourceAndResponse('delete', 'db_name');

		$server = new Sophpa_Server($this->mockResource);
		
		$server->deleteDatabase('db_name');
	}

	public function testShouldGetVersion()
	{
		$response = json_decode('{"couchdb":"Welcome","version":"0.9.0a749067"}', true);
		$this->setUpResourceAndResponse('get', '/', $response);

		$server = new Sophpa_Server($this->mockResource);
		
		$this->assertEquals('0.9.0a749067', $server->getVersion());
	}

	public function testInitializesRestart()
	{
		$this->setUpResourceAndResponse('post', '_restart', array('ok' => true));

		$server = new Sophpa_Server($this->mockResource);

		$this->assertTrue($server->restart());
	}

	public function testAquiresCorrectNumberOfUuidsWhenEmptyCache()
	{
		$response = array('uuids' => array(
			'1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
		));

		$this->setUpResourceAndResponse('get', '_uuids', $response, array('count' => 12));

		$server = new Sophpa_Server($this->mockResource);
		$uuids = $server->getUuid(2);

		$this->assertEquals(array('1', '2'), $uuids);
	}

	public function testAquiresCorrectNumberOfCachedUuids()
	{
		$response = array('uuids' => array(
			'1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
		));

		$this->setUpResourceAndResponse('get', '_uuids', $response, array('count' => 12));

		$server = new Sophpa_Server($this->mockResource);
		$uuids = $server->getUuid(2);
		$uuids2 = $server->getUuid(10);

		$this->assertEquals(array('1', '2'), $uuids);
		$this->assertEquals(array('3', '4', '5', '6', '7', '8', '9', '10', '11', '12'), $uuids2);
	}

	public function testSetsUuidCacheOption()
	{
		$this->setUpResourceAndResponse('get', '_uuids', array('uuids' => array()), array('count' => 110));

		$server = new Sophpa_Server($this->mockResource, 100);
		$server->getUuid(10);
	}

	protected function setUpResourceAndResponse($resourceMethod, $requestPath, $response = null, $requestParams = null)
	{
		if($response) {
			$this->mockResponse->expects($this->once())
							   ->method('getContent')
							   ->will($this->returnValue($response));
		}

		if($requestParams) {
			$this->mockResource->expects($this->once())
							   ->method($resourceMethod)
							   ->with($this->equalTo($requestPath), $this->equalTo($requestParams))
							   ->will($this->returnValue($this->mockResponse));
		} else {
			$this->mockResource->expects($this->once())
							   ->method($resourceMethod)
							   ->with($this->equalTo($requestPath))
							   ->will($this->returnValue($this->mockResponse));
		}
	}
}
