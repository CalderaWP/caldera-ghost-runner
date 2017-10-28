<?php


namespace calderawp\object;


/**
 * Trait jsonableTrait
 * @package calderawp\object
 */
trait jsonableTrait {


	/**
	 * @inheritdoc
	 */
	public function jsonSerialize()
	{
		if (  method_exists( $this, 'toArray' ) ) {
			return $this->toArray();
		}

		return [];
	}


}