<?php
namespace calderawp\object;

/**
 * Trait arrayableTrait
 * @package calderawp\object
 */
trait arrayableTrait {

	/**
	 * Convert to array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$vars = get_object_vars(  $this );
		$array = [];
		foreach( $vars as $property => $value ){
			$array[ $property ] = $value;
		}

		return $array;
	}

}