<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/View/Permanent.php';
require_once 'Sophpa/View/Temporary.php';

class Sophpa_ViewTest extends PHPUnit_Framework_TestCase
{
	protected $stubRes;

	protected function setUp()
	{
		$http = $this->getMock('Sophpa_Http');

		$this->stubRes = $this->getMock(
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

	public function testCreatesPermanentView()
	{
		$view = new Sophpa_View_Permanent($this->stubRes);
		
		$this->assertTrue($view instanceof Sophpa_View_Permanent);
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
		$view = new Sophpa_View_Permanent($this->stubRes);
		
		$this->assertEquals($expectedOptions, $view->encodeOptions($options));
	}

	public function testQueriesPermanentViewWithoutOptions()
	{
		$this->stubRes->expects($this->once())->method('get');

		$view = new Sophpa_View_Permanent($this->stubRes);
		$view->query();
	}

	public function testQueriesPermanentViewWithOptions()
	{
		$options = array('descending' => true, 'limit' => 50);
		
		$this->stubRes->expects($this->once())
					  ->method('get')
					  ->with($this->anything(), $this->equalTo(array()), $this->equalTo($options));
		
		$view = new Sophpa_View_Permanent($this->stubRes);
		$view->query($options);
	}

	public function testQueriesPermanentViewWithKeysOption()
	{
		$options = array(
			'descending' => true,
			'limit' => 50,
			'keys' => array('somekey', 'someotherkey')
		);
		$this->stubRes->expects($this->once())
					  ->method('post')
					  ->with($this->anything(), $this->arrayHasKey('keys'), $this->anything(), $this->contains(50));

		$view = new Sophpa_View_Permanent($this->stubRes);
		$view->query($options);
	}

	public function testCreatesTemporaryView()
	{
		$view = new Sophpa_View_Temporary($this->stubRes, 'mapFunc');
		
		$this->assertTrue($view instanceof Sophpa_View_Temporary);
	}

	public function testQueriesTemporaryViewWithoutOptions()
	{
		$this->stubRes->expects($this->once())
					  ->method('post')
					  ->with(
							$this->anything(),
							$this->equalTo(array('map'=> 'mapFunc', 'reduce' => 'reduceFunc', 'language' => 'javascript')),
							$this->anything()
					  );
		
		$view = new Sophpa_View_Temporary($this->stubRes, 'mapFunc', 'reduceFunc');
		$view->query();
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
		$this->stubRes->expects($this->once())
					  ->method('post')
					  ->with(
							$this->anything(),
							$this->equalTo($expectedBody),
							$this->anything(),
							$this->equalTo(array('descending' => true, 'limit' => 50))
						);
		
		$view = new Sophpa_View_Temporary($this->stubRes, 'mapFunc', 'reduceFunc');
		$view->query($options);
	}
}

