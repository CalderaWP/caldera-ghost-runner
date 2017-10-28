<?php


namespace calderawp\object;

/**
 * Class almostStdImmutable
 *
 * Variation on almostSTD, but properites can only be set at instantiation, via constructor
 *
 * @package calderawp\object
 */
abstract class almostStdImmutable extends almostStd{


	/**
	 * Block setting properties
	 *
	 * @param string $prop NOT USED
	 * @param mixed $value NOT USED
	 *
	 * @return bool
	 */
	public function __set( $prop, $value )
	{
		return false;
	}

	/**
	 * Block setting by stdClass
	 *
	 * @param \stdClass $obj NOT USED
	 */
	public function setFromObject( \stdClass $obj ){}

}