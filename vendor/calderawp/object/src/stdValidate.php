<?php


namespace calderawp\object;


/**
 * Class stdValidate
 *
 * Decorates a stdClass object with defaults for properites
 *
 * @package calderawp\object
 */
abstract class stdValidate extends jsonAndArrayable {


	/**
	 * stdClass object being decorated
	 *
	 * @var \stdClass
	 */
	protected $decoratedObj;

	/**
	 * Properties that object should have
	 *
	 * @var array
	 */
	protected  $properties = [];

	/**
	 * Default values for properties
	 *
	 * @var array
	 */
	protected $defaults = [];

	/**
	 * stdValidate constructor.
	 *
	 * @param \stdClass $decoratedObj Object to decorate
	 * @param array $properties Array of property names that object should have
	 * @param array $defaults Array of defaults, keys must match $properties
	 */
	public function __construct( \stdClass $decoratedObj, array $properties = [], array  $defaults = [] )
	{
		$this->decoratedObj = $decoratedObj;
		$this->properties = array_merge( $properties, $this->properties );
		$this->defaults = array_merge( $defaults, $this->defaults );
	}

	/**
	 * @inheritdoc
	 */
	public function __get( $name )
	{

		if( ! isset( $this->decoratedObj->$name ) && isset( $this->defaults[ $name ] ) ){

			return $this->defaults[ $name ];
		}

		if( isset( $this->decoratedObj->$name ) ){
			return $this->decoratedObj->$name;
		}

	}


	/**
	 * Set value of property if it exists
	 *
	 * @param string $prop Name of property
	 * @param mixed $value Value of property
	 *
	 * @throws \Exception If $prop is not string
	 *
	 * @return  bool True.
	 */
	public function __set( $prop, $value )
	{
		if ( is_string( $prop ) ) {
			if( property_exists( $this, $prop ) || isset( $this->properties[ $prop ] ) ){
				$this->$prop = $value;
				return true;
			}


			return false;
		}else{
			throw new \Exception( sprintf( 'Prop passed to stdValidate::__set() (as %s) must be string. Type is %s.', get_class( $this ), gettype( $prop ) ) );

		}

	}

}

