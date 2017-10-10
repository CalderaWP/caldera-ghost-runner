<?php


namespace calderawp\ghost\Plugins;
use Pusher\Commands\UpdatePlugin;


/**
 * Class Plugin
 *
 * THIS Should be an interface/abstract class/CF as reference implementation...
 * @package calderawp\ghost\Plugins
 */
class Plugin {

	/** @var  string */
	protected $branch;

	/**
	 * Nonce action string
	 *
	 * @var string
	 */
	const ACTION = 'pluginUpdate';

	//should be in interface
	public function getRepo(){
		return 'calderawp/caldera-forms';
	}

	public function __construct( $branch )
	{
		$this->branch = $branch;
	}

	public function install(){
		if( $this->hasPusher() ){
			$command = new \Pusher\Commands\InstallPlugin(array(
					'repository' => $this->getRepo(),
					'branch' => $this->branch,
					'type' => 'gh'
				)
			);

			$this->runPusherCommand( $command );
		}


	}

	public function update( $file ){
		if( $this->hasPusher() ){
			$command = new UpdatePlugin( array(
				'file' => $file,
				'repository' => $this->getRepo(),
			));
			$this->runPusherCommand( $command );
		}
	}

	protected function hasPusher()
	{
		return class_exists( '\Pusher\Pusher' );
	}

	/**
	 * @param $command
	 */
	protected function runPusherCommand( $command )
	{
		$pusher = \Pusher\Pusher::getInstance();

		$dashboard = $pusher->make( "Pusher\Dashboard" );

		$dashboard->execute( $command );
	}
}