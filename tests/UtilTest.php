<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Util.php';

class Sophpa_UtilTest extends PHPUnit_Framework_TestCase
{
	protected $params;

	protected function setUp()
	{
		$this->params = array('param1' => 'value1', 'param2' => 'value2');
		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function testShouldCreateBase()
	{
		$url = Sophpa_Util::uri('http://localhost:5948');
		
		$this->assertEquals('http://localhost:5948/', $url);
	}

	public function testShouldCreateBaseWithParams()
	{
		$url = Sophpa_Util::uri('http://localhost:5948', null, $this->params);
		
		$this->assertEquals('http://localhost:5948/?param1=value1&param2=value2', $url);
	}
	
	public function testShouldCreateBaseAndPath()
	{
		$url = Sophpa_Util::uri('http://localhost:5948/', '/_all_docs');
		
		$this->assertEquals('http://localhost:5948/_all_docs', $url);
	}
	
	public function testShouldCreateBaseWithMultiplePaths()
	{
		$url = Sophpa_Util::uri('http://localhost:5948/', '/database/document/');
		
		$this->assertEquals('http://localhost:5948/database/document', $url);
	}

	public function testShouldCreateBaseWithMultiplePathsAndParams()
	{
		$url = Sophpa_Util::uri('http://localhost:5948/', '/database/document/', $this->params);
		
		$this->assertEquals(
			'http://localhost:5948/database/document?param1=value1&param2=value2',
			$url
		);
	}
}
