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
        // Only expose dates with future, unbooked slots that are not tied to completed/cancelled appointments.
        $sql = "SELECT DATE(s.date) AS date
                FROM {$this->table} AS s
                LEFT JOIN (
                    SELECT t1.slot_id, t1.status
                    FROM appointments t1
                    INNER JOIN (
                        SELECT slot_id, MAX(id) AS max_id
                        FROM appointments
                        GROUP BY slot_id
                    ) t2 ON t1.id = t2.max_id
                ) AS a ON a.slot_id = s.id
                WHERE s.doctor_id = ?
                  AND s.is_booked = 0
                  AND (s.date > CURDATE() OR (s.date = CURDATE() AND s.start_time > CURTIME()))
                  AND (a.status IS NULL OR a.status NOT IN ('completed','cancelled'))
                GROUP BY DATE(s.date)
                ORDER BY date ASC";
        $stmt = $this->db->raw($sql, [$doctor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get available slots for a doctor on a given date
    public function get_available_slots($doctor_id, $date) {
        $params = [$doctor_id, $date];
        $timeFilter = '';

        if ($date === date('Y-m-d')) {
            $timeFilter = ' AND s.start_time > ?';
            $params[] = date('H:i:s');
        }

        $sql = "SELECT s.*
                FROM {$this->table} AS s
                LEFT JOIN (
                    SELECT t1.slot_id, t1.status
                    FROM appointments t1
                    INNER JOIN (
                        SELECT slot_id, MAX(id) AS max_id
                        FROM appointments
                        GROUP BY slot_id
                    ) t2 ON t1.id = t2.max_id
                ) AS a ON a.slot_id = s.id
                WHERE s.doctor_id = ?
                  AND s.date = ?
                  AND s.is_booked = 0
                  AND (a.status IS NULL OR a.status NOT IN ('completed','cancelled'))" . $timeFilter . "
                ORDER BY s.start_time ASC";

        $stmt = $this->db->raw($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
