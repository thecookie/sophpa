<?php

require_once 'PHPUnit/Framework.php';
require_once 'Sophpa/Document.php';

class Sophpa_DocumentTest extends PHPUnit_Framework_TestCase
{
	protected $json;
	protected $documentArray;

	protected function setUp()
	{
		$this->json = '{"_id": "9f7b1e615381835eb9939c28c4ec44a9","_rev": "2769738038","username":"I\'m a username!","details":{"type":"special"}}';
		
		$this->documentArray = array(
			'_id' => '9f7b1e615381835eb9939c28c4ec44a9',
			'_rev' => '2769738038',
			'username' => 'I\'m a username!',
			'details' => array('type' => 'special')
		);

		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function testCreatesDocumentFromJson()
	{
		$document = new Sophpa_Document($this->json);
		
		$this->assertTrue($document instanceof Sophpa_Document);
	}

	public function testCreatesDocumentFromArray()
	{
		$document = new Sophpa_Document($this->documentArray);

		$this->assertTrue($document instanceof Sophpa_Document);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testShouldThrowExceptionWhenInvalidJson()
	{
		$document = new Sophpa_Document('invalid json!');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testShouldThrowException()
	{
		$document = new Sophpa_Document(new stdClass());
	}

	public function testReadsDocumentAsArray()
	{
		$document = new Sophpa_Document($this->json);
		
		$this->assertEquals('2769738038', $document['_rev']);
		$this->assertEquals('special', $document['details']['type']);
	}

	public function testWriteToDocumentAsArray()
	{
		$document = new Sophpa_Document($this->json);
		
		$document['username'] = 'New username!';
		$document['new_post'] = 'new!';
		$document['new'][0]['new'] = 'New here too';

		$this->assertEquals('New username!', $document['username']);
		$this->assertEquals('new!', $document['new_post']);
		$this->assertEquals('New here too', $document['new'][0]['new']);
	}

	public function testMergeDocumentWithArray()
	{
		$array = array(
			'newindex1' => 'value',
			'username' => 'value2',
			'newindex3' => array('value3', 'value4'));

		$document = new Sophpa_Document($this->json);
		
		$document->merge($array);
		
		$this->assertEquals('value', $document['newindex1']);
		$this->assertEquals('value2', $document['username']);
		$this->assertEquals(array('value3', 'value4'), $document['newindex3']);
	}
}

