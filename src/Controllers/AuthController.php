<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AuthService;
use App\Helpers\Filter;
use App\Core\Logger;
use App\Core\Flash;

use App\Exceptions\HTTP\MethodNotAllowedException;


class AuthController extends BaseController
{
    protected $authService;
    protected $filter;
    protected $logger;

    public function __construct(AuthService $authService, Filter $filter, Logger $logger) {
        $this->authService = $authService;
        $this->filter = $filter;
        $this->logger = $logger;
    }

    /*************************************** Registration ***************************************/

    public function register()
    {
        if ($this->authService->is_user_logged_in()) {
            return redirect('/');
        }

        $errors = [];
        $inputs = [];

        if (request()->is_method('post')) {
            $fields = [
                'csrf_token' => 'string',
                'username' => 'string | required | alphanumeric | between: 3, 25 | unique: users, username',
                'email' => 'email | required | email | unique: users, email',
                'password' => 'string | required | secure',
                'password2' => 'string | required | same: password',
                'agree' => 'string | required'
            ];

            $messages = [
                'password2' => [
                    'required' => 'Please enter the password again',
                    'same' => 'The password does not match'
                ],
                'agree' => [
                    'required' => 'You need to agree to the terms of services to register'
                ],
            ];

            [$inputs, $errors] = $this->filter->filter(request()->post(), $fields, $messages);

            if ($errors) {
                return view('auth/register')->with([
                    'errors' => $errors,
                    'inputs'=> $inputs
                ]);
            }

            $activation_code = $this->authService->generate_activation_code();

            if ($this->authService->register($inputs['email'], $inputs['username'], $inputs['password'], $activation_code)) {
                $this->authService->send_activation_email($inputs['email'], $activation_code);
                return redirect('auth/login')->with_message(
                    'Your account has been created successfully. Please check your email to activate your account.'
                );
            }
            
        } elseif (request()->is_method('get')) {
            [$errors, $inputs] = session_flash('errors', 'inputs');
            return view('auth/register')->with([
                'errors' => $errors,
                'inputs'=> $inputs
            ]);
        }
    }

    /*************************************** Login ***************************************/

    public function login()
    {
        if ($this->authService->is_user_logged_in()) {
            return redirect('/');
        }

        $inputs = [];
        $errors = [];

        if (request()->is_method('post')) {
            $fields = [
                'csrf_token' => 'string',
                'username' => 'string | required',
                'password' => 'string | required',
                'remember_me' => 'string'
            ];

            $messages = [
                'username' => [
                    'required' => 'Please enter your username'
                ],
                'password' => [
                    'required' => 'Please enter your password'
                ]
            ];

            [$inputs, $errors] = $this->filter->filter(request()->post(), $fields, $messages);

            if ($errors) {
                return view('auth/login')->with([
                    'errors' => $errors,
                    'inputs'=> $inputs
                ]);
            }

            $user = $this->authService->login($inputs['username'], $inputs['password'], isset($inputs['remember_me']));

            if (!$user) {
                $errors['login'] = 'Invalid username or password';
                return view('auth/login')->with([
                    'errors' => $errors,
                    'inputs'=> $inputs
                ]);
            }

            // login successfully
            return redirect('/');

        } elseif (request()->is_method('get')) {
            [$errors, $inputs] = session_flash('errors', 'inputs');
            
            return view('auth/login')->with([
                'errors' => $errors,
                'inputs'=> $inputs
            ]);
        }
    }

    /*************************************** Logout ***************************************/

    public function logout()
    {
        if (request()->is_method('post')) {
            $this->authService->logout();
            return redirect('/');
        }
        throw new MethodNotAllowedException('Cannot access this page directly');
    }

    /*************************************** Activation ***************************************/

    public function activate()
    {
        $errors = [];
        $inputs = [];

        if (request()->is_method('get')) {
            $fields = [
                'activation_code' => 'string | required'
            ];

            $messages = [
                'activation_code' => [
                    'required' => 'Please enter the activation code'
                ]
            ];

            [$inputs, $errors] = $this->filter->filter(request()->get(), $fields, $messages);

            if (!$errors) {
                if ($this->authService->is_user_verified($inputs['activation_code'])) {
                    if ($this->authService->is_user_logged_in()) {
                        return redirect('/');
                    }
                    return redirect('auth/login')->with_message(
                        'Your account is already activated. Please log in.',
                        Flash::FLASH_INFO
                    );
                }

                if($this->authService->activate($inputs['activation_code'])) {
                    return redirect('auth/login')->with_message(
                        'Your account has been activated successfully.'
                    );
                }
            }
        }

        return redirect('auth/register')->with_message(
            'The activation link is not valid, please register again.', 
            Flash::FLASH_ERROR
        );
    }
}
