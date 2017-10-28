<?php


namespace calderawp\ghost\Entities;
use calderawp\object\stdValidate;


/**
 * Class Test
 * @package calderawp\ghost\Entities
 */
class Test extends stdValidate {


	protected  $properties = [
		'config',
		'description',
		'gitissue',
		'ghostinspectorid',
		'release',
		'testsuite',
		'xtestreason',
		'helpscout',
	];

	protected $defaults = [
		'config'           => [ ],
		'description'      => 'No description',
		'gitissue'         => 0,
		'ghostinspectorid' => 0,
		'release'          => 0,
		'testsuite'        => 0,
		'xtestreason'      => '',
		'helpscout'        => 0,
	];


	public function pageSlug()
	{
		return sanitize_title_with_dashes( $this->name );
	}
}