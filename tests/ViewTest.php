<?php

require_once 'PHPUnit/Framework.php';

class Sophpa_ViewTest extends PHPUnit_Framework_TestCase
{
	protected $mockRes;
	protected $mockResponse;

	protected function setUp()
	{
		$this->mockRes = $this->getMock(
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
		$this->mockResponse->expects($this->once())->method('getContent')->will($this->returnValue(array('data')));
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
		$this->mockRes->expects($this->once())
					  ->method('get')
					  ->with($this->anything(), $this->equalTo($expectedOptions))
					  ->will($this->returnValue($this->mockResponse));
		$db = new Sophpa_Database($this->mockRes);

		$db->view('design/name', $options);
	}

	public function testQueriesPermanentViewWithoutOptions()
	{
		
		$this->mockRes->expects($this->once())->method('get')->will($this->returnValue($this->mockResponse));

		$db = new Sophpa_Database($this->mockRes);
		$db->view('design/view');
	}

	public function testQueriesPermanentViewWithOptions()
	{
		$options = array('descending' => true, 'limit' => 50);

		$this->mockRes->expects($this->once())
					  ->method('get')
					  ->with($this->anything(), $this->equalTo($options))
					  ->will($this->returnValue($this->mockResponse));
		
		$db = new Sophpa_Database($this->mockRes);
		$db->view('design/view', $options);
	}

	public function testQueriesPermanentViewWithKeysOption()
	{
		$options = array(
			'descending' => true,
			'limit' => 50,
			'keys' => array('somekey', 'someotherkey')
		);
		$this->mockRes->expects($this->once())
					  ->method('post')
					  ->with($this->anything(), $this->arrayHasKey('keys'), $this->contains(50))
					  ->will($this->returnValue($this->mockResponse));

		$db = new Sophpa_Database($this->mockRes);
		$db->view('design/view', $options);
	}

	public function testQueriesTemporaryViewWithoutOptions()
	{
		$this->mockRes->expects($this->once())
					  ->method('post')
					  ->with(
							$this->equalTo('_temp_view'),
							$this->equalTo(array('map'=> 'mapFunc', 'reduce' => 'reduceFunc', 'language' => 'javascript')),
							$this->anything()
					  )->will($this->returnValue($this->mockResponse));
		
		$db = new Sophpa_Database($this->mockRes);
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
		$this->mockRes->expects($this->once())
					  ->method('post')
					  ->with(
							$this->anything(),
							$this->equalTo($expectedBody),
							$this->equalTo(array('descending' => true, 'limit' => 50))
						)->will($this->returnValue($this->mockResponse));
		
		$db = new Sophpa_Database($this->mockRes);
		$db->query('mapFunc', 'reduceFunc', $options);
	}
}

