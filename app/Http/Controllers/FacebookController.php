<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SammyK;
use Facebook\Exceptions\FacebookSDKException;
use Session;
use Auth;
use DB;


class FacebookController extends Controller
{
    private $callback_url;

    public function __construct()
    {
        $this->callback_url = env('FACEBOOK_CALLBACK');
    }

    public function index(SammyK\LaravelFacebookSdk\LaravelFacebookSdk $fb, Request $request)
    {
        // Send an array of permissions to request
        $login_url = $fb->getRedirectLoginHelper()->getLoginUrl($this->callback_url, ['email']);

//        dd($request->session()->all());
       // $request->session()->flush();

        return view('welcome', ['login_url' => $login_url]);
    }

    public function callback(SammyK\LaravelFacebookSdk\LaravelFacebookSdk $fb)
    {
        // Получаем токен
        try {
            $token = $fb->getAccessTokenFromRedirect($this->callback_url);
        } catch (FacebookSDKException $e) {
            dd($e->getMessage());
        }

        // Access token will be null if the user denied the request
        // or if someone just hit this URL outside of the OAuth flow.
        if (! $token) {
            // Get the redirect helper
            $helper = $fb->getRedirectLoginHelper();

            if (! $helper->getError()) {
                abort(403, 'Unauthorized action.');
            }

            // User denied the request
            dd(
                $helper->getError(),
                $helper->getErrorCode(),
                $helper->getErrorReason(),
                $helper->getErrorDescription()
            );
        }

        if (! $token->isLongLived()) {
            // OAuth 2.0 client handler
            $oauth_client = $fb->getOAuth2Client();

            // Extend the access token.
            try {
                $token = $oauth_client->getLongLivedAccessToken($token);
            } catch (FacebookSDKException $e) {
                dd($e->getMessage());
            }
        }

        $fb->setDefaultAccessToken($token);

        // Save for later
        Session::put('fb_user_access_token', (string) $token);

        // Get basic info on the user from Facebook.
        try {
            $response = $fb->get('/me?fields=id,name,email');
            $posts = $fb->get('/me/feed?fields=description,comments,shares,likes,picture&limit=5');
        } catch (FacebookSDKException $e) {
            dd($e->getMessage());
        }


        // Convert the response to a `Facebook/GraphNodes/GraphUser` collection
        $facebook_user = $response->getGraphUser();
        $user_posts = $posts->getGraphEdge()->asArray();

       // dd($user_posts);

        $table = DB::table('users');
        $user = $table->where('email', $facebook_user['email'])->first();

        if($user == null) {
            $new_user = $table->insertGetId([
                'name' => $facebook_user['name'],
                'email' => $facebook_user['email']
            ]);
            Auth::loginUsingId($new_user);
        } else {
            Auth::loginUsingId($user->id);
        }

        Session::put('posts', (array)$user_posts);

        return redirect()->route('home');
    }
}
