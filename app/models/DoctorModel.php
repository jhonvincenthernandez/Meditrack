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

    /**
     * Returns only entries linked to doctor-role users (or unlinked records)
     * for use in dropdowns where staff should be excluded.
     */
    public function all_active_doctors()
    {
        $sql = "SELECT d.*
                FROM {$this->table} AS d
                LEFT JOIN users AS u ON u.id = d.user_id
                WHERE u.role = 'doctor' OR d.user_id IS NULL
                ORDER BY d.name ASC";
        $stmt = $this->db->raw($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            return $this->get_all_with_stats();
        } else {
            $sql = "SELECT d.*,
                    COUNT(DISTINCT CASE WHEN a.status IN ('scheduled','confirmed') THEN a.id END) as appointment_count,
                    COUNT(DISTINCT CASE WHEN ds.is_booked = 0 AND ds.date >= CURDATE() THEN ds.id END) as available_slots
                    FROM doctors d
                    LEFT JOIN appointments a ON a.doctor_id = d.id
                    LEFT JOIN doctor_slots ds ON ds.doctor_id = d.id
                    WHERE (d.name LIKE ? OR d.specialty LIKE ? OR d.contact LIKE ?)
                    GROUP BY d.id
                    ORDER BY d.name ASC";
            
            $searchTerm = '%'.$q.'%';
            $stmt = $this->db->raw($sql, [$searchTerm, $searchTerm, $searchTerm]);
            $allRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data['total_rows'] = count($allRecords);
            $offset = ($page - 1) * $records_per_page;
            $data['records'] = array_slice($allRecords, $offset, $records_per_page);
            
            return $data;
        }
    }

    public function get_all_with_stats() {
        $sql = "SELECT d.*,
                COUNT(DISTINCT CASE WHEN a.status IN ('scheduled','confirmed') THEN a.id END) as appointment_count,
                COUNT(DISTINCT CASE WHEN ds.is_booked = 0 AND ds.date >= CURDATE() THEN ds.id END) as available_slots
                FROM doctors d
                LEFT JOIN appointments a ON a.doctor_id = d.id
                LEFT JOIN doctor_slots ds ON ds.doctor_id = d.id
                GROUP BY d.id
                ORDER BY d.name ASC";
        $stmt = $this->db->raw($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}