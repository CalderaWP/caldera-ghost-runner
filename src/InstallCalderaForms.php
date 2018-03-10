<?php


namespace calderawp\ghost;


use Pusher\Commands\InstallPlugin;
use Pusher\Pusher;

class InstallCalderaForms
{
    /**
     * @var InstallPlugin
     */
    protected $command;

    public function __construct( $branch )
    {
        $this->command = new InstallPlugin( array (
            'action' => 'install-plugin',
            'type' => 'gh',
            'repository' => 'caldera-forms',
            'branch' => $branch,
            'subdirectory' => '',
        ) );



    }

    public function install()
    {
        $dashbaord = Pusher::getInstance()->make('Pusher\Dashboard');
        $result = $dashbaord->execute( $this->command );
        return $result;
    }
}