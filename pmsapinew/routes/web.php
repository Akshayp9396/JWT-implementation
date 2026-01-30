<?php

/** @var \Laravel\Lumen\Routing\Router $router */
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/*$router->get('/', function () use ($router) {
    return $router->app->version();
});*/

$router->get('/test', function () use ($router) {
    return  response()->json([
    'name' => 'Abigail',
    'state' => 'CA',
]);    

});


// $router->group(['prefix' => 'api'], function () use ($router) {
//   $router->get('authors',  ['uses' => 'AuthorController@showAllAuthors']);

//   $router->get('authors/{id}', ['uses' => 'AuthorController@showOneAuthor']);

//   $router->post('checkIn', ['uses' => 'ApiController@checkIn'] );
//   $router->post('extendCheckIn', ['uses' => 'ApiController@extendCheckIn'] );
//   $router->post('checkOut', ['uses' => 'ApiController@checkOut'] );
//   $router->post('changePlan', ['uses' => 'ApiController@changePlan'] );

//   $router->delete('authors/{id}', ['uses' => 'AuthorController@delete']);

//   $router->put('authors/{id}', ['uses' => 'AuthorController@update']);
//   $router->post('apiTest', ['uses' => 'ApiController@testFunc'] );
//   $router->post('shiftRoom', ['uses' => 'ApiController@shiftRoom'] );

//   $router->post('resendPassword', ['uses' => 'ApiController@resendPassword'] );

// });  

$router->post('api/login', ['uses' => 'AuthController@login']);

$router->group(['prefix' => 'api', 'middleware' => 'auth'], function () use ($router) {

    // Rajagiri Hospital APIs from Documentation 
    $router->post('checkIn', ['uses' => 'ApiController@checkIn']);     
    $router->post('changePlan', ['uses' => 'ApiController@changePlan']); 
    $router->post('checkOut', ['uses' => 'ApiController@checkOut']);   

    // Other Secured APIs
    $router->post('extendCheckIn', ['uses' => 'ApiController@extendCheckIn']);
    $router->post('shiftRoom', ['uses' => 'ApiController@shiftRoom']);
    $router->post('resendPassword', ['uses' => 'ApiController@resendPassword']);
    $router->post('apiTest', ['uses' => 'ApiController@testFunc']);

    // Secured Author Management
    $router->get('authors', ['uses' => 'AuthorController@showAllAuthors']);
    $router->get('authors/{id}', ['uses' => 'AuthorController@showOneAuthor']);
    $router->delete('authors/{id}', ['uses' => 'AuthorController@delete']);
    $router->put('authors/{id}', ['uses' => 'AuthorController@update']);

});