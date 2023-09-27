<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserCreated;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request,$user_id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
//            'company_address' => $request->company_address,
            'phone' => $request->phone,
            'role_id' => $request->role_id,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'zip_code' => $request->zip_code,
            'created_by'=>$user_id,
            'password' => Hash::make($request->password),


        ]);
     


//        event(new Registered($user));
        $dataPermissions = [];

        $data = $request->input('permissions');
        if(sizeof($data) > 0) {
                    foreach ($data as $d) {
                        $permissions=new \stdClass();
                         $permissions-> permission_id = $d;
                          $dataPermissions [] = (array) $permissions;
                    }
                   $user->permissions()->attach( $dataPermissions); 
        }
        $token = app('auth.password.broker')->createToken($user);

        Notification::route('mail',$request->email)->notify(new UserCreated($request,$token));
        return response()->json(['success' => 'Created Successfully', 'id' => $user->id], 201);
        
    }
}
