<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: AuthController
 * 
 * Automatically generated via CLI.
 */
class AuthController extends Controller
{
    public function login()
    {
        if ($this->io->method() === 'post') {
            $email = $this->io->post('email');
            $password = $this->io->post('password');

            if ($this->lauth->login($email, $password)) {
                $role = $this->session->userdata('role');
                switch ($role) {
                    case 'admin':
                        redirect('/dashboard_admin');
                        break;
                    case 'doctor':
                        redirect('/dashboard_doctor');
                        break;
                    case 'staff':
                        redirect('/dashboard_staff');
                        break;
                }
            } else {
                $data['error'] = 'Invalid email or password.';
                $this->call->view('/auth/login', $data);
                return;
            }
        }
        $this->call->view('/auth/login');
    }

    public function register()
    {
        $this->call->library('lauth');

        if ($this->io->method() == 'post') {
            $name = $this->io->post('name');
            $email = $this->io->post('email');
            $password = $this->io->post('password');
            $role = $this->io->post('role');

            if ($this->lauth->register($name, $email, $password, $role)) {
                redirect('/auth/login');
            }
        }

        $this->call->view('/auth/register');
    }

    public function logout()
    {
        $this->call->library('lauth');
        $this->lauth->logout();
        redirect('/auth/login');
    }
}