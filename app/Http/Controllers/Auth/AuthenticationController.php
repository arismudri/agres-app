<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Lib\ResponseFormatter;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    protected $response;

    public function __construct()
    {
        $this->response = new ResponseFormatter();
    }

    /**
     * Create new user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        try {
            $data = $request->only("name", "email", "password");

            $rule = [
                "name" => ["required", "string", "max:100"],
                "email" => ["required", "email", "max:100", "unique:users"],
                "password" => ["required", "string", "min:8", "max:100"],
            ];

            $validate = Validator::make($data, $rule);

            if ($validate->fails()) {
                return $this->response->fail("Validation fail", $validate->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data["password"] = bcrypt($data["password"]);

            $user = User::create($data);

            return $this->response->success("Register successfully", $user);
        } catch (Exception $e) {
            return $this->response->fail($e->getMessage());
        }
    }

    /**
     * Authenticate user data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $data = $request->only("email", "password");

            $rule = [
                "email" => ["required", "email", "max:100", "exists:users"],
                "password" => ["required", "string", "min:8", "max:100"],
            ];

            $validate = Validator::make($data, $rule);

            if ($validate->fails()) {
                return $this->response->fail("Validation fail", $validate->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!$token = JWTAuth::attempt($data)) {
                return $this->response->fail("Login credentials are invalid");
            }

            return $this->response->success("Login successfully", compact("token"));
        } catch (Exception $e) {
            return $this->response->fail($e->getMessage());
        }
    }

    /**
     * Remove user token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            $data = $request->only("token");

            $rule = [
                "token" => "required"
            ];

            $validate = Validator::make($data, $rule);

            if ($validate->fails()) {
                return $this->response->fail("Validation fail", $validate->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            JWTAuth::invalidate($data["token"]);

            return $this->response->success("Logout successufully");
        } catch (Exception $e) {
            return $this->response->fail($e->getMessage());
        }
    }

    /**
     * Display Authenticated user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function userInfo(Request $request)
    {
        try {
            return $this->response->success("Successfully get user info",  $request->user());
        } catch (Exception $e) {
            return $this->response->fail($e->getMessage());
        }
    }
}
