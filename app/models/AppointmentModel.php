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
        if (empty($data['status'])) {
            $data['status'] = 'scheduled';
        }

        if (empty($data['payment_status'])) {
            $data['payment_status'] = 'pending';
        }

        if (empty($data['currency'])) {
            $data['currency'] = 'PHP';
        }

        foreach (['completed_at', 'cancelled_at', 'cancelled_by', 'cancellation_reason'] as $field) {
            if (!array_key_exists($field, $data)) {
                $data[$field] = null;
            }
        }

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
    public function mark_status($id, $status, array $meta = [])
    {
    $allowed = ['scheduled','confirmed','completed','cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $data = ['status' => $status];

        if ($status === 'cancelled') {
            $reason = trim($meta['cancellation_reason'] ?? '');
            if ($reason === '') {
                return false;
            }
        }

        $data = array_merge($data, $meta);

        $data['completed_at'] = ($status === 'completed') ? date('Y-m-d H:i:s') : null;

        if ($status === 'cancelled') {
            if (!array_key_exists('cancelled_at', $data)) {
                $data['cancelled_at'] = date('Y-m-d H:i:s');
            }
            if (!array_key_exists('cancelled_by', $data)) {
                $data['cancelled_by'] = null;
            }
        } else {
            $data['cancellation_reason'] = null;
            $data['cancelled_at'] = null;
            $data['cancelled_by'] = null;
        }

        return $this->db->table($this->table)->where('id', $id)->update($data);
    }

    public function attach_invoice(int $appointment_id, array $invoice_data, ?int $payment_id = null)
    {
        $update = [
            'invoice_id'    => $invoice_data['id'] ?? null,
            'invoice_url'   => $invoice_data['invoice_url'] ?? null,
            'checkout_url'  => $invoice_data['invoice_url'] ?? null,
            'payment_due_at'=> isset($invoice_data['expiry_date']) ? date('Y-m-d H:i:s', strtotime($invoice_data['expiry_date'])) : null,
            'amount'        => $invoice_data['amount'] ?? null,
            'currency'      => $invoice_data['currency'] ?? 'PHP',
            'payment_status'=> strtolower($invoice_data['status'] ?? 'pending'),
            'xendit_status' => strtoupper($invoice_data['status'] ?? 'PENDING'),
        ];

        if (!empty($payment_id)) {
            $update['payment_id'] = $payment_id;
        }

        return $this->db->table($this->table)
            ->where('id', $appointment_id)
            ->update($update);
    }

    public function find_by_invoice(string $invoice_id)
    {
        return $this->db->table($this->table)
            ->where('invoice_id', $invoice_id)
            ->get();
    }

    public function update_payment_state_by_invoice(string $invoice_id, array $data)
    {
        return $this->db->table($this->table)
            ->where('invoice_id', $invoice_id)
            ->update($data);
    }

    public function mark_paid(int $appointment_id, array $meta = [])
    {
        $data = [
            'payment_status' => 'paid',
            'xendit_status'  => strtoupper($meta['xendit_status'] ?? 'PAID'),
            'paid_at'        => $meta['paid_at'] ?? date('Y-m-d H:i:s'),
            'status'         => 'completed',
            'completed_at'   => date('Y-m-d H:i:s'),
        ];

        if (!empty($meta['payment_channel'])) {
            $data['cancellation_reason'] = null;
        }

        return $this->db->table($this->table)
            ->where('id', $appointment_id)
            ->update($data);
    }

    public function mark_failed(int $appointment_id, string $status = 'expired', array $meta = [])
    {
        $data = [
            'payment_status' => $status,
            'xendit_status'  => strtoupper($meta['xendit_status'] ?? $status),
        ];

        if (!empty($meta['payment_due_at'])) {
            $data['payment_due_at'] = $meta['payment_due_at'];
        }

        return $this->db->table($this->table)
            ->where('id', $appointment_id)
            ->update($data);
    }

    public function set_payment_reference(int $appointment_id, int $payment_id)
    {
        return $this->db->table($this->table)
            ->where('id', $appointment_id)
            ->update(['payment_id' => $payment_id]);
    }

    public function get_confirmed_ready_for_completion()
    {
        return $this->db->table($this->table . ' AS a')
            ->select('a.id, a.slot_id, a.payment_status, s.date AS slot_date, s.start_time, s.end_time')
            ->left_join('doctor_slots AS s', 's.id = a.slot_id')
            ->where('a.status', 'confirmed')
            ->get_all();
    }
}
