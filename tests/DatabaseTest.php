<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Database.php';
require_once 'Sophpa/Response.php';

class Sophpa_DatabaseTest extends PHPUnit_Framework_TestCase
{
	protected $responseHeader = array();

	protected $mockResource;

	protected function setUp()
	{
		$this->mockResource = $this->getMock(
			'Sophpa_Resource',
			array('delete','get', 'head','post', 'put', '__toString'),
			array(),
			'',
			false
		);
	}

	public function testShouldGetDbNameSetInConstructor()
	{
		$db = new Sophpa_Database($this->mockResource, 'test_database');
		
		$this->assertEquals('test_database', $db->getName());
	}

	public function testShouldGetDbNameNotSetInConstructor()
	{
		$content = '{"db_name":"test","doc_count":0,"doc_del_count":0,"update_seq":0,"purge_seq":0,"compact_running":false,"disk_size":4096,"instance_start_time":"1235932884009239"}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->mockResource->expects($this->once())->method('get')->will($this->returnValue($response));
			 
		$db = new Sophpa_Database($this->mockResource);

		$this->assertEquals('test', $db->getName());
	}

	public function testShouldGetInfo()
	{
		$content = '{"db_name":"test","doc_count":0,"doc_del_count":0,"update_seq":0,"purge_seq":0,"compact_running":false,"disk_size":4096,"instance_start_time":"1235932884009239"}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->mockResource->expects($this->any())->method('get')->will($this->returnValue($response));
			 
		$db = new Sophpa_Database($this->mockResource, 'test_database');
		
		$this->assertEquals(json_decode($content, true), $db->getInfo());
	}

	public function testShouldGetCorrectDocumentCount()
	{
		$content = '{"db_name":"test","doc_count":12345,"doc_del_count":0,"update_seq":0,"purge_seq":0,"compact_running":false,"disk_size":4096,"instance_start_time":"1235932884009239"}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->mockResource->expects($this->any())->method('get')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->mockResource, 'test_database');

		$this->assertEquals(12345, count($db));
	}
	
	public function testShouldGetTheCorrectDocument()
	{
		$content = '{"_id":"9f7b1e615381835eb9939c28c4ec44a9","_rev":"D1C946B7","Subject":"I like Plankton","Author":"Rusty","PostedDate":"2006-08-15T17:30:12-04:00","Tags":["plankton", "baseball", "decisions"],"Body":"I decided today that I dont like baseball. I like plankton."}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->mockResource->expects($this->any())->method('get')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->mockResource, 'test_database');
		$doc = $db->get('9f7b1e615381835eb9939c28c4ec44a9');

		$this->assertEquals('9f7b1e615381835eb9939c28c4ec44a9', $doc['_id']);
	}

	public function testCreatesNewDocumentFromArray()
	{
		$json = '{"ok":true, "id":"123BAC", "rev":"946B7D1C"}';
		$response = new Sophpa_Response(201, $this->responseHeader, $json);
		$this->mockResource->expects($this->once())->method('post')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->mockResource, 'test_database');
		$result = $db->create(array('test', 'test', 'test'));

		$this->assertEquals('123BAC', $result['id']);
	}

	public function testSavesNewDocumentFromObject()
	{
		$json = '{"ok":true, "id":"123BAC", "rev":"946B7D1C"}';
		$response = new Sophpa_Response(201, $this->responseHeader, $json);
		$this->mockResource->expects($this->once())->method('put')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->mockResource, 'test_database');
		$doc = array(
			'_id' => 'id',
			'_rev' => 'rev',
			'key' => 'value'
		);
		$result = $db->save($doc);

		$this->assertEquals('123BAC', $result['id']);
		$this->assertEquals('946B7D1C', $result['rev']);
	}

	public function testShouldDeleteDocumentById()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '{"ok":true,"rev":"2839830636"}');
		$this->mockResource->expects($this->once())->method('delete')->will($this->returnValue($response));
 
		$db = new Sophpa_Database($this->mockResource, 'test_database');
		$result = $db->delete(array('_id' => '123BAC', '_rev' => '946B7D1C'));
	}
}

