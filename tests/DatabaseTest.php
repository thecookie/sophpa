<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Database.php';
require_once 'Sophpa/Server.php';
require_once 'Sophpa/Resource.php';
require_once 'Sophpa/Response.php';

class Sophpa_DatabaseTest extends PHPUnit_Framework_TestCase
{
	protected $responseHeader = array();

	protected $mockServer;

	protected $mockResource;

	/**
	 * @var Sophpa_Response
	 */
	protected $mockResponse;

	protected function setUp()
	{
		$this->mockResource = $this->getMock(
			'Sophpa_Resource',
			array('delete','get', 'head','post', 'put', '__toString'),
			array(),
			'',
			false
		);

		$this->mockServer = $this->getMock(
			'Sophpa_Server',
			array('getResource', 'getUuid'),
			array(),
			'',
			false
		);
		$this->mockServer->expects($this->once())
						 ->method('getResource')
						 ->will($this->returnValue($this->mockResource));
		
		$this->mockResponse = $this->getMock(
			'Sophpa_Response',
			array('getContent'),
			array(),
			'',
			false
		);
	}

	public function testShouldGetDbName()
	{
		$db = new Sophpa_Database($this->mockServer, 'test_database');
		
		$this->assertEquals('test_database', $db->getName());
	}

	public function testShouldGetInfo()
	{
		$content = '{"db_name":"test","doc_count":0,"doc_del_count":0,"update_seq":0,"purge_seq":0,"compact_running":false,"disk_size":4096,"instance_start_time":"1235932884009239"}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->mockResource->expects($this->once())->method('get')->will($this->returnValue($response));
			 
		$db = new Sophpa_Database($this->mockServer, 'test_database');
		
		$this->assertEquals(json_decode($content, true), $db->getInfo());
	}

	public function testShouldGetCorrectDocumentCount()
	{
		$content = '{"db_name":"test","doc_count":12345,"doc_del_count":0,"update_seq":0,"purge_seq":0,"compact_running":false,"disk_size":4096,"instance_start_time":"1235932884009239"}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->mockResource->expects($this->any())->method('get')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->mockServer, 'test_database');

		$this->assertEquals(12345, count($db));
	}
	
	public function testShouldGetTheCorrectDocument()
	{
		$content = '{"_id":"9f7b1e615381835eb9939c28c4ec44a9","_rev":"D1C946B7","Subject":"I like Plankton","Author":"Rusty","PostedDate":"2006-08-15T17:30:12-04:00","Tags":["plankton", "baseball", "decisions"],"Body":"I decided today that I dont like baseball. I like plankton."}';
		$response = new Sophpa_Response(200, $this->responseHeader, $content);
		$this->mockResource->expects($this->any())->method('get')->will($this->returnValue($response));

		$db = new Sophpa_Database($this->mockServer, 'test_database');
		$doc = $db->get('9f7b1e615381835eb9939c28c4ec44a9');

		$this->assertEquals('9f7b1e615381835eb9939c28c4ec44a9', $doc['_id']);
	}

	public function testSavesDocumentWithoutId()
	{
		$json = '{"ok":true, "id":"random_uuid", "rev":"946B7D1C"}';
		$response = new Sophpa_Response(201, $this->responseHeader, $json);
		$this->mockResource->expects($this->once())
							->method('put')
							->with(array('test_database', 'random_uuid'))
							->will($this->returnValue($response));
		$this->mockServer->expects($this->once())->method('getUuid')->will($this->returnValue(array('random_uuid')));

		$db = new Sophpa_Database($this->mockServer, 'test_database');
		$result = $db->save(array('test', 'test', 'test'));

		$this->assertEquals('random_uuid', $result['id']);
	}

	public function testSavesNewDocument()
	{
		$json = '{"ok":true, "id":"123BAC", "rev":"946B7D1C"}';
		$response = new Sophpa_Response(201, $this->responseHeader, $json);
		$this->mockResource->expects($this->once())
						   ->method('put')
						   ->with(array('test_database', 'id'))
						   ->will($this->returnValue($response));

		$db = new Sophpa_Database($this->mockServer, 'test_database');
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
		$this->mockResource->expects($this->once())
						   ->method('delete')
						   ->with(array('test_database', '123BAC'))
						   ->will($this->returnValue($this->mockResponse));

		$db = new Sophpa_Database($this->mockServer, 'test_database');
		$db->delete(array('_id' => '123BAC', '_rev' => '946B7D1C'));
	}

	public function testShouldGetDbNameFromToString()
	{
		$db = new Sophpa_Database($this->mockServer, 'random_name');

		$this->assertEquals('random_name', (string)$db);
	}

	public function testInitializesCompaction()
	{
		$response = new Sophpa_Response(202, $this->responseHeader, '{"ok":true}');
		$this->mockResource->expects($this->once())
						   ->method('post')
						   ->with(array('random_name', '_compact'))
						   ->will($this->returnValue($response));
		$db = new Sophpa_Database($this->mockServer, 'random_name');
		
		$this->assertTrue($db->compact());
	}

	public function testBulkSavesNewDocumnets()
	{
		$docs = array(
			array('banana'),
			array('apple'),
			array('pear')
		);
		
		$expectedBody = array(
			'docs' => array(
				array(
					'banana',
					'_id' => 'uuid3'
				),
				array(
					'apple',
					'_id' => 'uuid2'
				),
				array(
					'pear',
					'_id' => 'uuid1'
				)
			)
		);

		$this->mockResource->expects($this->once())
						   ->method('post')
						   ->with(array('db_name', '_bulk_docs'), $expectedBody)
						   ->will($this->returnValue($this->mockResponse));

		$this->mockServer->expects($this->once())
						 ->method('getUuid')
						 ->with(3)
						 ->will($this->returnValue(array('uuid1', 'uuid2', 'uuid3')));

		$db = new Sophpa_Database($this->mockServer, 'db_name');
		$db->bulkSave($docs);
	}

	public function testBulkSavesDocuments()
	{
		$docs = array(
			array(
				'banana',
				'_id' => 'uuid3'
			),
			array(
				'apple',
				'_id' => 'uuid2'
			),
			array(
				'pear',
				'_id' => 'uuid1'
			)
		);

		$this->mockResource->expects($this->once())
						   ->method('post')
						   ->with(array('db_name', '_bulk_docs'), array('docs' => $docs))
						   ->will($this->returnValue($this->mockResponse));

		$this->mockServer->expects($this->once())
						 ->method('getUuid')
						 ->with(0)
						 ->will($this->returnValue(array()));

		$db = new Sophpa_Database($this->mockServer, 'db_name');
		$db->bulkSave($docs);
	}

	public function testBulkSavesDocumentsWithOption()
	{
		$docs = array(array('_id' => 'id', 'document!'));
		
		$expectedBody = array(
			'docs' => $docs,
			Sophpa_Database::BULK_OPTION_ALL_OR_NOTHING => true
		);

		$this->mockResource->expects($this->once())
						   ->method('post')
						   ->with(array('db_name', '_bulk_docs'), $expectedBody)
						   ->will($this->returnValue($this->mockResponse));

		$this->mockServer->expects($this->once())
						 ->method('getUuid')
						 ->with(0)
						 ->will($this->returnValue(array()));

		$db = new Sophpa_Database($this->mockServer, 'db_name');
		$db->bulkSave($docs, array(Sophpa_Database::BULK_OPTION_ALL_OR_NOTHING => true));
	}

	public function testBulkDeleteDocuments()
	{
		$docs = array(
			array('_id' => 'id1', '_rev' => 'rev1'),
			array('_id' => 'id2', '_rev' => 'rev2')
		);
		
		$expectedBody = array(
			'docs' => array(
				array('_id' => 'id1', '_rev' => 'rev1', '_deleted' => true),
				array('_id' => 'id2', '_rev' => 'rev2', '_deleted' => true)
			)
		);

		$this->mockResource->expects($this->once())
						   ->method('post')
						   ->with(array('db_name', '_bulk_docs'), $expectedBody)
						   ->will($this->returnValue($this->mockResponse));

		$this->mockServer->expects($this->once())
						 ->method('getUuid')
						 ->with(0)
						 ->will($this->returnValue(array()));

		$db = new Sophpa_Database($this->mockServer, 'db_name');
		$db->bulkDelete($docs);
	}
}

