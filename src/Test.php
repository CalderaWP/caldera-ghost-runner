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
class Test {


	const ACTION = 'runTest';


	/**
	 * @var Entity
	 */
	protected  $entity;

	public function __construct( Entity $entity  )
	{
		$this->entity = $entity;
	}

	/**
	 * Run test with a specific URL
	 *
	 * @param $siteUrl
	 * @param bool $immediate
	 *
	 * @return bool
	 */
	public function runOn( $siteUrl, $immediate = false ){

		$client = calderaGhostRunner()->getTestsClient();
		$result = $client->runTest(
			$this->ghostinspectorid,
			$this->getUrl(
				/**
				 * Filter URL for site to run test on
				 *
				 * @param string $siteUrl URL (home_url()) for site to run tests on.
				 * @param \calderawp\ghost\Entities\Test $entity Test
				 */
				apply_filters( 'calderaGhostRunner.testRunOn.url', $siteUrl, $this->entity )
			),
			$immediate
		);
		return $result;
	}

	public function __get( $name )
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
	 * Get link for running this test with
	 *
	 * @return string
	 */
	public function runLink()
	{
		return calderaGhostRunner()->adminUrl(
			array(
				'id' => $this->ghostinspectorid,
			),
			self::ACTION
		);
	}

	/**
	 * Get URL for test on current site.
	 *
	 * @param $siteUrl
	 *
	 * @return string
	 */
	public function getUrl( $siteUrl )
	{
		return trailingslashit( $siteUrl ) . $this->entity->pageSlug();
	}
}