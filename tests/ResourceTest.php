<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Resource.php';
require_once 'Sophpa/Http.php';

class Sophpa_ResourceTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Sophpa_Http
	 */
	protected $mockHttp;

	/**
	 * @var Sophpa_Resource
	 */
	protected $resource;

	/**
	 * @var Sophpa_Response
	 */
	protected $mockResponse;

	/**
	 * @var array
	 */
	protected $params;

	protected function setUp()
	{
		$this->mockHttp = $this->getMock('Sophpa_Http', array('request'));
		$this->mockResponse = $this->getMock(
			'Sophpa_Response',
			array('isOk', 'getStatus', 'getContent'),
			array(), '', false
		);
		$this->resource = new Sophpa_Resource($this->mockHttp, 'http://localhost:5948');
		$this->params = array('param1' => 'value1', 'param2' => 'value2');
		
	}

	public function testRequestsWithCorrectBaseUrl()
	{
		$this->mockResponse->expects($this->once())->method('isOk')->will($this->returnValue(true));

		$this->mockHttp->expects($this->once())
					   ->method('request')
					   ->with($this->equalTo('GET'), $this->equalTo('http://localhost:5948/'))
					   ->will($this->returnValue($this->mockResponse));

		$this->resource->get('/');
	}

	public function testRequestsWithCorrectBaseUrlAndParams()
	{
		$this->mockResponse->expects($this->once())->method('isOk')->will($this->returnValue(true));

		$this->mockHttp->expects($this->once())
					   ->method('request')
					   ->with($this->equalTo('GET'), $this->equalTo('http://localhost:5948/?param1=value1&param2=value2'))
					   ->will($this->returnValue($this->mockResponse));

		$this->resource->get('/', $this->params);
	}

	public function testRequestsWithCorrectBaseUrlAndPath()
	{
		$this->mockResponse->expects($this->once())->method('isOk')->will($this->returnValue(true));

		$this->mockHttp->expects($this->once())
					   ->method('request')
					   ->with($this->equalTo('GET'), $this->equalTo('http://localhost:5948/_all_docs'))
					   ->will($this->returnValue($this->mockResponse));

		$this->resource->get('_all_docs');
	}

	public function testRequestsWithCorrectBaseUrlAndMultiplePaths()
	{
		$this->mockResponse->expects($this->once())->method('isOk')->will($this->returnValue(true));

		$this->mockHttp->expects($this->once())
					   ->method('request')
					   ->with($this->equalTo('GET'), $this->equalTo('http://localhost:5948/database/document'))
					   ->will($this->returnValue($this->mockResponse));

		$this->resource->get('/database/document/');
	}

	public function testRequestsWithCorrectBaseUrlMultiplePathsAndParams()
	{
		$this->mockResponse->expects($this->once())->method('isOk')->will($this->returnValue(true));

		$this->mockHttp->expects($this->once())
					   ->method('request')
					   ->with($this->equalTo('GET'), $this->equalTo('http://localhost:5948/database/document?param1=value1&param2=value2'))
					   ->will($this->returnValue($this->mockResponse));

		$this->resource->get('/database/document/', $this->params);
	}

	public function testRequestsWithCorrectBaseUrlAndMultiplePathsUsingArray()
	{
		$this->mockResponse->expects($this->once())->method('isOk')->will($this->returnValue(true));

		$this->mockHttp->expects($this->once())
					   ->method('request')
					   ->with($this->equalTo('GET'), $this->equalTo('http://localhost:5948/test/test2/test3'))
					   ->will($this->returnValue($this->mockResponse));

		$this->resource->get(array('test', 'test2', 'test3'));
	}
}
