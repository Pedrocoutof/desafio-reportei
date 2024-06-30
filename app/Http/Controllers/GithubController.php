<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GithubController extends Controller
{
    public function redirect() {
        return Socialite::driver('github')->redirect();
    }

    public function callback() {
        try {
            $user = Socialite::driver('github')->user();
            $userCreated = User::updateOrCreate([
                'github_id' => $user->id,
            ], [
                'email' => $user->email,
                'name' => $user->name,
                'nickname' => $user->nickname,
                'github_id' => $user->id,
                'avatar' => $user->avatar,
                'github_token' => $user->token,
                'password' => encrypt(Str::random(10))
            ]);

            Auth::login($userCreated);
            return redirect()->intended('dashboard');

        } catch (\Exception $e) {
            dd($e);
        }
    }
}
