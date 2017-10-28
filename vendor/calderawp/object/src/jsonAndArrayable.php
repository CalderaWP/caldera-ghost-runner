<?php


namespace calderawp\object;


/**
 * Class jsonAndArrayable
 * @package calderawp\object
 */
abstract class jsonAndArrayable implements \JsonSerializable {

	use arrayableTrait;
	use jsonableTrait;

}