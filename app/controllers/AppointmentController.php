<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AppointmentController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->call->model('AppointmentModel');
        $this->call->model('DoctorModel');
        $this->call->model('PatientModel');
        $this->call->model('ScheduleModel');
        $this->call->library('lauth');
    }

    public function index() {
        $this->lauth->require_login();
        $data['appointments'] = $this->AppointmentModel->all_with_details();
        $this->call->view('/appointments/index', $data);
    }

    public function add_form() {
        $this->lauth->require_login();
        $data['patients'] = $this->PatientModel->all();
        $data['doctors'] = $this->DoctorModel->all();
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
        $patient_id = $_POST['patient_id'] ?? null;
        $doctor_id  = $_POST['doctor_id'] ?? null;
        $slot_id    = $_POST['slot_id'] ?? null;
        $notes      = $_POST['notes'] ?? '';

        // Basic validation
        if (empty($patient_id) || empty($doctor_id) || empty($slot_id)) {
            exit('Missing required fields.');
        }

        // Validate slot exists and belongs to the doctor and is not booked
        $slot = $this->ScheduleModel->get_slot($slot_id);
        if (!$slot) {
            exit('Invalid slot.');
        }
        if ((int)$slot['doctor_id'] !== (int)$doctor_id) {
            exit('Selected slot does not belong to the chosen doctor.');
        }
        if ((int)($slot['is_booked'] ?? 0) === 1 || $this->AppointmentModel->is_slot_booked($slot_id)) {
            exit('This slot is already booked and cancelled.');
        }

        // Create appointment and mark slot as booked
        $this->db->transaction();
        try {
            $appointment_id = $this->AppointmentModel->insert_appointment([
                'patient_id' => $patient_id,
                'doctor_id'  => $doctor_id,
                'slot_id'    => $slot_id,
                'notes'      => $notes,
            ]);
            $this->ScheduleModel->set_booked($slot_id, 1);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->roll_back();
            exit('Failed to save appointment.');
        }
        redirect('/appointments');
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

        $patient_id = $_POST['patient_id'] ?? null;
        $doctor_id  = $_POST['doctor_id'] ?? null;
        $slot_id    = $_POST['slot_id'] ?? null;
        $notes      = $_POST['notes'] ?? '';

        if (empty($patient_id) || empty($doctor_id) || empty($slot_id)) {
            exit('Missing required fields.');
        }

        // Ensure doctor is the logged-in doctor's record
        $current_doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id'] ?? 0);
        if (!$current_doctor || (int)$current_doctor['id'] !== (int)$doctor_id) {
            exit('Unauthorized doctor selection.');
        }

        // Validate slot
        $slot = $this->ScheduleModel->get_slot($slot_id);
        if (!$slot) { exit('Invalid slot.'); }
        if ((int)$slot['doctor_id'] !== (int)$doctor_id) { exit('Slot does not belong to doctor.'); }
        if ((int)($slot['is_booked'] ?? 0) === 1 || $this->AppointmentModel->is_slot_booked($slot_id)) {
            exit('This slot is already booked.');
        }

        $this->db->transaction();
        try {
            $this->AppointmentModel->insert_appointment([
                'patient_id' => $patient_id,
                'doctor_id'  => $doctor_id,
                'slot_id'    => $slot_id,
                'notes'      => $notes,
            ]);
            $this->ScheduleModel->set_booked($slot_id, 1);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->roll_back();
            exit('Failed to save appointment.');
        }
        redirect('/dashboard_doctor');
    }

    public function staff_add() {
        $this->lauth->require_login();
        $data['patients'] = $this->PatientModel->all();
        $data['doctors'] = $this->DoctorModel->all();
        $this->call->view('/appointments/staff_add', $data);
    }

    // Dedicated save for Staff
    public function save_staff_add() {
        $this->lauth->require_login();
        $this->lauth->require_role('staff');

        $patient_id = $_POST['patient_id'] ?? null;
        $doctor_id  = $_POST['doctor_id'] ?? null;
        $slot_id    = $_POST['slot_id'] ?? null;
        $notes      = $_POST['notes'] ?? '';

        if (empty($patient_id) || empty($doctor_id) || empty($slot_id)) {
            exit('Missing required fields.');
        }

        // Validate slot
        $slot = $this->ScheduleModel->get_slot($slot_id);
        if (!$slot) { exit('Invalid slot.'); }
        if ((int)$slot['doctor_id'] !== (int)$doctor_id) { exit('Selected slot does not belong to the chosen doctor.'); }
        if ((int)($slot['is_booked'] ?? 0) === 1 || $this->AppointmentModel->is_slot_booked($slot_id)) {
            exit('This slot is already booked.');
        }

        $this->db->transaction();
        try {
            $this->AppointmentModel->insert_appointment([
                'patient_id' => $patient_id,
                'doctor_id'  => $doctor_id,
                'slot_id'    => $slot_id,
                'notes'      => $notes,
            ]);
            $this->ScheduleModel->set_booked($slot_id, 1);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->roll_back();
            exit('Failed to save appointment.');
        }
        redirect('/dashboard_staff');
    }

    // Dedicated save for Admin (using same payload as staff: select doctor, date, slot)
    public function save_admin() {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');

        $patient_id = $_POST['patient_id'] ?? null;
        $doctor_id  = $_POST['doctor_id'] ?? null;
        $slot_id    = $_POST['slot_id'] ?? null;
        $notes      = $_POST['notes'] ?? '';

        if (empty($patient_id) || empty($doctor_id) || empty($slot_id)) {
            exit('Missing required fields.');
        }

        $slot = $this->ScheduleModel->get_slot($slot_id);
        if (!$slot) { exit('Invalid slot.'); }
        if ((int)$slot['doctor_id'] !== (int)$doctor_id) { exit('Selected slot does not belong to the chosen doctor.'); }
        if ((int)($slot['is_booked'] ?? 0) === 1 || $this->AppointmentModel->is_slot_booked($slot_id)) {
            exit('This slot is already booked.');
        }

        $this->db->transaction();
        try {
            $this->AppointmentModel->insert_appointment([
                'patient_id' => $patient_id,
                'doctor_id'  => $doctor_id,
                'slot_id'    => $slot_id,
                'notes'      => $notes,
            ]);
            $this->ScheduleModel->set_booked($slot_id, 1);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->roll_back();
            exit('Failed to save appointment.');
        }
        redirect('/appointments');
    }

    public function edit_form($id) {
        $this->lauth->require_login();
        $data['appointment'] = $this->AppointmentModel->get_with_details($id);
        if (!$data['appointment']) { exit('Appointment not found'); }
        $data['patients'] = $this->PatientModel->all();
        $data['doctors'] = $this->DoctorModel->all();
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
     * Mark appointment as completed
     */
    public function complete($id)
    {
        $this->lauth->require_login();
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin','doctor'], true)) {
            exit('Access denied');
        }

        $appt = $this->AppointmentModel->find($id);
        if (!$appt) { exit('Appointment not found'); }

        // Guard: can only complete a scheduled appointment
        $curr = $appt['status'] ?? 'scheduled';
        if ($curr !== 'scheduled') {
            exit('Invalid action: only scheduled appointments can be completed. Current status: '.htmlspecialchars($curr));
        }

        // Mark completed (keep slot booked for history)
        $ok = $this->AppointmentModel->mark_status($id, 'completed');
        if (!$ok) { exit('Unable to mark as completed'); }

        redirect('/appointments');
    }

    /**
     * Cancel appointment (frees slot)
     */
    public function cancel($id)
    {
        $this->lauth->require_login();
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin','doctor','staff'], true)) {
            exit('Access denied');
        }

        $appt = $this->AppointmentModel->find($id);
        if (!$appt) { exit('Appointment not found'); }

        // Guard: can only cancel a scheduled appointment
        $curr = $appt['status'] ?? 'scheduled';
        if ($curr !== 'scheduled') {
            exit('Invalid action: only scheduled appointments can be cancelled. Current status: '.htmlspecialchars($curr));
        }

        // Free slot if present
        if (!empty($appt['slot_id'])) {
            $this->ScheduleModel->set_booked($appt['slot_id'], 0);
        }
        $ok = $this->AppointmentModel->mark_status($id, 'cancelled');
        if (!$ok) { exit('Unable to cancel appointment'); }

        redirect('/appointments');
    }
}
