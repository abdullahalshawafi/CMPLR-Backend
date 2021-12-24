<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Misc\Helpers\Errors;
use App\Http\Requests\Auth\GoogleRequest;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Services\Auth\LoginService;
use App\services\Auth\RegisterService;
use Illuminate\Auth\Events\Registered;

class GoogleController extends Controller
{
    protected $loginService;
    protected $RegisterService;
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(LoginService $loginService, RegisterService $RegisterService)
    {
        $this->loginService = $loginService;
        $this->RegisterService = $RegisterService;
    }
    public function GoogleLogin()
    {

        return Socialite::driver('google')->redirect();
    }
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $token = $user->token;
            //$user2 = Socialite::driver('google')->userFromToken($token);
            dd($user);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function GetUserFromGoogle(Request $request)
    {
        $token = $request->token;
        try {
            $user = Socialite::driver('google')->userFromToken($token);
        } catch (\Throwable $th) {
            $error['token'] = Errors::TOKEN_ERROR;
            return $this->error_response(Errors::ERROR_MSGS_422, $error, 422);
        }
       // $check = User::where('email', $user->email)->first();
        $check = User::where('google_id', $user->id)->first();
        if ($check) {
            $user = Auth::loginUsingId($check->id);
            try {
                $userLoginToken = $user->CreateToken('authToken')->accessToken;
            } catch (\Throwable $th) {
                $error['token'] = Errors::GENERATE_TOKEN_ERROR;
                return $this->error_response(Errors::ERROR_MSGS_500, $error, 500);
            }
            return response()->json(['user' => auth()->user(), 'token' => $userLoginToken], 200);
        } else {
            $error['user'] = 'you should register first';
            return $this->error_response(Errors::ERROR_MSGS_401, $error, 401);
        }
    }

    public function SignUpWithGoogle(GoogleRequest $request)
    {
        $request->validate([
            'blog_name' => ['required', 'unique:blogs', 'max:22', 'alpha_dash'],
            'age' => ['required', 'integer', 'between: 18,80'],
            'token' => ['required']
        ]);
        try {
            $user = Socialite::driver('google')->userFromToken($request->token);
        } catch (\Throwable $th) {
            $error['token'] = Errors::TOKEN_ERROR;
            return $this->error_response(Errors::ERROR_MSGS_422, $error, 422);
        }
        if (User::where('google_id', $user->id)->first()) 
        {
            $user = Auth::loginUsingId($user->id);
            try {
                $userLoginToken = $user->CreateToken('authToken')->accessToken;
            } catch (\Throwable $th) {
                $error['token'] = Errors::GENERATE_TOKEN_ERROR;
                return $this->error_response(Errors::ERROR_MSGS_500, $error, 500);
            }
            return response()->json(['user' => auth()->user(), 'token' => $userLoginToken], 200);
        }

        $user = $this->RegisterService->CreateUserGoogle($user->email, $request->age, $user->id);
        if (!$user) {
            $error['user'] = Errors::CREATE_ERROR;
            $this->error_response(Errors::ERROR_MSGS_500, $error, 500);
        }

        $blog = $this->RegisterService->CreateBlog($request->blog_name, $user);

        if (!$blog)
            return $this->error_response(Errors::ERROR_MSGS_500, Errors::CREATE_ERROR, 500);

        $avatar = $blog->settings->avatar;
        // link user with blog
        $link_user_blog = $this->RegisterService->LinkUserBlog($user, $blog);

        if (!$link_user_blog)
            return $this->error_response(Errors::ERROR_MSGS_500, 'link error', 500);

        //create the access token to the user   
        $generate_token = $this->RegisterService->GenerateToken($user);

        if (!$generate_token)
            return $this->error_response(Errors::ERROR_MSGS_500, ERRORS::GENERATE_TOKEN_ERROR, 500);

        $request['blog']=$blog;
        $request['token'] = $user->token();


        // this method will return true if authentication was successful
        if (Auth::loginUsingId($user->id)) {
            $request['user'] = Auth::user();
            // Fire Registered event
            event(new Registered($user));
            $resource =  new RegisterResource($request);
            return $this->success_response($resource, 201);
        }

        return $this->error_response(Errors::ERROR_MSGS_500, Errors::CREATE_ERROR, 500);
    }
}
