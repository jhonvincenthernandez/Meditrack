<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: PatientModel
 * 
 * Automatically generated via CLI.
 */
class PatientModel extends Model {
    protected $table = 'patients';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }

     public function all($with_deleted = false)
    {
        return $this->db->table($this->table)->get_all();
    }

    // 🔍 Search by name or ID
    public function search($keyword) {
        return $this->db->table('patients')
            ->like('name', $keyword)
            ->or_like('age', $keyword)
            ->or_like('gender', $keyword)
            ->or_like('contact', $keyword)
            ->get_all();
    }
}