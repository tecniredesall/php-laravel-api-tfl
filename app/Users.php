<?php

namespace App;

    use Carbon\Carbon;
    use Laravel\Passport\HasApiTokens;
    use Illuminate\Notifications\Notifiable;
    use Illuminate\Foundation\Auth\User as Authenticatable;

// use Illuminate\Contracts\Auth\Authenticatable;

class Users extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'lastname', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'sigimage'
    ];

    public function metadatas()
    {
        return $this->hasOne('\App\MetaData', 'user_id');
    }

    public function security()
    {
        return $this->belongsToMany('\App\Security_process', 'security_grant', 'user', 'process');
    }

    protected static function mostrar($id = null, $request = array())
    {
        $query = is_null($id) ? self::where('status', '<>', 2)->selectRaw('id, name,lastname, email, status, address, state, city, phone, source_id, branch_id') : self::where('id', $id)->selectRaw('id, name,lastname, email, status, address, state, city, phone, source_id, branch_id');
        $query = $query->paginate($request->input('limiter', env('PER_PAGE')))->toArray();
        $users = &$query['data'];
        $usersId = collect($users)->pluck('id');
        $permisions = \App\Security_grant::selectRaw('GROUP_CONCAT(process) as permision,user as user_id')->whereIn('user', $usersId)
            ->groupBy('user')
            ->get();
        foreach ($users as &$item) {
            $item['securityArray'] = [];
            foreach ($permisions as $key => $permision) {
                if ($item['id'] == $permision->user_id) {
                    $permision_user = explode(',', $permision->permision);
                    $item['securityArray'] = array_map(function ($param) {
                        return (int)($param);
                    }, $permision_user);
                    $permisions->forget($key);
                    break;
                }
            }
        }
        return $query;
    }

    private static function saveOrUpdate($request, $id)
    {
        \DB::beginTransaction();
        try {
            $obj = new self;
            $obj->id = $id;

            if (isset($request->password)){
            	$obj->contrasena = $request->password;
            }
            else{
            	if( !(\App\Cudrequest::validUuid($id)) ){
	                $pass = \DB::table('users')->where('id', $id)->pluck('password')[0];
	                $obj->contrasena = isset($pass) ? $pass : NULL;
	            }else {
	                $CUDRequest = \App\Cudrequest::where('cudrequest_id', $id)->pluck('request')[0];
	                $obj->contrasena = isset(json_decode($CUDRequest)->contrasena) ? json_decode($CUDRequest)->contrasena : NULL;
	            }
            }

            $obj->name = isset($request->name) ? $request->name : NULL;
            $obj->lastname = isset($request->lastname) ? $request->lastname : NULL;
            $obj->email = isset($request->email) ? $request->email : NULL;
            $obj->status = isset($request->status) ? $request->status : 0;
            $obj->address = isset($request->address) ? $request->address : NULL;
            $obj->state = isset($request->state) ? $request->state : NULL;
            $obj->city = isset($request->city) ? $request->city : NULL;
            $obj->phone = isset($request->phone) ? $request->phone : NULL;

            \App\Cudrequest::process( 'Users', $obj, $id );

            \DB::commit();
            return self::mostrar( null, $request );
        } catch (Exception $e) {
            \DB::rollBack();
            return false;
        }
    }

    private static function del($id)
    {
        try{
            return \App\Cudrequest::delrequest($id, 'Users');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected static function guardar($request, $id)
    {
        return static::saveOrUpdate($request, $id);
    }

    protected static function actualizar($request, $id)
    {
        return static::saveOrUpdate($request, $id);
    }

    protected static function eliminar($id)
    {
        return static::del($id);
    }
}
