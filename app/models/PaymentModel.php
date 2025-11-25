<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class PaymentModel extends Model {
    protected $table = 'payments';
    protected $primary_key = 'id';

    public function create_payment(array $data)
    {
        $defaults = [
            'currency' => 'PHP',
            'status' => 'pending',
            'raw_payload' => null,
            'payer_email' => null,
            'payer_name' => null,
            'payment_channel' => null,
            'failure_reason' => null,
            'paid_at' => null,
            'expires_at' => null,
        ];

        return $this->insert(array_merge($defaults, $data));
    }

    public function find_by_invoice(string $invoice_id)
    {
        return $this->db->table($this->table)
            ->where('invoice_id', $invoice_id)
            ->get();
    }

    public function find_by_external(string $external_id)
    {
        return $this->db->table($this->table)
            ->where('external_id', $external_id)
            ->get();
    }

    public function find_by_appointment(int $appointment_id)
    {
        return $this->db->table($this->table)
            ->where('appointment_id', $appointment_id)
            ->order_by('id', 'DESC')
            ->get();
    }

    public function update_by_invoice(string $invoice_id, array $data)
    {
        return $this->db->table($this->table)
            ->where('invoice_id', $invoice_id)
            ->update($data);
    }

    public function update_payment(int $id, array $data)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update($data);
    }

    public function sum_paid_between(string $start, string $end): float
    {
        $row = $this->db->table($this->table)
            ->select('COALESCE(SUM(amount),0) AS total_amount')
            ->where('status', 'paid')
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<=', $end)
            ->get();

        return (float)($row['total_amount'] ?? 0);
    }

    public function sum_paid_between_for_doctor(int $doctorId, string $start, string $end): float
    {
        $row = $this->db->table($this->table)
            ->select('COALESCE(SUM(amount),0) AS total_amount')
            ->where('status', 'paid')
            ->where('doctor_id', $doctorId)
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<=', $end)
            ->get();

        return (float)($row['total_amount'] ?? 0);
    }

    public function get_pending_payments(int $limit = 5): array
    {
        return $this->db->table($this->table . ' AS pay')
            ->select('pay.*, pat.name AS patient_name, doc.name AS doctor_name, slots.date AS slot_date, slots.start_time')
            ->left_join('appointments AS ap', 'ap.id = pay.appointment_id')
            ->left_join('patients AS pat', 'pat.id = pay.patient_id')
            ->left_join('doctors AS doc', 'doc.id = pay.doctor_id')
            ->left_join('doctor_slots AS slots', 'slots.id = ap.slot_id')
            ->where('pay.status', 'pending')
            ->order_by('pay.created_at', 'ASC')
            ->limit($limit)
            ->get_all();
    }

    public function get_pending_summary(): array
    {
        $row = $this->db->table($this->table)
            ->select('COUNT(*) AS total_count, COALESCE(SUM(amount),0) AS total_amount')
            ->where('status', 'pending')
            ->get();

        return [
            'count' => (int)($row['total_count'] ?? 0),
            'amount' => (float)($row['total_amount'] ?? 0),
        ];
    }

    public function all_with_relations(?int $doctorId = null, array $filters = [])
    {
        $builder = $this->db->table($this->table . ' AS pay')
            ->select('pay.*, ap.status AS appointment_status, ap.payment_status AS appointment_payment_status, ap.invoice_url, ap.invoice_id, ap.slot_id, ap.consultation_fee, ap.amount AS appointment_amount, pat.name AS patient_name, doc.name AS doctor_name, slots.date AS slot_date, slots.start_time, slots.end_time')
            ->left_join('appointments AS ap', 'ap.id = pay.appointment_id')
            ->left_join('patients AS pat', 'pat.id = pay.patient_id')
            ->left_join('doctors AS doc', 'doc.id = pay.doctor_id')
            ->left_join('doctor_slots AS slots', 'slots.id = ap.slot_id')
            ->order_by('pay.id', 'DESC');

        if (!is_null($doctorId)) {
            $builder->where('pay.doctor_id', $doctorId);
        }

        $status = $filters['status'] ?? null;
        if (!empty($status)) {
            $builder->where('pay.status', $status);
        }

        $dateFrom = $filters['date_from'] ?? null;
        if (!empty($dateFrom)) {
            $builder->where('pay.created_at', '>=', $dateFrom . ' 00:00:00');
        }

        $dateTo = $filters['date_to'] ?? null;
        if (!empty($dateTo)) {
            $builder->where('pay.created_at', '<=', $dateTo . ' 23:59:59');
        }

        return $builder->get_all();
    }
}
