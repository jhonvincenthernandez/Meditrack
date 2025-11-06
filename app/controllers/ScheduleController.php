<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ScheduleController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->call->model('ScheduleModel');
        $this->call->model('DoctorModel');
        $this->call->library('lauth');
    }

    // Doctor self-manage slots
    public function manage() {
        $this->lauth->require_login();
        $this->lauth->require_role('doctor');

        // Identify current doctor linked to logged-in user
        $this->call->model('DoctorModel');
        $doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id'] ?? 0);
        if (!$doctor) {
            exit('No doctor record linked to your account.');
        }
        $doctor_id = (int)$doctor['id'];

        // Fetch own slots (with latest appointment status)
        $slots = $this->ScheduleModel->doctor_slots_with_status($doctor_id);

        $data = [
            'doctor' => $doctor,
            'slots' => $slots,
            'flash_warning' => $_SESSION['slot_warning'] ?? null,
            'flash_success' => $_SESSION['slot_success'] ?? null,
        ];
        unset($_SESSION['slot_warning'], $_SESSION['slot_success']);

        $this->call->view('/schedules/manage', $data);
    }

    // Doctor save new slot
    public function doctor_save() {
        $this->lauth->require_login();
        $this->lauth->require_role('doctor');

        $this->call->model('DoctorModel');
        $doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id'] ?? 0);
        if (!$doctor) {
            $_SESSION['slot_warning'] = 'No doctor record linked to your account.';
            redirect('/schedules/manage');
        }
        $doctor_id = (int)$doctor['id'];

        $date = $_POST['date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';

        // Basic validation
            // Past date not allowed
            $today = date('Y-m-d');
            if ($date < $today) {
                $_SESSION['slot_warning'] = "Date must be today or in the future.";
                redirect('/schedules/manage');
            }
            if (empty($date) || empty($start_time) || empty($end_time)) {
            $_SESSION['slot_warning'] = 'All fields are required.';
            redirect('/schedules/manage');
        }
        if (strtotime($end_time) <= strtotime($start_time)) {
            $_SESSION['slot_warning'] = 'End time must be after start time.';
            redirect('/schedules/manage');
        }

        // Overlap validation
        if ($this->ScheduleModel->check_overlap($doctor_id, $date, $start_time, $end_time)) {
            $_SESSION['slot_warning'] = 'This slot overlaps with an existing slot.';
            redirect('/schedules/manage');
        }

        // Insert slot
        $this->ScheduleModel->create_slot([
            'doctor_id'  => $doctor_id,
            'date'       => $date,
            'start_time' => $start_time,
            'end_time'   => $end_time,
            'is_booked'  => 0,
        ]);

        $_SESSION['slot_success'] = 'Slot added successfully.';
        redirect('/schedules/manage');
    }

    // Doctor delete own unbooked slot
    public function doctor_delete($id) {
        $this->lauth->require_login();
        $this->lauth->require_role('doctor');

        $this->call->model('DoctorModel');
        $doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id'] ?? 0);
        if (!$doctor) { redirect('/schedules/manage'); }
        $doctor_id = (int)$doctor['id'];

        $slot = $this->db->table('doctor_slots')->where('id', (int)$id)->get();
        if (!$slot || (int)$slot['doctor_id'] !== $doctor_id) {
            redirect('/schedules/manage');
        }
        // Only allow deletion if not booked
        if ((int)($slot['is_booked'] ?? 0) === 0) {
            $this->ScheduleModel->delete_slot((int)$id);
            $_SESSION['slot_success'] = 'Slot deleted.';
        } else {
            $_SESSION['slot_warning'] = 'Cannot delete a booked slot.';
        }
        redirect('/schedules/manage');
    }
    // Index
    public function index() {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');

    $data['slots'] = $this->ScheduleModel->all_slots_with_doctor();
        $this->call->view('/schedules/index', $data);
    }

    // Add form
    public function add_form() {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');

        $data['doctors'] = $this->DoctorModel->all();
        $this->call->view('/schedules/add', $data);
    }

    // Save slot
    public function save() {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');

        $doctor_id = $_POST['doctor_id'];
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // VALIDATION
            // Past date not allowed
            $today = date('Y-m-d');
            if ($date < $today) {
                $_SESSION['slot_warning'] = 'Date must be today or in the future.';
                redirect('/schedules/add_form');
            }
        if (empty($doctor_id) || empty($date) || empty($start_time) || empty($end_time)) {
            $_SESSION['slot_warning'] = "All fields are required!";
            redirect('/schedules/add_form');
        }

        if (strtotime($end_time) <= strtotime($start_time)) {
            $_SESSION['slot_warning'] = "End time must be after start time!";
            redirect('/schedules/add_form');
        }

        // Check overlap
        if ($this->ScheduleModel->check_overlap($doctor_id, $date, $start_time, $end_time)) {
            $_SESSION['slot_warning'] = "This slot overlaps with an existing slot!";
            redirect('/schedules/add_form');
        }

        // Insert
        $slot = [
            'doctor_id' => $doctor_id,
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'is_booked' => 0
        ];
        $this->ScheduleModel->create_slot($slot);
        redirect('/schedules');
    }

    // Edit form
    public function edit_form($id) {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');

        $data['slot'] = $this->ScheduleModel->db->table('doctor_slots')->where('id', $id)->get();
        $data['doctors'] = $this->DoctorModel->all();
        $this->call->view('/schedules/edit', $data);
    }

    // Update slot
    public function update($id) {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');

        $doctor_id = $_POST['doctor_id'];
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        if (strtotime($end_time) <= strtotime($start_time)) {
            $_SESSION['slot_warning'] = "End time must be after start time!";
            redirect("/schedules/edit_form/$id");
        }

        if ($this->ScheduleModel->check_overlap($doctor_id, $date, $start_time, $end_time, $id)) {
            $_SESSION['slot_warning'] = "This slot overlaps with an existing slot!";
            redirect("/schedules/edit_form/$id");
        }

        $slot = [
            'doctor_id' => $doctor_id,
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];
        $this->ScheduleModel->update_slot($id, $slot);
        redirect('/schedules');
    }

    // Delete
    public function delete($id) {
        $this->lauth->require_login();
        $this->lauth->require_role('admin');

        $this->ScheduleModel->delete_slot($id);
        redirect('/schedules');
    }
}
