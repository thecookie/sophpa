<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Database.php';
require_once 'Sophpa/Response.php';

class Sophpa_DatabaseTest extends PHPUnit_Framework_TestCase
{
	protected $stubResource;

	protected function setUp()
	{
		$http = $this->getMock('Sophpa_Http');

		$this->stubResource = $this->getMock(
			'Sophpa_Resource',
			array('delete','get', 'head','post', 'put', '__toString'),
			array($http, 'http://localhost:5984/')
		);

		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function testShouldGetNameWhenSet()
	{
		$db = new Sophpa_Database($this->stubResource, 'test_database');
		
		$this->assertEquals('test_database', $db->getName());
	}

	public function testShouldGetNameWhenNotSet()
	{
		$content = '{"db_name":"test","doc_count":0,"doc_del_count":0,"update_seq":0,"purge_seq":0,"compact_running":false,"disk_size":4096,"instance_start_time":"1235932884009239"}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->stubResource->expects($this->once())->method('get')->will($this->returnValue($response));
			 
		$db = new Sophpa_Database($this->stubResource);

		$this->assertEquals('test', $db->getName());
	}

	public function testShouldGetInfo()
	{
		$content = '{"db_name":"test","doc_count":0,"doc_del_count":0,"update_seq":0,"purge_seq":0,"compact_running":false,"disk_size":4096,"instance_start_time":"1235932884009239"}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->stubResource->expects($this->any())->method('get')->will($this->returnValue($response));
			 
		$db = new Sophpa_Database($this->stubResource, 'test_database');
		
		$this->assertEquals(json_decode($content, true), $db->getInfo());
	}

	public function testShouldGetCorrectDocumentCount()
	{
		$content = '{"db_name":"test","doc_count":12345,"doc_del_count":0,"update_seq":0,"purge_seq":0,"compact_running":false,"disk_size":4096,"instance_start_time":"1235932884009239"}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->stubResource->expects($this->any())->method('get')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->stubResource, 'test_database');

		$this->assertEquals(12345, count($db));
	}
	
	public function testShouldGetTheCorrectDocument()
	{
		$content = '{"_id":"9f7b1e615381835eb9939c28c4ec44a9","_rev":"D1C946B7","Subject":"I like Plankton","Author":"Rusty","PostedDate":"2006-08-15T17:30:12-04:00","Tags":["plankton", "baseball", "decisions"],"Body":"I decided today that I dont like baseball. I like plankton."}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->stubResource->expects($this->any())->method('get')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->stubResource, 'test_database');
		$doc = $db->get('9f7b1e615381835eb9939c28c4ec44a9');

		$this->assertTrue($doc instanceof Sophpa_Document);
		$this->assertEquals('9f7b1e615381835eb9939c28c4ec44a9', $doc['_id']);
	}

	public function testCreatesNewDocumentFromArray()
	{
		$json = '{"ok":true, "id":"123BAC", "rev":"946B7D1C"}';
		$response = new Sophpa_Response(201, $this->responseHeader, $json);
		$this->stubResource->expects($this->any())->method('post')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->stubResource, 'test_database');
		$id = $db->create(array('test', 'test', 'test'));

		$this->assertEquals('123BAC', $id);
	}

	public function testSavesNewDocumentFromObject()
	{
		$json = '{"ok":true, "id":"123BAC", "rev":"946B7D1C"}';
		$response = new Sophpa_Response(201, $this->responseHeader, $json);
		$this->stubResource->expects($this->once())->method('put')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->stubResource, 'test_database');
		$doc = new Sophpa_Document(array(
			'_id' => 'id',
			'_rev' => 'rev',
			'data' => 'content'
		));
		$db->save($doc);

		$this->assertEquals('123BAC', $doc['_id']);
		$this->assertEquals('946B7D1C', $doc['_rev']);
	}

	public function testShouldDeleteDocumentById()
	{
		$response = new Sophpa_Response(200, $this->responseHeader, '{"ok":true,"rev":"2839830636"}');
		$this->stubResource->expects($this->any())->method('delete')->will($this->returnValue($response));
 
		$db = new Sophpa_Database($this->stubResource, 'test_database');
		$doc = new Sophpa_Document(array('_id' => '123BAC', '_rev' => '946B7D1C'));
		$result = $db->delete($doc);
	}

	public function testShouldReturnPermanentView()
	{
		
	}
}

