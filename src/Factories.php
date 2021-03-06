<?php


namespace calderawp\ghost;

use calderawp\ghost\Entities\Test;

/**
 * Class Factories
 * @package calderawp\ghost
 */
class Factories
{

	/**
	 * Gets tests, optionally setting in container, from a google sheer in the right forms
	 *
	 * @param string $docId ID of Google sheet -- must be "Published To The Web" and in the right format, results will be minimally validated, follow the fucking schema.
	 * @param Container|null $container Optional. Container instance to add tests to.
	 *
	 * @return array|mixed|null|object Raw results from the sheet
	 */
	public static function testsFromGoogleSheet($docId, Container $container = null)
	{

		$data = self::getDataFromGoogle($docId, 1, md5(__CLASS__ . __METHOD__ . $docId));

		if (is_null($data) || is_null($container)) {
			return $data;
		}


		if (is_object($data) && isset($data->rows) && ! empty($data->rows)) {
			/** @var \stdClass $test */
			foreach ($data->rows as $test) {
				$container->addTest(
					new \calderawp\ghost\Test(
						self::testEntity($test)
					)
				);
			}
		}

		return $data;
	}

	/**
	 * Get all test forms from the Google Sheet
	 *
	 * @param string $docId ID of Google sheet
	 *
	 *
	 * @return \stdClass|null
	 */
	public static function testData($docId)
	{
		$data = self::getDataFromGoogle($docId, 4, md5(__CLASS__ . __METHOD__ . $docId));
		if (! empty($data)) {
			return $data;
		}

		return null;
	}

	/**
	 * Create a new Test entity from stdClass object returned form API
	 *
	 * @param \stdClass $object
	 *
	 * @return Test
	 */
	public static function testEntity(\stdClass $object)
	{
		return new Test($object);
	}

	/**
	 * Run the importer
	 *
	 * @param string $docId Optional. ID of google doc to copy tests forms
	 */
	public static function import($docId = null)
	{
		$tests = static::testData(! is_null($docId) ? $docId : calderaGhostRunnerEnv('CGRGDID'));
		if (is_array($tests)) {
			$importer = new Import($tests);
			$importer->run();
		}
	}


	/**
	 * Get the WP_Post object for page with a test, by Ghost inspector ID
	 *
	 * @param int $id Ghost inspector ID for test
	 * @param Container|null $container
	 *
	 * @return \WP_Post|null
	 */
	public static function pageByGhostId($id, Container $container = null)
	{
		if (! $container) {
			$container = calderaGhostRunner();
		}
		$key = 'page' . CGR_VER;
		if (! $container->offsetExists($key)) {
			$query = new \WP_Query(array(
				'post_type' => 'page',
				'meta_field' => 'CGR_ghostInspectorID',
				'meta_value' => $id
			));
			if (0 < $query->found_posts) {
				$container->offsetSet($key, $query->posts[0]);
			} else {
				$container->offsetSet($key, null);
			}
		}

		return $container->offsetGet($key);
	}

	/**
	 * Get ghost inspector page for test
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public static function testUrl($id)
	{
		return 'https://app.ghostinspector.com/tests/' . $id;
	}

	/**
	 * @param $docId
	 * @param $sheet
	 * @param string $key Cache key Version is prepended and then its md5 hashed
	 *
	 * @return array|mixed|null|object
	 */
	protected static function getDataFromGoogle($docId, $sheet, $key)
	{
		$key = CGR_VER .'2'. $key;
		$cached = get_transient($key);
		if (! empty($cached) && is_array($cached)) {
			return $cached;
		} else {
			$r = \Requests::get('https://yzoy1wu6tg.execute-api.us-east-1.amazonaws.com/dev/list', array(), array( 'verify' => false ));
			if (200 == $r->status_code) {
				$data = json_decode($r->body);
				if (! isset($data[0])) {
					return null;
				}
				set_transient($key, $data[0], HOUR_IN_SECONDS);

				return $data[0];
			} else {
				return null;
			}
		}
	}
}
