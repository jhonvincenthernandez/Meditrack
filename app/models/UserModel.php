<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: UserModel
 * 
 * Automatically generated via CLI.
 */
class UserModel extends Model {
    protected $table = 'users';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }

     public function get_all_users()
    {
        return $this->db->table($this->table)->get_all();
    }

    public function delete_user($id)
    {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }

     public function all($with_deleted = false)
    {
        return $this->db->table($this->table)->get_all();
    }

    public function insert_user($name, $email, $password, $role)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->db->table($this->table)->insert([
            'name' => $name,
            'email' => $email,
            'password' => $hash,
            'role' => $role
        ]);
    }

     // 🔍 Search by name or email
    public function search($keyword) {
        return $this->db->table($this->table)
            ->like('name', $keyword)
            ->or_like('email', $keyword)
            ->get_all();
    }
}