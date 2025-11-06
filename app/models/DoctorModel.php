<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: DoctorModel
 * 
 * Automatically generated via CLI.
 */
class DoctorModel extends Model {
    protected $table = 'doctors';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    public function all($with_deleted = false)
    {
        return $this->db->table($this->table)->get_all();
    }

    // Find doctor by linked user_id
    public function find_by_user_id($user_id)
    {
        return $this->db->table($this->table)
                        ->where('user_id', $user_id)
                        ->get();
    }

    // 🔍 Search by name or ID
    public function search($keyword) {
        return $this->db->table('doctors')
            ->like('name', $keyword)
            ->or_like('specialty', $keyword)
            ->or_like('contact', $keyword)
            ->get_all();
    }

     public function page($q, $records_per_page = null, $page = null) {
            if (is_null($page)) {
                return $this->db->table('doctors')->get_all();
            } else {
                $query = $this->db->table('doctors');
                
                // Build LIKE conditions
                $query->like('id', '%'.$q.'%')
                    ->or_like('user_id', '%'.$q.'%')
                    ->or_like('name', '%'.$q.'%')
                    ->or_like('specialty', '%'.$q.'%')
                    ->or_like('contact', '%'.$q.'%');

                // Clone before pagination
                $countQuery = clone $query;

                $data['total_rows'] = $countQuery->select_count('*', 'count')
                                                ->get()['count'];

                $data['records'] = $query->pagination($records_per_page, $page)
                                        ->get_all();

                return $data;
            }
        }

}