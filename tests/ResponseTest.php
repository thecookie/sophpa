<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Response.php';

class Sophpa_ResponseTest extends PHPUnit_Framework_TestCase
{
	protected $headers;
	protected $content;

	protected function setUp()
	{
		$this->headers = array(
			'Date' => 'Thu, 17 Aug 2006 05:39:28 +0000GMT',
			'Content-Length' => '37',
			'Content-Type' => 'application/json',
			'Connection' => 'close'
		);

		$this->content = '{"_id":"discussion_tables","_rev":"D1C946B7","Subject":"I like Plankton","Author":"Rusty","PostedDate":"2006-08-15T17:30:12-04:00","Tags":["plankton", "baseball", "decisions"],"Body":"I decided today that I dont like baseball. I like plankton."}';

		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function testShouldCreateResponse()
	{
		try {
			$response = new Sophpa_Response(200, $this->headers, $this->content);
			
			$this->assertTrue($response instanceof Sophpa_Response);
		} catch(Exception $e) {
			$this->fail('Could not create an isntance of Sophpa_Response');
		}
	}

	public function testShouldCreateResponseFromRaw()
	{
		$rawResponse = file_get_contents('tests/_files/rawResponse.txt');
		try {
			$response = Sophpa_Response::createFromRaw($rawResponse);
			
			$this->assertTrue($response instanceof Sophpa_Response);			
		} catch(Exception $e) {
			$this->fail();
		}
	}

	public function testShouldReturnCorrectStatus()
	{
		try {
			$response = new Sophpa_Response(200, $this->headers, $this->content);
			
			$this->assertEquals($response->getStatus(), 200);
		} catch(Exception $e) {
			$this->fail('Could not create an isntance of Sophpa_Response');
		}
	}
	
	public function testStatusShouldBeOk()
	{
		try {
			$response = new Sophpa_Response(200, $this->headers, $this->content);
			
			$this->assertTrue($response->isOk());
		} catch(Exception $e) {
			$this->fail();
		}
	}

	public function testStatusShouldNotBeOk()
	{
		try {
			$response = new Sophpa_Response(404, $this->headers, $this->content);
			
			$this->assertFalse($response->isOk());
		} catch(Exception $e) {
			$this->fail();
		}
	}

	public function testShouldReturnCorrectHeaders()
	{
		try {
			$response = new Sophpa_Response(200, $this->headers, $this->content);
			
			$this->assertEquals($response->getHeaders(), $this->headers);
		} catch(Exception $e) {
			$this->fail();
		}
	}

	public function testShouldReturnCorrectHeader()
	{
		try {
			$response = new Sophpa_Response(200, $this->headers, $this->content);
			
			$this->assertEquals($response->getHeader('connection'), $this->headers['Connection']);
		} catch(Exception $e) {
			$this->fail();
		}
	}

	public function testShouldNotReturnHeader()
	{
		try {
			$response = new Sophpa_Response(200, $this->headers, $this->content);
			
			$this->assertNull($response->getHeader('Random-Header'));
		} catch(Exception $e) {
			$this->fail();
		}
	}

	public function testShouldReturnCorrectContent()
	{
		try {
			$response = new Sophpa_Response(200, $this->headers, $this->content);
			
			$this->assertEquals($response->getContent(), json_decode($this->content, true));
		} catch(Exception $e) {
			$this->fail();
		}
	}
}
