<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: UserController
 * 
 * Automatically generated via CLI.
 */
class UserController extends Controller {
    public function __construct()
    {
        parent::__construct();
        $this->call->library('lauth');
        $this->lauth->require_admin();

        $this->call->model('UserModel');
    }

    public function index()
    {
        if (!$this->lauth->is_logged_in() || !$this->lauth->has_role('admin')) {
            redirect('/auth/login');
        }

       $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if ($q !== '') {
            $data['users'] = $this->UserModel->search($q);
        } else {
            $data['users'] = $this->UserModel->all();
        }

        $this->call->view('/users/index', $data);
    }

    public function add_form()
    {
        if (!$this->lauth->is_logged_in() || !$this->lauth->has_role('admin')) {
            redirect('/auth/login');
        }
        $this->call->view('/users/add');
    }

    public function save()
    {
        if (!$this->lauth->is_logged_in() || !$this->lauth->has_role('admin')) {
            redirect('/auth/login');
        }

        $name = $this->io->post('name');
        $email = $this->io->post('email');
        $password = $this->io->post('password');
        $role = $this->io->post('role');

        $this->UserModel->insert_user($name, $email, $password, $role);
        redirect('/users');
    }

    /**
     * Show edit form
     */
    public function edit_form($id)
    {
        if (!$this->lauth->is_logged_in() || !$this->lauth->has_role('admin')) {
            redirect('/auth/login');
        }

        $user = $this->UserModel->find($id);
        if (!$user) {
            show_error('User not found', 404);
        }

        // Prevent editing super admin via UI (mirrors index view restriction)
        if (isset($user['role']) && $user['role'] === 'admin') {
            redirect('/users');
        }

        $this->call->view('/users/edit', ['user' => $user]);
    }

    /**
     * Update user record
     */
    public function update($id)
    {
        if (!$this->lauth->is_logged_in() || !$this->lauth->has_role('admin')) {
            redirect('/auth/login');
        }

        $name = trim($this->io->post('name'));
        $email = trim($this->io->post('email'));
        $password = $this->io->post('password'); // optional
        $role = $this->io->post('role');

        $data = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
        ];

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->UserModel->update($id, $data);
        redirect('/users');
    }

    public function delete($id)
    {
        if ($this->lauth->has_role('admin')) {
            $this->UserModel->delete_user($id);
        }
        redirect('/users');
    }
}