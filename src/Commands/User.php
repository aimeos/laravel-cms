<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Commands;

use Illuminate\Console\Command;


class User extends Command
{
    /**
     * Command name
     */
    protected $signature = 'cms:user {--disable : Disables an user as CMS editor} {email : E-Mail of the user}';

    /**
     * Command description
     */
    protected $description = 'Authorization for CMS users';


    /**
     * Execute command
     */
    public function handle()
    {
        $email = $this->argument( 'email' );
        $value = $this->option( 'disable' ) ? 0 : 1;

		\Illuminate\Foundation\Auth\User::where( 'email', $email )->firstOrFail()
			->forceFill( ['cmseditor' => $value] )
            ->save();

        if( $value ) {
            $this->info( sprintf( '  Enabled [%1$s] as CMS user', $email ) );
        } else {
            $this->info( sprintf( '  Disabled [%1$s] as CMS user', $email ) );
        }
    }
}
