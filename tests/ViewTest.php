<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/View/Permanent.php';

class Sophpa_ViewTest extends PHPUnit_Framework_TestCase
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

	public function testCreatesPermanentView()
	{
		$view = new Sophpa_View_Permanent($this->stubResource);
		
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
		$view = new Sophpa_View_Permanent($this->stubResource);

		$options = $view->encodeOptions($options);
		
		$this->assertEquals(7, $options['limit']);
		$this->assertEquals(12345, $options['endkey']);
		$this->assertEquals('"someusername"', $options['startkey']);
		$this->assertEquals(true, $options['descending']);
		$this->assertEquals('"d7b29022653ffe9da88cb6b969be1784"', $options['key']);
	}

	public function testQueriesPermanentViewWithoutOptions()
	{
		$this->stubResource->expects($this->once())->method('get');

		$view = new Sophpa_View_Permanent($this->stubResource);

		$view->query();
	}

	public function testQueriesPermanentViewWithOptions()
	{
		$options = array('descending' => true, 'limit' => 50);
		
		$this->stubResource	->expects($this->once())
							->method('get')
							->with($this->anything(), $this->anything(), $this->contains(50));
		
		$view = new Sophpa_View_Permanent($this->stubResource);

		$view->query($options);
	}

	public function testQueriesPermanentViewWithKeysOption()
	{
		$options = array(
			'descending' => true,
			'limit' => 50,
			'keys' => array('somekey', 'someotherkey')
		);

		$this->stubResource	->expects($this->once())
							->method('post')
							->with($this->anything(), $this->arrayHasKey('keys'), $this->anything(), $this->contains(50));

		$view = new Sophpa_View_Permanent($this->stubResource);

		$view->query($options);
	}
}

