<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RestorePasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rehash:passwords {--sendmail=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This change all password in MD5 to AES-256';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $parameters = $this->option( 'sendmail' );
        if( $parameters == 1 ){
            \Mail::send( 'emails.sync', array( 'datas' => array( \App\Users::get(), \App\Sellers::get() ) ), function( $message ){
                $message->from( env( 'MAIL_FROM_ADDRESS' ), env( 'MAIL_FROM_NAME' ) );
                $message->to( 'carlos@grainchain.io', 'Pruebas' );
                $message->priority(1);
            });
            $this->info( json_encode(array( 'status' => true, 'msg' => 'Email sent successfully' )));
            return false;
        }
        if( $parameters == 0 ){
            $r = storage_path() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'passwords.txt';
            if( !\File::exists( $r ) )
                $this->error( 'File not found' );  
            $file = \File::get( $r );
            $fails = '';
            $count = 0;
            foreach( explode( "\n", nl2br( $file ) ) as $key => $valor ){
                if( $valor != '' ){
                    $rows = explode( ' MD5 : ', $valor );
                    if( isset( $rows[ 0 ] ) && isset( $rows[ 1 ] ) ){
                        if( !\App\Users::where( 'password', $rows[ 0 ] )->get()->isEmpty() )
                            \App\Users::where( 'password', $rows[ 0 ] )->update( array( 'password' => bcrypt( str_replace('<br />', '', $rows[ 1 ]) ) ) );
                        if( !\App\Sellers::where( 'password', $rows[ 0 ] )->get()->isEmpty() )
                            \App\Sellers::where( 'password', $rows[ 0 ] )->update( array( 'password' => bcrypt( str_replace('<br />', '', $rows[ 1 ]) ) ) );
                        $count++;
                    } else
                        $fails .= $valor . "\n";
                }
            }
            $this->info( json_encode(array( 'status' => true, 'correct' => $count, 'fails' => $fails )));
            return false;
        }
    }
}
