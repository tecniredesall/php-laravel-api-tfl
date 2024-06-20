<?php

namespace App\Http\Controllers\API;

use Lcobucci\JWT\Parser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller as Controller;
use Illuminate\Support\Facades\Log;

class Sign extends Controller {

	protected $isMobile = false;

	/**
	 * success response method.
	 *
	 * @return \Illuminate\Http\Response
	 */
	private function sendSuccess( $message, $result ){
		$response = [
			'status' => true,
			'message' => $message,
		];
		$data = array_merge( $response, $result );
		return response()->json( $data, 200 );
	}

	/**
	 * return error response.
	 *
	 * @return \Illuminate\Http\Response
	 */
	private function sendError( $error, $errorMessages = [], $code = 401 ){
		$response = [
			'status' => false,
			'message' => $error,
		];
		if( !empty( $errorMessages ) )
			$response[ 'data' ] = $errorMessages;
		return response()->json( $response, $code );
	}

	/**
	 * Process the login form submitted, check for the
	 * credentials in the users table. If match found,
	 * else, display the error message.
	 */

	public function in( Request $request ){
		$this->isMobile = isset( $request->isMobile ) && $request->isMobile == true ? true : false;
		if( !isset( $request->email ) || !isset( $request->password ) )
			return $this->sendError( 'The fields is empty', array(), 400 );
		$s = \App\Users::with( 'security' )->where( 'email', $request->email );
		if( $s->count() == 0 )
			return $this->sendError( $request->email . ' not found', array(), 404 );
		$rs = \App\Api::iCan( $s->first()->id, 19 ) ? 1 : 0;
		$name = ( $this->isMobile ) ? 'GrainChainMobile' : 'GrainChainWeb';
		if( $rs == 0 )
			return $this->sendError( "You don't have access to this module", array(), 403 );
		if( !\Auth::once( array( 'email' => $request->email, 'password' => $request->password ) ) )
			return $this->sendError( 'Your password is invalid', array(), 401 );
		$user = $request->user();
		//SNS Initializer
		if( $this->isMobile && ( isset( $request->device_id ) && !is_null( $request->device_id ) ) )
			\App\SNS::addIfNotExistDevice( $request );
		$qq = \DB::table( 'oauth_clients' )->where( 'name', $name )->first();
		$guzzle = new \GuzzleHttp\Client(['verify'=> false, 'timeout' => env('TIME_LIMIT')]);
		$params = [
			'grant_type' => 'password',
			'client_id' => $qq->id,
			'client_secret' => $qq->secret,
			'username' => $request->email,
			'password' => $request->password,
			'scope' => '*',
		];

		$response = $guzzle->post( env( 'APP_URL' ) . '/oauth/token', [
			'form_params' => $params,
		]);

		$access_token = json_decode( ( string ) $response->getBody(), true )[ 'access_token' ];
		//dd($access_token);
		$c = 1;
		$t = 0;
		$p = 1;
		$q = \App\Metadata_users::where( 'user_id', $request->user()->id )->get();
		$d = ( $q->count() > 0 ) ? date( 'Y', strtotime( $q[ 0 ]->temp_ini ) ) : date( 'Y' );
		return $this->sendSuccess( 'You been connected successfully', array(
				'data' => $user->toArray(),
				'season' => $d,
				'token' => $access_token,
				'token_type' => 'Bearer',
				'isClose' => $p,
				'metric' => \App\Company_info::select('*')->pluck('metric_system_id')[0]
			)
		);
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function out( Request $request ){
		try{
			$value = $request->bearerToken();
			$id = (new Parser())->parse($value)->claims()->get('jti');
			\DB::table( 'oauth_access_tokens' )->where( 'id', $id )->update([
				'revoked' => true
			]);
			return $this->sendSuccess( 'Disconnected has beed successfully', array() );
		} catch( Exception $e ){
			Log::error($e->getMessage());
			return $this->sendError( 'Internal Server Error', array(), 500 );
		}
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	protected function refreshToken( Request $request ){
		$request->request->add([
			'grant_type' => 'refresh_token',
			'refresh_token' => $request->refresh_token,
			'client_id' => $request->client_id,
			'client_secret' => $request->client_secret,
		]);
		$proxy = Request::create(
			'/oauth/token',
			'POST'
		);
		return \Route::dispatch($proxy);
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function reset( Request $request ){
		$rules = array(
			'hash' => 'required',
			'h' => 'required',
			'password' => 'required|min:6|regex:/^.*(?=.{1,})(?=.*[a-z])(?=.{1,})(?=.*[A-Z])(?=.*[0-9#?!@()$%^&*=_{}[\]:;\"<>,.\/~`±§+-])/',
			'confirm' => 'required|min:6|same:password'
		);
		$messages = array (
			'password.required' => 'Field required',
			'password.regex' => 'The password must be at least 6 characters, uppercase, lowercase, number and/or special characters',
			'confirm.same' => 'Password and confirm password should be the same'
		);
		$validator = \Validator::make($request->all(), $rules, $messages);
		\DB::beginTransaction();
		if( $validator->fails() )
			return $this->sendError( 'Error', $validator->errors(), 400 );
		$model = decrypt( $request->h );
		$data = json_decode( decrypt( $request->hash ), TRUE );
		try{
			$modelo = str_replace( " ", "", str_replace( "'", "\ ", '\App\'' . $model ) );
			$obj = $modelo::where( 'id',  $data[ 'id' ] )->firstOrFail();
			$d = $modelo::find( $obj->id );
			$d->password = \Hash::make( $request->password, [
				'memory' => 1024,
				'time' => 2,
				'threads' => 2,
			]);
			$d->push();
			\DB::commit();
			return $this->sendSuccess( 'Password has been changed successfully', array() );
		} catch( \Illuminate\Database\Eloquent\ModelNotFoundException $e ){
			\DB::rollBack();
			Log::error($e->getMessage());
			return $this->sendError( 'Internal Server Error', array(), 500 );
		}
	}


	public function resetp(Request $request){
		try{
			$data = decrypt( $request["hash"] );
			$data = json_decode( $data, true );
			$data['h'] = $request["h"];
			$data['model'] = decrypt($request["h"]);
			$data['uri'] = $request["i"];
			$data['hash'] = $request["hash"];

			return $data;
		}catch(\Exception $exception){
			return $exception->getMessage();
		}
	}

	public function isactive( Request $request ){
		try{
			$value = $request->bearerToken();
			$id = ( new Parser() )->parse( $value )->getHeader( 'jti' );
			$isActive = \DB::table( 'oauth_access_tokens' )->where( 'id', $id )->get();
			if( $isActive[0]->revoked )
				return $this->sendError( 'Session ended', array(), 401 );
		} catch( Exception $e ){
			Log::error($e->getMessage());
			return $this->sendError( 'Internal Server Error', array(), 500 );
		}
	}

}?>
