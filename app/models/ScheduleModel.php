<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ScheduleModel extends Model {
    protected $table = 'doctor_slots';
    protected $primary_key = 'id';

        // Get all slots with doctor info and latest appointment status (if any)
        public function all_slots_with_doctor() {
                $sql = "SELECT s.id, s.date, s.start_time, s.end_time, s.is_booked,
                                             d.name AS doctor_name, d.specialty,
                                             a.status AS appt_status
                                FROM {$this->table} AS s
                                LEFT JOIN doctors AS d ON d.id = s.doctor_id
                                LEFT JOIN (
                                    SELECT t1.* FROM appointments t1
                                    INNER JOIN (
                                        SELECT slot_id, MAX(id) AS max_id
                                        FROM appointments
                                        GROUP BY slot_id
                                    ) t2 ON t1.id = t2.max_id
                                ) a ON a.slot_id = s.id
                                ORDER BY s.date ASC, s.start_time ASC";
                $stmt = $this->db->raw($sql);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Get a doctor's own slots with latest appointment status
        public function doctor_slots_with_status($doctor_id) {
                $sql = "SELECT s.id, s.date, s.start_time, s.end_time, s.is_booked,
                                             a.status AS appt_status
                                FROM {$this->table} AS s
                                LEFT JOIN (
                                    SELECT t1.* FROM appointments t1
                                    INNER JOIN (
                                        SELECT slot_id, MAX(id) AS max_id
                                        FROM appointments
                                        GROUP BY slot_id
                                    ) t2 ON t1.id = t2.max_id
                                ) a ON a.slot_id = s.id
                                WHERE s.doctor_id = ?
                                ORDER BY s.date ASC, s.start_time ASC";
                $stmt = $this->db->raw($sql, [$doctor_id]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    // Check if new slot overlaps existing slots
    public function check_overlap($doctor_id, $date, $start_time, $end_time, $exclude_id = null) {
        // Robust DB-level check: overlap if NOT (new_end <= existing.start OR new_start >= existing.end)
        $sql = "SELECT 1 FROM {$this->table}
                WHERE doctor_id = ? AND date = ?
                  AND NOT (? <= start_time OR ? >= end_time)";
        $params = [$doctor_id, $date, $end_time, $start_time];
        if (!empty($exclude_id)) {
            $sql .= " AND id <> ?";
            $params[] = $exclude_id;
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->raw($sql, $params);
        return (bool)$stmt->fetchColumn();
    }

    // Create slot
    public function create_slot($data) {
        return $this->db->table($this->table)->insert($data);
    }

    // Update slot
    public function update_slot($id, $data) {
        return $this->db->table($this->table)->where('id', $id)->update($data);
    }

    // Delete slot
    public function delete_slot($id) {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }

    // Get distinct future dates for a doctor's available (not booked) slots
    public function get_doctor_dates($doctor_id) {
        // Only expose dates with future, unbooked slots.
        $sql = "SELECT DATE(date) AS date
                FROM {$this->table}
                WHERE doctor_id = ?
                  AND is_booked = 0
                  AND (date > CURDATE() OR (date = CURDATE() AND start_time > CURTIME()))
                GROUP BY DATE(date)
                ORDER BY date ASC";
        $stmt = $this->db->raw($sql, [$doctor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get available slots for a doctor on a given date
    public function get_available_slots($doctor_id, $date) {
        $qb = $this->db->table($this->table)
            ->where('doctor_id', $doctor_id)
            ->where('date', $date)
            ->where('is_booked', 0);
        // If requesting today's date, filter out slots that already started
        if ($date === date('Y-m-d')) {
            $qb->where('start_time', '>', date('H:i:s'));
        }
        return $qb->order_by('start_time', 'ASC')->get_all();
    }

    // Get a slot by id
    public function get_slot($slot_id) {
        return $this->db->table($this->table)->where('id', $slot_id)->get();
    }

    // Mark a slot as booked/unbooked
    public function set_booked($slot_id, $booked = 1) {
        return $this->db->table($this->table)->where('id', $slot_id)->update(['is_booked' => $booked ? 1 : 0]);
    }
}
