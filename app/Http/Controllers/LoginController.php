<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    function login(Request $request) {
        $data = $request->getContent();
        try {
            $client = new \Google\Client();
            $client->setAuthConfig('../client_secret_816663279962-flqvijjsct88lrm9nmfl1qgcj22l3fsl.apps.googleusercontent.com.json');
            $client->addScope(\Google\Service\Classroom::CLASSROOM_COURSES_READONLY);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            // Your redirect URI can be any registered URI, but in this example
            // we redirect back to this same page
            $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            $client->setRedirectUri($redirect_uri);

            $response['response'] = $client;
            $http_status_code = 200;
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
