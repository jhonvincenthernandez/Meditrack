<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: PatientController
 * 
 * Automatically generated via CLI.
 */
class PatientController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->call->library('lauth');
        $this->lauth->require_login();
        $this->call->model('PatientModel');
    }

    public function index() {
    $query = trim((string)($_GET['q'] ?? ''));
        $patients = $query !== '' ? $this->PatientModel->search($query) : $this->PatientModel->all();
        if (!is_array($patients)) {
            $patients = [];
        }

        if ($this->lauth->is_staff() && !$this->lauth->is_admin()) {
            $directoryData = $this->build_staff_directory_dataset($patients, $query);
            $this->call->view('/patients/staff_directory', $directoryData);
            return;
        }

        $this->lauth->require_role('admin');
        $data = [
            'patients' => $patients,
            'query' => $query,
        ];

        // pass flashes
        $data['flash_success'] = $_SESSION['success'] ?? null;
        $data['flash_error'] = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);
        $this->call->view('/patients/index', $data);
    }

    public function add_form() {
        $this->lauth->require_role('admin');
        $data = [
            'flash_success' => $_SESSION['success'] ?? null,
            'flash_error' => $_SESSION['error'] ?? null,
            'errors' => $_SESSION['errors'] ?? null,
            'old' => $_SESSION['old'] ?? []
        ];
        unset($_SESSION['success'], $_SESSION['error'], $_SESSION['errors'], $_SESSION['old']);
        $this->call->view('/patients/add', $data);
    }

    public function save() {
        $this->lauth->require_role('admin');
        $this->call->library('Form_validation');
        $this->form_validation->name('name')->required('Patient name is required.')->valid_name('Patient name must contain only letters and spaces.');
        $this->form_validation->name('age')->required('Age is required.')->pattern('int', 'Age must be a number.');
        $this->form_validation->name('gender')->required('Gender is required.')->in_list('Male,Female,Other', 'Please select a valid gender.');
        $this->form_validation->name('contact')->required('Contact is required.')->custom_pattern('09[0-9]{9}', 'Contact must be 11 digits and start with 09.')->max_length(11, 'Contact must be 11 digits or less.');

        if (! $this->form_validation->run()) {
            $_SESSION['errors'] = $this->form_validation->get_errors();
            $_SESSION['old'] = [
                'name' => $this->io->post('name'),
                'age' => $this->io->post('age'),
                'gender' => $this->io->post('gender'),
                'contact' => $this->io->post('contact'),
                'medical_history' => $this->io->post('medical_history')
            ];
            redirect('/patients/add');
            return;
        }

        // Normalize contact to digits only and ensure beginning with 09
        $contact = preg_replace('/\D+/', '', (string)$this->io->post('contact'));
        if (strlen($contact) === 13 && substr($contact, 0, 3) === '63') {
            // Convert +639xxxxxxxx to 09xxxxxxxxx
            $contact = '0' . substr($contact, 2);
        }

        $patient = [
            'name' => trim($this->io->post('name')),
            'age' => (int)$this->io->post('age'),
            'gender' => $this->io->post('gender'),
            'contact' => $contact,
            'medical_history' => $this->io->post('medical_history') ?? null
        ];
        // audit
        $patient['created_by'] = $_SESSION['user_id'] ?? null;
        // Prevent duplicate by contact (if provided)
        if (!empty($contact)) {
            $existing = $this->db->table('patients')->where('contact', $contact)->limit(1)->get();
            if ($existing) {
                $_SESSION['error'] = 'A patient with this contact already exists.';
                $_SESSION['old'] = [
                    'name' => $this->io->post('name'),
                    'age' => $this->io->post('age'),
                    'gender' => $this->io->post('gender'),
                    'contact' => $this->io->post('contact'),
                    'medical_history' => $this->io->post('medical_history')
                ];
                redirect('/patients/add');
                return;
            }
        }
        $this->PatientModel->insert($patient);
        $_SESSION['success'] = 'Patient added successfully.';
        redirect('/patients');
    }

    public function edit_form($id) {
        $this->lauth->require_role('admin');
        $data['patient'] = $this->PatientModel->find($id);
        if (!$data['patient']) {
            $_SESSION['error'] = 'Patient not found.';
            redirect('/patients');
            return;
        }
        $data['flash_success'] = $_SESSION['success'] ?? null;
        $data['flash_error'] = $_SESSION['error'] ?? null;
        $data['errors'] = $_SESSION['errors'] ?? null;
        $data['old'] = $_SESSION['old'] ?? [];
        unset($_SESSION['success'], $_SESSION['error'], $_SESSION['errors'], $_SESSION['old']);
        $this->call->view('/patients/edit', $data);
    }

    public function update($id) {
        $this->lauth->require_role('admin');
        $patient = $this->PatientModel->find($id);
        if (!$patient) {
            $_SESSION['error'] = 'Patient not found.';
            redirect('/patients');
            return;
        }
        $this->call->library('Form_validation');
        $this->form_validation->name('name')->required('Patient name is required.')->valid_name('Patient name must contain only letters and spaces.');
        $this->form_validation->name('age')->required('Age is required.')->pattern('int', 'Age must be a number.');
        $this->form_validation->name('gender')->required('Gender is required.')->in_list('Male,Female,Other', 'Please select a valid gender.');
        $this->form_validation->name('contact')->required('Contact is required.')->custom_pattern('09[0-9]{9}', 'Contact must be 11 digits and start with 09.')->max_length(11, 'Contact must be 11 digits or less.');
        if (! $this->form_validation->run()) {
            $_SESSION['errors'] = $this->form_validation->get_errors();
            $_SESSION['old'] = [
                'name' => $this->io->post('name'),
                'age' => $this->io->post('age'),
                'gender' => $this->io->post('gender'),
                'contact' => $this->io->post('contact'),
                'medical_history' => $this->io->post('medical_history')
            ];
            redirect('/patients/edit/'.$id);
            return;
        }

        $contact = preg_replace('/\D+/', '', (string)$this->io->post('contact'));
        if (strlen($contact) === 13 && substr($contact, 0, 3) === '63') {
            $contact = '0' . substr($contact, 2);
        }
        $updated = [
            'name' => trim($this->io->post('name')),
            'age' => (int)$this->io->post('age'),
            'gender' => $this->io->post('gender'),
            'contact' => $contact,
            'medical_history' => $this->io->post('medical_history') ?? null
        ];
        $updated['updated_by'] = $_SESSION['user_id'] ?? null;
        // Prevent duplicate contact to other patients
        if (!empty($contact)) {
            $existing = $this->db->table('patients')->where('contact', $contact)->limit(1)->get();
            if ($existing && (int)$existing['id'] !== (int)$id) {
                $_SESSION['error'] = 'Another patient already uses this contact number.';
                $_SESSION['old'] = [
                    'name' => $this->io->post('name'),
                    'age' => $this->io->post('age'),
                    'gender' => $this->io->post('gender'),
                    'contact' => $this->io->post('contact'),
                    'medical_history' => $this->io->post('medical_history')
                ];
                redirect('/patients/edit/'.$id);
                return;
            }
        }
        $this->PatientModel->update($id, $updated);
        $_SESSION['success'] = 'Patient updated successfully.';
        redirect('/patients');
    }

    public function delete($id) {
        $this->lauth->require_role('admin');
        // Prevent deletion if patient has appointments
        $sql = "SELECT COUNT(*) as count FROM appointments WHERE patient_id = ?";
        $stmt = $this->db->raw($sql, [$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && $result['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete patient with existing appointments.';
            redirect('/patients');
            return;
        }
        $this->PatientModel->delete($id);
        $_SESSION['success'] = 'Patient deleted successfully.';
        redirect('/patients');
    }

    private function build_staff_directory_dataset(array $patients, string $query): array
    {
        $this->call->model('AppointmentModel');
        $appointments = $this->AppointmentModel->all_with_details();
        if (!is_array($appointments)) {
            $appointments = [];
        }

        $now = time();
        $stats = [];

        foreach ($appointments as $appt) {
            $patientId = (int)($appt['patient_id'] ?? 0);
            if ($patientId <= 0) {
                continue;
            }

            if (!isset($stats[$patientId])) {
                $stats[$patientId] = [
                    'total' => 0,
                    'outstanding' => 0.0,
                    'next' => null,
                    'last' => null,
                ];
            }

            $stats[$patientId]['total']++;

            $status = strtolower(trim((string)($appt['status'] ?? 'scheduled')));
            if ($status === '') {
                $status = 'scheduled';
            }
            $paymentStatus = strtolower(trim((string)($appt['payment_status'] ?? 'pending')));
            if ($paymentStatus === '') {
                $paymentStatus = 'pending';
            }

            $amount = (float)($appt['amount'] ?? $appt['consultation_fee'] ?? 0);
            if ($paymentStatus === 'pending' && $status !== 'cancelled') {
                $stats[$patientId]['outstanding'] += $amount;
            }

            $slotDate = $appt['slot_date'] ?? null;
            $startTime = $appt['start_time'] ?? '00:00:00';
            $slotTs = $slotDate ? strtotime($slotDate . ' ' . $startTime) : null;

            if ($slotTs && $slotTs >= $now && $status !== 'cancelled') {
                $next = $stats[$patientId]['next'];
                if ($next === null || $slotTs < ($next['ts'] ?? PHP_INT_MAX)) {
                    $stats[$patientId]['next'] = [
                        'ts' => $slotTs,
                        'label' => date('M d, Y · h:i A', $slotTs),
                        'doctor' => $appt['doctor_name'] ?? null,
                        'status' => $status,
                    ];
                }
            }

            $completedTs = null;
            if ($status === 'completed') {
                $completedTs = !empty($appt['completed_at']) ? strtotime($appt['completed_at']) : $slotTs;
            } elseif ($slotTs && $slotTs < $now && $status !== 'cancelled') {
                $completedTs = $slotTs;
            }

            if ($completedTs) {
                $last = $stats[$patientId]['last'];
                if ($last === null || $completedTs > ($last['ts'] ?? 0)) {
                    $stats[$patientId]['last'] = [
                        'ts' => $completedTs,
                        'label' => date('M d, Y · h:i A', $completedTs),
                        'doctor' => $appt['doctor_name'] ?? null,
                        'status' => $status,
                    ];
                }
            }
        }

        $directory = [];
        $upcomingCount = 0;
        $pendingCount = 0;
        $totalOutstanding = 0.0;

        foreach ($patients as $patient) {
            $pid = (int)($patient['id'] ?? 0);
            $stat = $stats[$pid] ?? null;
            $outstanding = $stat['outstanding'] ?? 0.0;

            $row = [
                'id' => $pid,
                'name' => $patient['name'] ?? 'Patient',
                'age' => $patient['age'] ?? '—',
                'gender' => $patient['gender'] ?? '—',
                'contact' => $patient['contact'] ?? '—',
                'total_visits' => (int)($stat['total'] ?? 0),
                'outstanding' => (float)$outstanding,
                'next_visit' => $stat['next']['label'] ?? null,
                'next_doctor' => $stat['next']['doctor'] ?? null,
                'next_status' => $stat['next']['status'] ?? null,
                'last_visit' => $stat['last']['label'] ?? null,
                'last_doctor' => $stat['last']['doctor'] ?? null,
                'last_status' => $stat['last']['status'] ?? null,
            ];

            if (!empty($row['next_visit'])) {
                $upcomingCount++;
            }
            if ($row['outstanding'] > 0) {
                $pendingCount++;
                $totalOutstanding += $row['outstanding'];
            }

            $directory[] = $row;
        }

        usort($directory, function ($a, $b) {
            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        });

        return [
            'patients' => $directory,
            'summary' => [
                'total' => count($patients),
                'upcoming' => $upcomingCount,
                'pending' => $pendingCount,
                'pending_amount' => $totalOutstanding,
            ],
            'query' => $query,
        ];
    }
}