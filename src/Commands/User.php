<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Commands;

use Illuminate\Support\Facades\Hash;
use Illuminate\Console\Command;


class User extends Command
{
    /**
     * Command name
     */
    protected $signature = 'cms:user
        {--disable : Disables the user as CMS editor}
        {--password= : Secret password of the account (will ask if user will be created)}
        {email : E-Mail of the user}';

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

		if( ( $user = \Illuminate\Foundation\Auth\User::where( 'email', $email )->first() ) === null )
        {
            $user = (new \Illuminate\Foundation\Auth\User())->forceFill( [
                'password' => Hash::make( $this->option( 'password' ) ?: $this->secret( 'Password' ) ),
                'email' => $email,
                'name' => $email,
            ] );
        }

        $user->forceFill( ['cmseditor' => $value] )->save();

        if( $value ) {
            $this->info( sprintf( '  Enabled [%1$s] as CMS user', $email ) );
        } else {
            $this->info( sprintf( '  Disabled [%1$s] as CMS user', $email ) );
        }
    }
}
