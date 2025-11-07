<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: AuthController
 */
class AuthController extends Controller
{
    public function login()
    {
        if ($this->io->method() === 'post') {
            $email = $this->io->post('email');
            $password = $this->io->post('password');

            if ($this->lauth->login($email, $password)) {
                $subject = "Welcome to MediTrack+";
                $message = "
                <h3>Hello {$email},</h3>
                <p>Thank you for using Meditrack.</p>
                ";
                sendMail($email, $subject, $message);


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
                    default:
                        redirect('/');
                }
            } else {
                $data['error'] = 'Invalid email or password.';
                $this->call->view('/auth/login', $data);
                return;
            }
        }

        // Show login page (optionally with flash)
        $data = [];
        if (!empty($_SESSION['reg_success'])) {
            $data['reg_success'] = $_SESSION['reg_success'];
            unset($_SESSION['reg_success']);
        }
        $this->call->view('/auth/login', $data);
    }

    public function register()
    {
        $this->call->library('lauth');

        if ($this->io->method() === 'post') {
            $name = trim($this->io->post('name'));
            $email = trim($this->io->post('email'));
            $password = (string)$this->io->post('password');
            $confirm = (string)($this->io->post('confirm_password') ?? '');
            $role = strtolower(trim($this->io->post('role')));

            // Basic validations
            if ($password !== $confirm) {
                $data['error'] = 'Passwords do not match.';
                $this->call->view('/auth/register', $data);
                return;
            }
            if (!in_array($role, ['doctor','staff'], true)) {
                $data['error'] = 'Invalid role. Please choose Doctor or Staff.';
                $this->call->view('/auth/register', $data);
                return;
            }
            if (strlen($password) < 6) {
                $data['error'] = 'Password must be at least 6 characters.';
                $this->call->view('/auth/register', $data);
                return;
            }

            if ($this->lauth->register($name, $email, $password, $role)) {
                $subject = "Welcome to MediTrack+";
                $message = "
                <h3>Hello {$role}: {$name},</h3>
                <p>Thank you for trusting our clinic.</p>
                ";
                sendMail($email, $subject, $message);


                $_SESSION['reg_success'] = 'Account created successfully. You can now log in.';
                redirect('/auth/login');
            } else {
                $data['error'] = 'Registration failed. Email may already be in use.';
                $this->call->view('/auth/register', $data);
                return;
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