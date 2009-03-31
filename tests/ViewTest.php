<?php

require_once 'PHPUnit/Framework.php';

class Sophpa_ViewTest extends PHPUnit_Framework_TestCase
{
	protected $mockResource;
	protected $mockResponse;
	protected $mockServer;

	protected function setUp()
	{
		$this->mockResource = $this->getMock(
			'Sophpa_Resource',
			array('delete','get', 'head','post', 'put', '__toString'),
			array(),
			'',
			false
		);

		$this->mockResponse = $this->getMock(
			'Sophpa_Response',
			array('getContent'),
			array(),
			'',
			false
		);
		$this->mockResponse->expects($this->once())
						   ->method('getContent')
							->will($this->returnValue(array('data')));

		$this->mockServer = $this->getMock(
			'Sophpa_Server',
			array('getResource'),
			array(),
			'',
			false
		);
		$this->mockServer->expects($this->once())
						 ->method('getResource')
						 ->will($this->returnValue($this->mockResource));
	}

	public function testEncodesCorrectOptions()
	{
		$options = array(
			'limit' => 7,
			'endkey' => 12345,
			'startkey' => 'someusername',
			'descending' => true,
			'key' => 'd7b29022653ffe9da88cb6b969be1784'
		);
		$expectedOptions = array(
			'limit' => 7,
			'endkey' => 12345,
			'startkey' => '"someusername"',
			'descending' => true,
			'key' => '"d7b29022653ffe9da88cb6b969be1784"'
		);
		$this->mockResource->expects($this->once())
					  ->method('get')
					  ->with($this->anything(), $this->equalTo($expectedOptions))
					  ->will($this->returnValue($this->mockResponse));
		$db = new Sophpa_Database($this->mockServer, 'name');

		$db->view('design/name', $options);
	}

	public function testQueriesPermanentViewWithoutOptions()
	{
		$this->mockResource->expects($this->once())->method('get')->will($this->returnValue($this->mockResponse));

		$db = new Sophpa_Database($this->mockServer, 'name');
		$db->view('design/view');
	}

	public function testQueriesPermanentViewWithOptions()
	{
		$options = array('descending' => true, 'limit' => 50);

		$this->mockResource->expects($this->once())
					  ->method('get')
					  ->with($this->anything(), $this->equalTo($options))
					  ->will($this->returnValue($this->mockResponse));
		
		$db = new Sophpa_Database($this->mockServer, 'name');
		$db->view('design/view', $options);
	}

	public function testQueriesPermanentViewWithKeysOption()
	{
		$options = array(
			'descending' => true,
			'limit' => 50,
			'keys' => array('somekey', 'someotherkey')
		);
		$this->mockResource->expects($this->once())
					  ->method('post')
					  ->with($this->anything(), $this->arrayHasKey('keys'), $this->contains(50))
					  ->will($this->returnValue($this->mockResponse));

		$db = new Sophpa_Database($this->mockServer, 'name');
		$db->view('design/view', $options);
	}

	public function testQueriesTemporaryViewWithoutOptions()
	{
		$this->mockResource->expects($this->once())
					  ->method('post')
					  ->with(
							$this->equalTo(array('name', '_temp_view')),
							$this->equalTo(array('map'=> 'mapFunc', 'reduce' => 'reduceFunc', 'language' => 'javascript')),
							$this->anything()
					  )->will($this->returnValue($this->mockResponse));
		
		$db = new Sophpa_Database($this->mockServer, 'name');
		$db->query('mapFunc', 'reduceFunc');
	}

	public function testQueriesTemporaryViewWithKeysOption()
	{
		$options = array(
			'descending' => true,
			'limit' => 50,
			'keys' => array('somekey', 'someotherkey')
		);
		$expectedBody = array(
			'map'=> 'mapFunc',
			'reduce' => 'reduceFunc',
			'language' => 'javascript', 
			'keys' => array('somekey', 'someotherkey')
		);
		$this->mockResource->expects($this->once())
					  ->method('post')
					  ->with(
							$this->equalTo(array('name', '_temp_view')),
							$this->equalTo($expectedBody),
							$this->equalTo(array('descending' => true, 'limit' => 50))
						)->will($this->returnValue($this->mockResponse));
		
		$db = new Sophpa_Database($this->mockServer, 'name');
		$db->query('mapFunc', 'reduceFunc', $options);
	}
}

