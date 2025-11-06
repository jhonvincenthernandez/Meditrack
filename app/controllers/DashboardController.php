<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: DashboardController
 * 
 * Automatically generated via CLI.
 */
class DashboardController extends Controller {

    
     public function admin() {

        $this->call->library('lauth');
        $this->lauth->require_login();

        // Fetch data
        $patients = $this->PatientModel->all();
        $doctors = $this->DoctorModel->all();
        $appointments = $this->AppointmentModel->all_with_details();

        // Split upcoming/past based on current time
        $now = time();
        $upcoming = array_filter($appointments, function($a) use ($now) {
            if (empty($a['slot_date'])) return false;
            $time = !empty($a['start_time']) ? $a['start_time'] : '00:00:00';
            $ts = strtotime($a['slot_date'] . ' ' . $time);
            return $ts !== false && $ts >= $now;
        });

        usort($upcoming, function($a, $b) {
            $ta = strtotime(($a['slot_date'] ?? '1970-01-01') . ' ' . ($a['start_time'] ?? '00:00:00'));
            $tb = strtotime(($b['slot_date'] ?? '1970-01-01') . ' ' . ($b['start_time'] ?? '00:00:00'));
            return $ta <=> $tb;
        });

        $past = array_filter($appointments, function($a) use ($now) {
            if (empty($a['slot_date'])) return false;
            $time = !empty($a['start_time']) ? $a['start_time'] : '00:00:00';
            $ts = strtotime($a['slot_date'] . ' ' . $time);
            return $ts !== false && $ts < $now;
        });
        usort($past, function($a, $b) {
            $ta = strtotime(($a['slot_date'] ?? '1970-01-01') . ' ' . ($a['start_time'] ?? '00:00:00'));
            $tb = strtotime(($b['slot_date'] ?? '1970-01-01') . ' ' . ($b['start_time'] ?? '00:00:00'));
            return $tb <=> $ta; // Descending
        });

        // Pass all to view
        $data = [
            'patient_count' => count($patients),
            'doctor_count' => count($doctors),
            'appointment_count' => count($appointments),
            'upcoming' => $upcoming,
            'past' => $past
        ];

        $this->call->view('/dashboard/index', $data);
    }

    public function doctor() {
    $this->call->library('lauth');
    $this->lauth->require_login();
    $this->lauth->require_role('doctor');

    $this->call->model('DoctorModel');
    $this->call->model('AppointmentModel');
    $this->call->model('PatientModel');

    // 1️⃣ Get the doctor linked to the logged-in user
    $doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id']);
    if (!$doctor) {
        echo '<h3>No doctor record linked to your account.</h3>';
        exit;
    }
    $doctor_id = $doctor['id'];

    // 2️⃣ Get appointments with details and filter by this doctor
    $all = $this->AppointmentModel->all_with_details();
    $appointments = array_values(array_filter($all, fn($a) => (int)$a['doctor_id'] === (int)$doctor_id));

    // 3️⃣ Split by Upcoming vs Past based on current time
    $now = time();
    $upcoming = array_filter($appointments, function($a) use ($now) {
        if (empty($a['slot_date'])) return false;
        $time = !empty($a['start_time']) ? $a['start_time'] : '00:00:00';
        $ts = strtotime($a['slot_date'] . ' ' . $time);
        return $ts !== false && $ts >= $now;
    });
    usort($upcoming, function($a,$b){
        $ta = strtotime(($a['slot_date'] ?? '1970-01-01') . ' ' . ($a['start_time'] ?? '00:00:00'));
        $tb = strtotime(($b['slot_date'] ?? '1970-01-01') . ' ' . ($b['start_time'] ?? '00:00:00'));
        return $ta <=> $tb;
    });

    $past = array_filter($appointments, function($a) use ($now) {
        if (empty($a['slot_date'])) return false;
        $time = !empty($a['start_time']) ? $a['start_time'] : '00:00:00';
        $ts = strtotime($a['slot_date'] . ' ' . $time);
        return $ts !== false && $ts < $now;
    });
    // Sort past descending (most recent first)
    usort($past, function($a,$b){
        $ta = strtotime(($a['slot_date'] ?? '1970-01-01') . ' ' . ($a['start_time'] ?? '00:00:00'));
        $tb = strtotime(($b['slot_date'] ?? '1970-01-01') . ' ' . ($b['start_time'] ?? '00:00:00'));
        return $tb <=> $ta;
    });

    // 4️⃣ Prepare data for view
    $data = [
        'patient_count' => count($this->PatientModel->all()),
        'appointment_count' => count($appointments),
        'upcoming' => $upcoming,
        'past' => $past
    ];

    // 5️⃣ Load the view
    $this->call->view('/dashboard/doctor', $data);
}



    public function staff() {
    $this->call->library('lauth');
    $this->lauth->require_login();
    $this->lauth->require_role('staff');

    $this->call->model('PatientModel');
    $this->call->model('AppointmentModel');

    $patients = $this->PatientModel->all(); // staff-accessible patients
    $appointments = $this->AppointmentModel->all_with_details();

    // Split upcoming/past based on current time
    $now = time();
    $upcoming = array_filter($appointments, function($a) use ($now) {
        if (empty($a['slot_date'])) return false;
        $time = !empty($a['start_time']) ? $a['start_time'] : '00:00:00';
        $ts = strtotime($a['slot_date'] . ' ' . $time);
        return $ts !== false && $ts >= $now;
    });
    usort($upcoming, function($a,$b){
        $ta = strtotime(($a['slot_date'] ?? '1970-01-01') . ' ' . ($a['start_time'] ?? '00:00:00'));
        $tb = strtotime(($b['slot_date'] ?? '1970-01-01') . ' ' . ($b['start_time'] ?? '00:00:00'));
        return $ta <=> $tb;
    });

    $past = array_filter($appointments, function($a) use ($now) {
        if (empty($a['slot_date'])) return false;
        $time = !empty($a['start_time']) ? $a['start_time'] : '00:00:00';
        $ts = strtotime($a['slot_date'] . ' ' . $time);
        return $ts !== false && $ts < $now;
    });
    usort($past, function($a,$b){
        $ta = strtotime(($a['slot_date'] ?? '1970-01-01') . ' ' . ($a['start_time'] ?? '00:00:00'));
        $tb = strtotime(($b['slot_date'] ?? '1970-01-01') . ' ' . ($b['start_time'] ?? '00:00:00'));
        return $tb <=> $ta; // Descending
    });

    // Prepare data for view
    $data = [
        'patient_count' => count($patients),
        'appointment_count' => count($appointments),
        'upcoming' => $upcoming,
        'past' => $past,
        'patients' => $patients // needed for add appointment form
    ];

    $this->call->view('/dashboard/staff', $data);
}

    /**
     * Private helper to get upcoming appointments sorted by date
     */
    private function get_upcoming_appointments(array $appointments): array {
        $todayStart = strtotime(date('Y-m-d'));
        $upcoming = array_filter($appointments, function($a) use ($todayStart) {
            if (empty($a['slot_date'])) return false;
            $time = !empty($a['start_time']) ? $a['start_time'] : '00:00:00';
            $ts = strtotime($a['slot_date'] . ' ' . $time);
            return $ts !== false && $ts >= $todayStart;
        });

        usort($upcoming, function($a, $b) {
            $ta = strtotime(($a['slot_date'] ?? '1970-01-01') . ' ' . ($a['start_time'] ?? '00:00:00'));
            $tb = strtotime(($b['slot_date'] ?? '1970-01-01') . ' ' . ($b['start_time'] ?? '00:00:00'));
            return $ta <=> $tb;
        });

        return $upcoming;
    }
}