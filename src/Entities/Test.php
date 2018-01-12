<?php


namespace calderawp\ghost\Entities;


/**
 * Class Test
 * @package calderawp\ghost\Entities
 */
class Test  {

    /**
     * stdClass object being decorated
     *
     * @var \stdClass
     */
    protected $decoratedObj;

    protected  $properties = array(
        'config',
        'description',
        'gitissue',
        'ghostinspectorid',
        'release',
        'testsuite',
        'xtestreason',
        'helpscout',
    );

    protected $defaults = array(
        'config'           => array(),
        'description'      => 'No description',
        'gitissue'         => 0,
        'ghostinspectorid' => 0,
        'release'          => 0,
        'testsuite'        => 0,
        'xtestreason'      => '',
        'helpscout'        => 0,
    );

    /**
     * stdValidate constructor.
     *
     * @param \stdClass $decoratedObj Object to decorate
     * @param array $properties Array of property names that object should have
     * @param array $defaults Array of defaults, keys must match $properties
     */
    public function __construct(  $decoratedObj, array $properties = array(), array  $defaults = array() )
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





	public function pageSlug()
	{
		return sanitize_title_with_dashes( $this->name );
	}

    /**
     * @return array
     */
	public function getFormConfig()
    {
        if( ! isset( $this->decoratedObj->config ) ){
            return array();
        }
        if( is_string( $this->decoratedObj->config ) ){
            $this->decoratedObj->config = json_decode( $this->decoratedObj->config, true );
        }

        if( is_object( $this->decoratedObj->config ) ){
            $this->decoratedObj->config = (array) $this->decoratedObj->config;
        }

        return $this->decoratedObj->config;
    }
}