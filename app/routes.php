<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::bind('project', function($slug)
{
	return Project::where('slug', $slug)->first();
});

Route::get('/', function()
{
	return View::make('index');
});

Route::get('/login', [
	'as' => 'login_path',
	'uses' => 'SessionsController@login'
]);

Route::get('/logout', [
	'as' => 'logout_path',
	'uses' => 'SessionsController@logout'
]);

Route::put('/conduit_certificate', [
	'before' => 'auth',
	'as' => 'conduit_certificate_path',
	'uses' => 'UsersController@updateCertificate'
]);

Route::get('/projects/{project}', [
	'as' => 'project_path',
	'uses' => 'ProjectsController@show'
]);

Route::get('/projects/{project}/sprints/create', [
	'as' => 'create_sprint_path',
	'uses' => 'SprintsController@create'
]);

Route::post('projects/{project}/sprints/store', [
	'before' => 'auth',
	'as' => 'store_sprint_path',
	'uses' => 'SprintsController@store'
]);
