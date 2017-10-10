<?php


namespace calderawp\ghost;


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

	/** @var string  */
	protected $pageSlug;
	/** @var string  */
	protected $branch;
	/** @var string  */
	protected $name;
	/** @var string  */
	protected $release;
	/** @var string  */
	protected $baseline;
	/** @var string  */
	protected $ghostinspectorid;

	protected $importData;

	public function __construct( $ghostinspectorid, $name, $release, $baseline, $branch = '' )
	{
		$this->ghostinspectorid = $ghostinspectorid;
		$this->name = $name;
		$this->pageSlug = sanitize_title_with_dashes( $name );
		$this->release = $release;
		$this->baseline = $baseline;
		$this->branch = $branch;
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
			$this->getUrl( $siteUrl ),
			$immediate
		);
		return $result;
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
		return trailingslashit( $siteUrl ) . $this->pageSlug;
	}
}