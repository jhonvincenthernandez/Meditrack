<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AppointmentController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->call->model('AppointmentModel');
        $this->call->model('DoctorModel');
        $this->call->model('PatientModel');
        $this->call->model('ScheduleModel');
        $this->call->model('PaymentModel');
        $this->call->library('lauth');

        $this->finalize_confirmed_appointments();
    }

    public function index() {
        $this->lauth->require_login();
        $records = $this->AppointmentModel->all_with_details();

        $grouped = [
            'scheduled' => [],
            'confirmed' => [],
            'completed' => [],
            'cancelled' => [],
        ];

        foreach ($records as $entry) {
            $status = strtolower($entry['status'] ?? 'scheduled');
            if (!array_key_exists($status, $grouped)) {
                $status = 'scheduled';
            }
            $grouped[$status][] = $entry;
        }

        $data['appointments'] = $grouped;
        $data['appointment_counts'] = [
            'scheduled' => count($grouped['scheduled']),
            'confirmed' => count($grouped['confirmed']),
            'completed' => count($grouped['completed']),
            'cancelled' => count($grouped['cancelled']),
        ];

        $this->call->view('/appointments/index', $data);
    }

    public function add_form() {
        $this->lauth->require_login();
        $data['patients'] = $this->PatientModel->all();
        $data['doctors'] = $this->DoctorModel->all_active_doctors();
        $this->call->view('/appointments/add', $data);
    }

    // JSON: distinct future dates with open slots for the doctor
    public function getDoctorDates($doctor_id) {
        header('Content-Type: application/json');
        $dates = $this->ScheduleModel->get_doctor_dates($doctor_id);
        echo json_encode($dates ?: []);
    }

    // JSON: available slots for a doctor on a selected date
    public function getAvailableSlots($doctor_id, $date) {
        header('Content-Type: application/json');
        $slots = $this->ScheduleModel->get_available_slots($doctor_id, $date);
        echo json_encode($slots ?: []);
    }

    public function save() {
        $this->lauth->require_login();
        $this->persist_booking($this->collect_booking_payload(), null, 'admin');
    }

    // Doctor self add view
    public function doc_add() {
        $this->lauth->require_login();
        $data['patients'] = $this->PatientModel->all();
        // Identify current doctor linked to logged in user
        $current_doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id'] ?? 0);
        $data['doctor_id'] = $current_doctor['id'] ?? null;
        $this->call->view('/appointments/doc_add', $data);
    }

    // Dedicated save for Doctor
    public function save_doc_add() {
        $this->lauth->require_login();
        $this->lauth->require_role('doctor');
        $current_doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id'] ?? 0);
        if (!$current_doctor) {
            exit('Doctor profile not linked to account.');
        }

        $payload = $this->collect_booking_payload();
        $this->persist_booking($payload, (int)$current_doctor['id'], 'doctor');
    }

    public function staff_add() {
        $this->lauth->require_login();
        $data['patients'] = $this->PatientModel->all();
        $data['doctors'] = $this->DoctorModel->all_active_doctors();
        $this->call->view('/appointments/staff_add', $data);
    }

    // Dedicated save for Staff
    public function save_staff_add() {
        $this->lauth->require_login();
        $this->lauth->require_role('staff');
        $this->persist_booking($this->collect_booking_payload(), null, 'staff');
    }

    // Dedicated save for Admin (using same payload as staff: select doctor, date, slot)
    public function save_admin() {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');
        $this->persist_booking($this->collect_booking_payload(), null, 'admin');
    }

    public function edit_form($id) {
        $this->lauth->require_login();
        $data['appointment'] = $this->AppointmentModel->get_with_details($id);
        if (!$data['appointment']) { exit('Appointment not found'); }
        $data['patients'] = $this->PatientModel->all();
        $data['doctors'] = $this->DoctorModel->all_active_doctors();
        // For edit, we will lazy-load dates/slots via JS; include current slot for default display
        $this->call->view('/appointments/edit', $data);
    }

    public function update($id) {
        $this->lauth->require_login();
        $appointment = $this->AppointmentModel->find($id);
        if (!$appointment) { exit('Appointment not found'); }

        $patient_id = $_POST['patient_id'] ?? $appointment['patient_id'];
        $doctor_id  = $_POST['doctor_id'] ?? $appointment['doctor_id'];
        $slot_id    = $_POST['slot_id'] ?? $appointment['slot_id'];
        $notes      = $_POST['notes'] ?? $appointment['notes'];

        // Validate slot
        $slot = $this->ScheduleModel->get_slot($slot_id);
        if (!$slot) {
            exit('Invalid slot.');
        }
        if ((int)$slot['doctor_id'] !== (int)$doctor_id) {
            exit('Selected slot does not belong to the chosen doctor.');
        }
        $changing_slot = ((int)$appointment['slot_id'] !== (int)$slot_id);
        if ($changing_slot && ((int)($slot['is_booked'] ?? 0) === 1 || $this->AppointmentModel->is_slot_booked($slot_id))) {
            exit('This slot is already booked.');
        }

        $this->db->transaction();
        try {
            // If changing slot: free previous, book new
            if ($changing_slot) {
                if (!empty($appointment['slot_id'])) {
                    $this->ScheduleModel->set_booked($appointment['slot_id'], 0);
                }
                $this->ScheduleModel->set_booked($slot_id, 1);
            }
            $this->AppointmentModel->update($id, [
                'patient_id' => $patient_id,
                'doctor_id'  => $doctor_id,
                'slot_id'    => $slot_id,
                'notes'      => $notes,
            ]);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->roll_back();
            exit('Failed to update appointment.');
        }
        redirect('/appointments');
    }

    public function delete($id) {
        $this->lauth->require_login();
        // Free the slot then delete
        $appointment = $this->AppointmentModel->find($id);
        if ($appointment && !empty($appointment['slot_id'])) {
            $this->ScheduleModel->set_booked($appointment['slot_id'], 0);
        }
        $this->AppointmentModel->delete_appointment($id);
        redirect('/appointments');
    }

    /**
     * Confirm appointment (after payment)
     */
    public function complete($id)
    {
        $this->lauth->require_login();
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin','doctor','staff'], true)) {
            exit('Access denied');
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $appointment = $this->AppointmentModel->get_with_details($id);
        if (!$appointment) { exit('Appointment not found'); }

        $curr = strtolower($appointment['status'] ?? 'scheduled');
        
        // Scheduled → Confirmed (after payment)
        if ($curr === 'scheduled') {
            $payment = $this->PaymentModel->find_by_appointment($appointment['id']);
            $paymentStatus = strtolower($appointment['payment_status'] ?? 'pending');

            if ($method === 'POST') {
                $this->handle_manual_completion($appointment, $payment);
                $this->redirect_after_action($role);
                return;
            }

            if ($paymentStatus !== 'paid') {
                exit('Payment is still pending. Use the manual completion form to override.');
            }

            $ok = $this->AppointmentModel->mark_status($id, 'confirmed');
            if (!$ok) { exit('Unable to confirm appointment'); }

            $this->redirect_after_action($role);
            return;
        }
        
        // Confirmed → Completed (after consultation)
        if ($curr === 'confirmed') {
            $ok = $this->AppointmentModel->mark_status($id, 'completed');
            if (!$ok) { exit('Unable to mark as completed'); }

            $this->redirect_after_action($role);
            return;
        }
        
        exit('Invalid action: appointment is already '.htmlspecialchars($curr));
    }

    /**
     * Cancel appointment (frees slot)
     */
    public function cancel($id)
    {
        $this->lauth->require_login();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            exit('Invalid request method.');
        }

        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin','doctor','staff'], true)) {
            exit('Access denied');
        }

        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            exit('Cancellation reason is required.');
        }

        $appt = $this->AppointmentModel->find($id);
        if (!$appt) { exit('Appointment not found'); }

        // Guard: can only cancel a scheduled appointment
        $curr = $appt['status'] ?? 'scheduled';
        if ($curr !== 'scheduled') {
            exit('Invalid action: only scheduled appointments can be cancelled. Current status: '.htmlspecialchars($curr));
        }

        $this->cancel_pending_payment($appt);

        // Free slot if present
        if (!empty($appt['slot_id'])) {
            $this->ScheduleModel->set_booked($appt['slot_id'], 0);
        }

        $meta = [
            'cancellation_reason' => $reason,
            'cancelled_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($_SESSION['name'])) {
            $meta['cancelled_by'] = $_SESSION['name'];
        }

        $ok = $this->AppointmentModel->mark_status($id, 'cancelled', $meta);
        if (!$ok) { exit('Unable to cancel appointment'); }

        $this->redirect_after_action($role);
    }

    public function checkout($id)
    {
        $this->lauth->require_login();
        $role = $_SESSION['role'] ?? 'admin';
        if (!in_array($role, ['admin','doctor','staff'], true)) {
            $role = 'admin';
        }
        $this->render_checkout($id, $role);
    }

    public function checkout_admin($id)
    {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');
        $this->render_checkout($id, 'admin');
    }

    public function checkout_doctor($id)
    {
        $this->lauth->require_login();
        $this->lauth->require_role('doctor');
        $this->render_checkout($id, 'doctor');
    }

    public function checkout_staff($id)
    {
        $this->lauth->require_login();
        $this->lauth->require_role('staff');
        $this->render_checkout($id, 'staff');
    }

    public function payment_success()
    {
        $external = $_GET['external_id'] ?? '';
        $payment = $external ? $this->PaymentModel->find_by_external($external) : null;
        $invoice = $payment ? $this->sync_invoice_status($payment) : null;
        if ($invoice && $external) {
            $payment = $this->PaymentModel->find_by_external($external);
        }
        $role = $_SESSION['role'] ?? 'admin';
        if (!in_array($role, ['admin','doctor','staff'], true)) {
            $role = 'admin';
        }
        $data = [
            'status' => 'success',
            'external_id' => $external,
            'payment' => $payment,
            'message' => 'Payment confirmed. If the dashboard hasn\'t refreshed automatically, you can close this tab safely.',
            'invoice' => $invoice,
            'return_url' => $this->resolve_checkout_return_url($role),
            'return_label' => $this->resolve_checkout_return_label($role),
        ];
        $this->call->view('/appointments/payment_status', $data);
    }

    public function payment_failed()
    {
        $external = $_GET['external_id'] ?? '';
        $payment = $external ? $this->PaymentModel->find_by_external($external) : null;
        $invoice = $payment ? $this->sync_invoice_status($payment) : null;
        if ($invoice && $external) {
            $payment = $this->PaymentModel->find_by_external($external);
        }
        $role = $_SESSION['role'] ?? 'admin';
        if (!in_array($role, ['admin','doctor','staff'], true)) {
            $role = 'admin';
        }
        $data = [
            'status' => 'failed',
            'external_id' => $external,
            'payment' => $payment,
            'message' => 'Payment was not completed. You may reopen the invoice from the appointments table to retry.',
            'invoice' => $invoice,
            'return_url' => $this->resolve_checkout_return_url($role),
            'return_label' => $this->resolve_checkout_return_label($role),
        ];
        $this->call->view('/appointments/payment_status', $data);
    }

    private function sync_invoice_status(array $payment): ?array
    {
        if (empty($payment['invoice_id'])) {
            return null;
        }

        try {
            $invoice = xendit_get_invoice($payment['invoice_id']);
        } catch (\Throwable $e) {
            return null;
        }

        $status = strtoupper($invoice['status'] ?? '');
        $paidAt = !empty($invoice['paid_at']) ? date('Y-m-d H:i:s', strtotime($invoice['paid_at'])) : null;
        $update = [
            'status' => strtolower($status ?: 'pending'),
            'payment_channel' => $invoice['payment_channel'] ?? $payment['payment_channel'] ?? null,
            'paid_at' => $paidAt,
            'raw_payload' => json_encode($invoice),
        ];
        $this->PaymentModel->update_by_invoice($invoice['id'], $update);

        $successStatuses = ['PAID','SETTLED'];
        $failedStatuses = ['EXPIRED','VOIDED','FAILED','CANCELED'];

        if (in_array($status, $successStatuses, true)) {
            $appointment = $this->AppointmentModel->find_by_invoice($invoice['id']);
            $nextState = $this->resolve_paid_status($appointment);
            $payload = [
                'payment_status' => 'paid',
                'xendit_status' => $status,
                'paid_at' => $paidAt ?? date('Y-m-d H:i:s'),
                'status' => $nextState['status'],
            ];
            if ($nextState['status'] === 'completed') {
                $payload['completed_at'] = $nextState['completed_at'] ?? date('Y-m-d H:i:s');
            } else {
                $payload['completed_at'] = null;
            }
            $this->AppointmentModel->update_payment_state_by_invoice($invoice['id'], $payload);
        } elseif (in_array($status, $failedStatuses, true)) {
            $this->AppointmentModel->update_payment_state_by_invoice($invoice['id'], [
                'payment_status' => strtolower($status),
                'xendit_status' => $status,
                'status' => 'cancelled',
                'cancellation_reason' => 'Payment ' . strtolower($status),
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $invoice;
    }

    private function collect_booking_payload(): array
    {
        return [
            'patient_id' => (int)($_POST['patient_id'] ?? 0),
            'doctor_id'  => (int)($_POST['doctor_id'] ?? 0),
            'slot_id'    => (int)($_POST['slot_id'] ?? 0),
            'notes'      => trim($_POST['notes'] ?? ''),
            'consultation_fee' => $_POST['consultation_fee'] ?? null,
        ];
    }

    private function persist_booking(array $payload, ?int $locked_doctor_id = null, string $checkoutRole = 'admin'): void
    {
        try {
            $result = $this->create_booking_with_invoice($payload, $locked_doctor_id);
        } catch (Throwable $e) {
            exit('Failed to save appointment: ' . $e->getMessage());
        }

        redirect($this->build_checkout_route($checkoutRole, $result['appointment_id']));
    }

    private function build_checkout_route(string $role, int $appointmentId): string
    {
        switch ($role) {
            case 'doctor':
                return '/appointments/doctor/checkout/' . $appointmentId;
            case 'staff':
                return '/appointments/staff/checkout/' . $appointmentId;
            default:
                return '/appointments/admin/checkout/' . $appointmentId;
        }
    }

    private function render_checkout(int $id, string $role): void
    {
        $appointment = $this->AppointmentModel->get_with_details($id);
        if (!$appointment) {
            exit('Appointment not found');
        }

        $payment = $this->PaymentModel->find_by_appointment($appointment['id']);
        if ($payment && $this->should_sync_invoice($payment)) {
            $invoice = $this->sync_invoice_status($payment);
            if ($invoice) {
                $appointment = $this->AppointmentModel->get_with_details($id);
                $payment = $this->PaymentModel->find_by_appointment($appointment['id']);
            }
        }

        $reissuedInvoice = null;
        if ($this->should_reissue_invoice($appointment, $payment)) {
            try {
                $reissuedInvoice = $this->reissue_invoice($appointment, $payment);
                $appointment = $this->AppointmentModel->get_with_details($id);
                $payment = $this->PaymentModel->find_by_appointment($appointment['id']);
            } catch (Throwable $e) {
                exit('Unable to regenerate invoice: ' . $e->getMessage());
            }
        }

        $data = [
            'appointment' => $appointment,
            'payment' => $payment,
            'reissued' => (bool) $reissuedInvoice,
            'return_url' => $this->resolve_checkout_return_url($role),
            'return_label' => $this->resolve_checkout_return_label($role),
            'checkout_role' => $role,
        ];

        $this->call->view('/appointments/checkout', $data);
    }

    private function create_booking_with_invoice(array $payload, ?int $locked_doctor_id = null): array
    {
        foreach (['patient_id','doctor_id','slot_id'] as $field) {
            if (empty($payload[$field])) {
                throw new RuntimeException('Missing required fields.');
            }
        }

        if ($locked_doctor_id !== null && (int)$locked_doctor_id !== (int)$payload['doctor_id']) {
            throw new RuntimeException('Unauthorized doctor selection.');
        }

        $patient = $this->get_patient($payload['patient_id']);
        $doctor  = $this->get_doctor($payload['doctor_id']);
        $slot    = $this->ScheduleModel->get_slot($payload['slot_id']);

        if (!$patient) { throw new RuntimeException('Patient not found.'); }
        if (!$doctor) { throw new RuntimeException('Doctor not found.'); }
        if (!$slot)   { throw new RuntimeException('Invalid slot.'); }
        if ((int)$slot['doctor_id'] !== (int)$payload['doctor_id']) {
            throw new RuntimeException('Selected slot does not belong to the chosen doctor.');
        }
        if ((int)($slot['is_booked'] ?? 0) === 1 || $this->AppointmentModel->is_slot_booked($payload['slot_id'])) {
            throw new RuntimeException('This slot is already booked.');
        }

        $amount   = $this->resolve_fee($payload['consultation_fee']);
        $currency = 'PHP';
        $notes    = $payload['notes'] ?? '';

        $this->db->transaction();
        try {
            $appointment_id = $this->AppointmentModel->insert_appointment([
                'patient_id' => $payload['patient_id'],
                'doctor_id'  => $payload['doctor_id'],
                'slot_id'    => $payload['slot_id'],
                'notes'      => $notes,
                'consultation_fee' => $amount,
                'amount' => $amount,
                'currency' => $currency,
                'payment_status' => 'pending',
            ]);
            $this->ScheduleModel->set_booked($payload['slot_id'], 1);

            $external_id = $this->generate_external_id($appointment_id);
            $invoice_payload = $this->build_invoice_payload($external_id, $amount, $currency, $patient, $doctor, $slot, $notes, $appointment_id);
            $invoice_response = xendit_create_invoice($invoice_payload);

            $payment_id = $this->PaymentModel->create_payment([
                'appointment_id' => $appointment_id,
                'patient_id' => $payload['patient_id'],
                'doctor_id' => $payload['doctor_id'],
                'external_id' => $external_id,
                'invoice_id' => $invoice_response['id'] ?? null,
                'invoice_url' => $invoice_response['invoice_url'] ?? null,
                'amount' => $amount,
                'currency' => $currency,
                'status' => strtolower($invoice_response['status'] ?? 'pending'),
                'payer_name' => $patient['name'] ?? null,
                'raw_payload' => json_encode($invoice_response),
                'expires_at' => isset($invoice_response['expiry_date']) ? date('Y-m-d H:i:s', strtotime($invoice_response['expiry_date'])) : null,
            ]);

            $this->AppointmentModel->attach_invoice($appointment_id, $invoice_response, $payment_id);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->roll_back();
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return [
            'appointment_id' => $appointment_id,
            'invoice_id' => $invoice_response['id'] ?? null,
            'external_id' => $external_id,
        ];
    }

    private function build_invoice_payload(string $external_id, float $amount, string $currency, array $patient, array $doctor, array $slot, string $notes, int $appointment_id): array
    {
        $settings = config_item('xendit') ?: [];
        $description = sprintf(
            'Consultation with Dr. %s on %s %s-%s',
            $doctor['name'] ?? ('Doctor #' . $doctor['id']),
            date('M d, Y', strtotime($slot['date'])),
            substr($slot['start_time'], 0, 5),
            substr($slot['end_time'], 0, 5)
        );

        $successUrl = $settings['success_redirect_url'] ?? (rtrim(base_url(), '/') . '/appointments/payment-success');
        $failureUrl = $settings['failure_redirect_url'] ?? (rtrim(base_url(), '/') . '/appointments/payment-failed');

        return [
            'external_id' => $external_id,
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'invoice_duration' => (int)(($settings['auto_expiry_minutes'] ?? 120) * 60),
            'success_redirect_url' => $this->decorate_redirect_url($successUrl, $external_id),
            'failure_redirect_url' => $this->decorate_redirect_url($failureUrl, $external_id),
            'customer' => [
                'given_names' => $patient['name'] ?? 'Patient',
                'mobile_number' => format_msisdn($patient['contact'] ?? ''),
            ],
            'metadata' => [
                'appointment_id' => $appointment_id,
                'patient_id' => $patient['id'] ?? null,
                'doctor_id' => $doctor['id'] ?? null,
                'slot_id' => $slot['id'] ?? null,
                'notes' => $notes,
                'external_id' => $external_id,
            ],
            'reminder_time' => 1,
            'reminder_time_unit' => 'hours',
        ];
    }

    private function generate_external_id(int $appointment_id): string
    {
        $random = strtoupper(bin2hex(random_bytes(3)));
        return sprintf('MTK-%d-%s', $appointment_id, $random);
    }

    private function resolve_checkout_return_url(string $role): string
    {
        $base = rtrim(base_url(), '/');
        switch ($role) {
            case 'doctor':
                return $base . '/dashboard_doctor';
            case 'staff':
                return $base . '/dashboard_staff';
            default:
                return $base . '/appointments';
        }
    }

    private function resolve_checkout_return_label(string $role): string
    {
        switch ($role) {
            case 'doctor':
                return 'Back to doctor dashboard';
            case 'staff':
                return 'Back to staff dashboard';
            default:
                return 'Back to appointments';
        }
    }

    private function should_sync_invoice(?array $payment): bool
    {
        if (!$payment || empty($payment['invoice_id'])) {
            return false;
        }

        $status = strtolower($payment['status'] ?? 'pending');
        return !in_array($status, ['paid', 'settled'], true);
    }

    private function should_reissue_invoice(array $appointment, ?array $payment): bool
    {
        $appointmentStatus = strtolower($appointment['status'] ?? 'scheduled');
        if ($appointmentStatus !== 'scheduled') {
            return false;
        }

        $paymentStatus = strtolower($appointment['payment_status'] ?? 'pending');
        if (in_array($paymentStatus, ['paid', 'confirmed', 'completed'], true)) {
            return false;
        }

        $expiredStates = ['expired', 'failed', 'cancelled', 'canceled', 'voided'];

        if (empty($appointment['invoice_id']) || empty($appointment['invoice_url'])) {
            return true;
        }

        if (in_array($paymentStatus, $expiredStates, true)) {
            return true;
        }

        $xenditStatus = strtolower($appointment['xendit_status'] ?? '');
        if ($xenditStatus && in_array($xenditStatus, $expiredStates, true)) {
            return true;
        }

        if (!empty($appointment['payment_due_at']) && strtotime($appointment['payment_due_at']) < time()) {
            return true;
        }

        if ($payment) {
            $paymentState = strtolower($payment['status'] ?? 'pending');
            if (in_array($paymentState, $expiredStates, true)) {
                return true;
            }

            $expiresAt = $payment['expires_at'] ?? null;
            if ($expiresAt && strtotime($expiresAt) < time()) {
                return true;
            }
        }

        return false;
    }

    private function reissue_invoice(array $appointment, ?array $payment = null): array
    {
        $patient = $this->get_patient((int) $appointment['patient_id']);
        $doctor = $this->get_doctor((int) $appointment['doctor_id']);
        $slot = !empty($appointment['slot_id']) ? $this->ScheduleModel->get_slot((int) $appointment['slot_id']) : null;

        if (!$patient) {
            throw new RuntimeException('Patient not found.');
        }

        if (!$doctor) {
            throw new RuntimeException('Doctor not found.');
        }

        if (!$slot) {
            throw new RuntimeException('Schedule slot not found.');
        }

        $amount = $this->resolve_existing_fee($appointment);
        $currency = $appointment['currency'] ?? 'PHP';
        $notes = $appointment['notes'] ?? '';
        $appointmentId = (int) $appointment['id'];

        $externalId = $this->generate_external_id($appointmentId);
        $invoicePayload = $this->build_invoice_payload($externalId, $amount, $currency, $patient, $doctor, $slot, $notes, $appointmentId);

        $this->db->transaction();
        try {
            $invoiceResponse = xendit_create_invoice($invoicePayload);

            $paymentData = [
                'appointment_id' => $appointmentId,
                'patient_id' => $appointment['patient_id'],
                'doctor_id' => $appointment['doctor_id'],
                'external_id' => $externalId,
                'invoice_id' => $invoiceResponse['id'] ?? null,
                'invoice_url' => $invoiceResponse['invoice_url'] ?? null,
                'amount' => $amount,
                'currency' => $currency,
                'status' => strtolower($invoiceResponse['status'] ?? 'pending'),
                'payer_name' => $patient['name'] ?? null,
                'payer_email' => $patient['email'] ?? null,
                'raw_payload' => json_encode($invoiceResponse),
                'expires_at' => isset($invoiceResponse['expiry_date']) ? date('Y-m-d H:i:s', strtotime($invoiceResponse['expiry_date'])) : null,
            ];

            if ($payment) {
                $this->PaymentModel->update_payment($payment['id'], $paymentData);
                $paymentId = $payment['id'];
            } else {
                $paymentId = $this->PaymentModel->create_payment($paymentData);
            }

            $this->AppointmentModel->attach_invoice($appointmentId, $invoiceResponse, $paymentId);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->roll_back();
            throw $e;
        }

        return $invoiceResponse;
    }

    private function resolve_existing_fee(array $appointment): float
    {
        $fee = $appointment['consultation_fee'] ?? $appointment['amount'] ?? null;
        return $this->resolve_fee($fee);
    }

    private function resolve_fee($raw): float
    {
        $default = (float)(config_item('default_consultation_fee') ?? 1500);
        $fee = is_numeric($raw) ? (float)$raw : 0;
        if ($fee <= 0) {
            $fee = $default;
        }
        return round($fee, 2);
    }

    private function get_patient(int $id): ?array
    {
        return $this->db->table('patients')->where('id', $id)->get();
    }

    private function get_doctor(int $id): ?array
    {
        return $this->db->table('doctors')->where('id', $id)->get();
    }

    private function decorate_redirect_url(string $url, string $external_id): string
    {
        if (strpos($url, 'external_id=') !== false) {
            return $url;
        }

        $separator = (parse_url($url, PHP_URL_QUERY) === null) ? '?' : '&';
        return $url . $separator . 'external_id=' . urlencode($external_id);
    }

    private function finalize_confirmed_appointments(): void
    {
        $records = $this->AppointmentModel->get_confirmed_ready_for_completion();
        if (empty($records)) {
            return;
        }

        $now = time();
        foreach ($records as $row) {
            if (strtolower($row['payment_status'] ?? '') !== 'paid') {
                continue;
            }
            $end = $this->compute_slot_timestamp($row['slot_date'] ?? null, $row['end_time'] ?? null);
            if ($end && $end <= $now) {
                $this->AppointmentModel->mark_status((int)$row['id'], 'completed', [
                    'payment_status' => $row['payment_status'] ?? 'paid',
                ]);
            }
        }
    }

    private function compute_slot_timestamp(?string $date, ?string $time): ?int
    {
        if (empty($date) || empty($time)) {
            return null;
        }

        $timestamp = strtotime($date . ' ' . $time);
        return $timestamp ?: null;
    }

    private function resolve_paid_status(?array $appointment): array
    {
        if (!$appointment) {
            return ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')];
        }

        $slot = !empty($appointment['slot_id']) ? $this->ScheduleModel->get_slot((int)$appointment['slot_id']) : null;
        $end = $slot ? $this->compute_slot_timestamp($slot['date'] ?? null, $slot['end_time'] ?? null) : null;
        if ($end && $end <= time()) {
            return ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')];
        }

        return ['status' => 'confirmed', 'completed_at' => null];
    }

    private function handle_manual_completion(array $appointment, ?array $payment = null): void
    {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin','staff'], true)) {
            exit('Manual completion override is only available to admin and staff accounts.');
        }

        $reason = trim($_POST['override_reason'] ?? '');
        if ($reason === '') {
            exit('Override reason is required.');
        }

        $allowedMethods = ['cash','insurance','bank_transfer','check','writeoff','other'];
        $method = strtolower($_POST['override_method'] ?? '');
        if (!in_array($method, $allowedMethods, true)) {
            exit('Invalid payment method.');
        }

        $methodLabel = $method;
        if ($method === 'other') {
            $custom = trim($_POST['override_method_other'] ?? '');
            if ($custom === '') {
                exit('Please specify the other payment method.');
            }
            $methodLabel = $custom;
        }

        $amountInput = $_POST['override_amount'] ?? null;
        $amount = is_numeric($amountInput) ? (float)$amountInput : null;
        if (empty($amount) || $amount <= 0) {
            $amount = $this->resolve_existing_fee($appointment);
        }

        if (empty($_POST['override_confirm'])) {
            exit('You must acknowledge the manual completion confirmation.');
        }

        $now = date('Y-m-d H:i:s');
        $manualMeta = [
            'reason' => $reason,
            'method' => $methodLabel,
            'amount' => $amount,
            'approved_by' => $_SESSION['name'] ?? ($_SESSION['email'] ?? 'System'),
            'role' => $role,
            'approved_at' => $now,
        ];

        if ($payment) {
            $this->expire_invoice_if_needed($payment);
            $raw = $payment['raw_payload'] ?? null;
            $decoded = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : []);
            if (!is_array($decoded)) {
                $decoded = [];
            }
            $decoded['manual_override'] = $manualMeta;

            $update = [
                'status' => 'paid',
                'payment_channel' => $methodLabel,
                'failure_reason' => 'Manual override completion',
                'paid_at' => $now,
                'amount' => $amount,
                'raw_payload' => json_encode($decoded),
            ];
            $this->PaymentModel->update_payment((int)$payment['id'], $update);
        } else {
            $externalId = $this->generate_external_id((int)$appointment['id']);
            $payload = [
                'appointment_id' => (int)$appointment['id'],
                'patient_id' => (int)$appointment['patient_id'],
                'doctor_id' => (int)$appointment['doctor_id'],
                'external_id' => $externalId,
                'amount' => $amount,
                'currency' => $appointment['currency'] ?? 'PHP',
                'status' => 'paid',
                'payer_name' => $appointment['patient_name'] ?? null,
                'payment_channel' => $methodLabel,
                'failure_reason' => 'Manual override completion',
                'paid_at' => $now,
                'raw_payload' => json_encode(['manual_override' => $manualMeta]),
            ];
            $paymentId = $this->PaymentModel->create_payment($payload);
            $this->AppointmentModel->set_payment_reference((int)$appointment['id'], (int)$paymentId);
        }

        $meta = [
            'payment_status' => 'paid',
            'xendit_status' => 'MANUAL_OVERRIDE',
            'paid_at' => $now,
        ];

        $ok = $this->AppointmentModel->mark_status((int)$appointment['id'], 'confirmed', $meta);
        if (!$ok) {
            exit('Unable to confirm appointment with manual override.');
        }
    }

    private function expire_invoice_if_needed(?array $payment): void
    {
        if (!$payment || empty($payment['invoice_id'])) {
            return;
        }

        $state = strtolower($payment['status'] ?? '');
        $terminalStates = ['paid','settled','expired','voided','cancelled','canceled'];
        if (in_array($state, $terminalStates, true)) {
            return;
        }

        try {
            xendit_expire_invoice($payment['invoice_id']);
        } catch (Throwable $e) {
            // Silently ignore to avoid blocking manual overrides
        }
    }

    private function cancel_pending_payment(array $appointment): void
    {
        $payment = $this->PaymentModel->find_by_appointment((int)($appointment['id'] ?? 0));
        if (!$payment) {
            return;
        }

        $pendingStates = ['pending', 'unpaid', 'requires_action', 'in_progress', 'waiting_payment', 'qris_pending'];
        $paymentState = strtolower($payment['status'] ?? 'pending');
        if (!in_array($paymentState, $pendingStates, true)) {
            return;
        }

        $finalStatus = 'cancelled';
        $failureReason = 'Appointment cancelled' . (!empty($_SESSION['name']) ? ' by ' . $_SESSION['name'] : '');
        $appointmentUpdate = function (array $data) use ($payment, $appointment) {
            if (!empty($payment['invoice_id'])) {
                $this->AppointmentModel->update_payment_state_by_invoice($payment['invoice_id'], $data);
            } else {
                $this->AppointmentModel->update((int)$appointment['id'], $data);
            }
        };

        if (!empty($payment['invoice_id'])) {
            try {
                $invoice = xendit_expire_invoice($payment['invoice_id']);
                $finalStatus = strtolower($invoice['status'] ?? $finalStatus);
                $this->PaymentModel->update_payment((int)$payment['id'], [
                    'status' => $finalStatus,
                    'raw_payload' => json_encode($invoice),
                    'payment_channel' => $invoice['payment_channel'] ?? $payment['payment_channel'],
                    'failure_reason' => $failureReason,
                    'expires_at' => isset($invoice['expiry_date']) ? date('Y-m-d H:i:s', strtotime($invoice['expiry_date'])) : $payment['expires_at'],
                ]);
            } catch (Throwable $e) {
                $this->PaymentModel->update_payment((int)$payment['id'], [
                    'status' => $finalStatus,
                    'failure_reason' => $failureReason . ' (invoice void failed: ' . $e->getMessage() . ')',
                ]);
            }
        } else {
            $this->PaymentModel->update_payment((int)$payment['id'], [
                'status' => $finalStatus,
                'failure_reason' => $failureReason,
            ]);
        }

        $appointmentUpdate([
            'payment_status' => $finalStatus,
            'xendit_status' => strtoupper($finalStatus),
        ]);
    }

    private function redirect_after_action(string $role): void
    {
        switch ($role) {
            case 'doctor':
                redirect('/dashboard_doctor');
                return;
            case 'staff':
                redirect('/dashboard_staff');
                return;
            default:
                redirect('/appointments');
        }
    }
}
