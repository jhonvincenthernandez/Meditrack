<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AppointmentModel extends Model {
    protected $table = 'appointments';
    protected $primary_key = 'id';

    public function all_with_details() {
        return $this->db->table($this->table . ' AS a')
            ->select('a.*, p.name AS patient_name, d.name AS doctor_name, s.date AS slot_date, s.start_time, s.end_time')
            ->left_join('patients AS p', 'p.id = a.patient_id')
            ->left_join('doctors AS d', 'd.id = a.doctor_id')
            ->left_join('doctor_slots AS s', 's.id = a.slot_id')
            ->order_by('s.date', 'DESC')
            ->order_by('s.start_time', 'DESC')
            ->get_all();
    }

    public function insert_appointment($data) {
        // Use base Model insert to return the last inserted ID
        return $this->insert($data);
    }

    public function is_slot_booked($slot_id) {
        // Consider a slot booked if an appointment references it
        $exists = $this->db->table($this->table)
            ->where('slot_id', $slot_id)
            ->get();
        return !empty($exists);
    }

    public function delete_appointment($id) {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }

    public function get_with_details($id) {
        return $this->db->table($this->table . ' AS a')
            ->select('a.*, p.name AS patient_name, d.name AS doctor_name, s.date AS slot_date, s.start_time, s.end_time, s.doctor_id AS slot_doctor_id')
            ->left_join('patients AS p', 'p.id = a.patient_id')
            ->left_join('doctors AS d', 'd.id = a.doctor_id')
            ->left_join('doctor_slots AS s', 's.id = a.slot_id')
            ->where('a.id', $id)
            ->get();
    }

    /**
     * Update appointment status with optional completed_at.
     * Allowed statuses: scheduled, completed, cancelled
     */
    public function mark_status($id, $status)
    {
        $allowed = ['scheduled','completed','cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $data = ['status' => $status];
        // Only set completed_at when moving to completed; null otherwise (if column exists)
        $data['completed_at'] = ($status === 'completed') ? date('Y-m-d H:i:s') : null;
        return $this->db->table($this->table)->where('id', $id)->update($data);
    }
}
