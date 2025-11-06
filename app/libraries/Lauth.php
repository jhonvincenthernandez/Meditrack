<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Library: Lauth
 * 
 * Automatically generated via CLI.
 */
class Lauth
{
    protected $_lava;

    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_lava->call->database();
        $this->_lava->call->library('session');
    }

    public function register($name, $email, $password, $role = 'staff')
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->_lava->db->table('users')->insert([
            'name' => $name,
            'email' => $email,
            'password' => $hash,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function login($email, $password)
    {
        $user = $this->_lava->db->table('users')->where('email', $email)->get();

        if ($user && password_verify($password, $user['password'])) {
            $this->_lava->session->set_userdata([
                'user_id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'logged_in' => true
            ]);
            return true;
        }
        return false;
    }

    public function is_logged_in()
    {
        return (bool) $this->_lava->session->userdata('logged_in');
    }

    public function has_role($role)
    {
        return $this->_lava->session->userdata('role') === $role;
    }

    public function require_login()
    {
        if (!$this->is_logged_in()) {
            redirect('/auth/login');
        }
    }

     public function require_admin()
    {
        if (!$this->is_logged_in() || !$this->has_role('admin')) {
            echo '<h3>Access denied: Admins only.</h3>';
            exit;
        }
    }

      /**
     * Require specific role(s)
     * @param string|array $roles
     */
    public function require_role($roles)
    {
        if (!$this->is_logged_in()) {
            redirect('/auth/login');
            exit;
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!in_array($this->_lava->session->userdata('role'), $roles)) {
            echo '<h3>Access denied: You do not have permission to view this page.</h3>';
            exit;
        }
    }

    /**
     * Check if current user is admin
     */
    public function is_admin()
    {
        return $this->has_role('admin');
    }

    /**
     * Check if current user is doctor
     */
    public function is_doctor()
    {
        return $this->has_role('doctor');
    }

    /**
     * Check if current user is staff
     */
    public function is_staff()
    {
        return $this->has_role('staff');
    }


    public function logout()
    {
        $this->_lava->session->unset_userdata(['user_id','name','email','role','logged_in']);
    }
}
