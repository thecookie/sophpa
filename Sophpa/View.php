<?php

abstract class Sophpa_View
{
	const OPTION_DESCENDING = 'descending';
	const OPTION_ENDKEY = 'endkey';
	const OPTION_ENDKEY_DOCID = 'endkey_docid';
	const OPTION_GROUP = 'group';
	const OPTION_GROUP_LEVEL = 'group_level';
	const OPTION_INCLUDE_DOCS = 'include_docs';
	const OPTION_KEY = 'key';
	const OPTION_KEYS = 'keys';
	const OPTION_LIMIT = 'limit';
	const OPTION_REDUCE = 'reduce';
	const OPTION_SKIP = 'skip';
	const OPTION_STALE = 'stale';
	const OPTION_STARTKEY = 'startkey';
	const OPTION_STARTKEY_DOCID = 'startkey_docid';

	/**
	 * Contains options to be JSON encoded, used in encodeOptions()
	 *
	 * @var array
	 */
	protected static $encode = array(
		self::OPTION_KEY,
		self::OPTION_STARTKEY,
		self::OPTION_ENDKEY
	);

	/**
	 * Holds the injected resource object
	 *
	 * @var Sophpa_Resource
	 */
	protected $resource;

	public function __construct(Sophpa_Resource $resource)
	{
		$this->resource = $resource;
	}

	/**
	 * CouchDB wants some query string options json encoded
	 *
	 * @param array $options
	 * @return array
	 */
	public function encodeOptions(array $options)
	{
		foreach($options as $key => &$value) {
			if(in_array($key, self::$encode)) {
				$value = json_encode($value);
			}
		}

		return $options;
	}

	/**
	 * Query a view
	 *
	 * @param array $options
	 * @return Sophpa_Response
	 */
	public abstract function query(array $options = array());
}

