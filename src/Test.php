<?php


namespace calderawp\ghost;

use \calderawp\ghost\Entities\Test as Entity;

/**
 * Class Test
 *
 {
ghostinspectorid: "gh1",
name: "n1",
branch: "b1",
release: "r1"
},
 *
 *
 * @package CalderaWP\GhostInspector
 */
class Test
{


	const ACTION = 'runTest';


	/**
	 * @var Entity
	 */
	protected $entity;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
	}


	public function __get($name)
	{
		return $this->entity->$name;
	}

	/**
	 * Get test name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get test ID
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->ghostinspectorid;
	}

	/**
	 * Get URL for test on current site.
	 *
	 * @param $siteUrl
	 *
	 * @return string
	 */
	public function getUrl($siteUrl)
	{
		return trailingslashit($siteUrl) . $this->entity->pageSlug();
	}
}
