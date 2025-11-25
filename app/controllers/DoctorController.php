<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: DoctorController
 * 
 * Automatically generated via CLI.
 */
class DoctorController extends Controller {
     public function __construct() {
        parent::__construct();
        $this->lauth->require_login();
        $this->call->model('DoctorModel');
        $this->call->model('UserModel');
    }

     /** LIST DOCTORS / DOCTOR SCHEDULE **/
    public function index() {
        $page =1;
        if(isset($_GET['page']) && ! empty($_GET['page'])) {
            $page = $this->io->get('page');
        }

        $q = '';
        if(isset($_GET['q']) && ! empty($_GET['q'])) {
            $q = trim($this->io->get('q'));
        }

        $records_per_page = 2;

        $all = $this->DoctorModel->page($q, $records_per_page, $page);
        $data['all'] = $all['records'];
        $total_rows = $all['total_rows'];
        $this->pagination->set_options([
            'first_link'     => '⏮ First',
            'last_link'      => 'Last ⏭',
            'next_link'      => 'Next →',
            'prev_link'      => '← Prev',
            'page_delimiter' => '&page='
        ]);
        $this->pagination->set_theme('bootstrap'); // or 'tailwind', or 'custom'
        $this->pagination->initialize($total_rows, $records_per_page, $page, site_url('/doctors').'?q='.$q);
        $data['page'] = $this->pagination->paginate();
        // Pass flash messages (using schedule controller pattern)
        $data['flash_success'] = $_SESSION['success'] ?? null;
        $data['flash_error'] = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);
            $this->call->view('/doctors/index', $data);

    }


    /**
     * Show add doctor form
     */
    public function add_form() {
       $this->lauth->require_admin();

   // Get all users with role 'doctor'
    $users = $this->UserModel->all();
    $users = array_filter($users, fn($u) => $u['role'] === 'doctor');

    // Exclude users who already have a doctor record
    $existing_doctor_user_ids = array_column($this->DoctorModel->all(), 'user_id');
    $users = array_filter($users, fn($u) => !in_array($u['id'], $existing_doctor_user_ids));

    // Pass $users and specialties to the view
    $specialties = $this->get_specialties();
    $data = [
        'users' => $users,
        'specialties' => $specialties,
        'flash_success' => $_SESSION['success'] ?? null,
        'flash_error' => $_SESSION['error'] ?? null,
        'errors' => $_SESSION['errors'] ?? null,
        'old' => $_SESSION['old'] ?? []
    ];
    unset($_SESSION['success'], $_SESSION['error'], $_SESSION['errors'], $_SESSION['old']);
    $this->call->view('/doctors/add', $data);
    }

    /**
     * Save doctor
     */
    public function save() {
        $this->lauth->require_admin();

        $this->call->library('Form_validation');

    $this->form_validation->name('name')->required('Doctor name is required.')->valid_name('Doctor name must contain only letters and spaces.');
    $this->form_validation->name('specialty')->required('Specialty is required.')->in_list(implode(',', $this->get_specialties()), 'Please select a valid specialty.');
    $this->form_validation->name('contact')->required('Contact number is required.')->custom_pattern('09[0-9]{9}', 'Contact must be a valid phone number starting with 09.')->max_length(11, 'Contact number must be 11 characters or less.');

        if (!$this->form_validation->run()) {
            $_SESSION['errors'] = $this->form_validation->get_errors();
            // Persist old inputs for the form
            $_SESSION['old'] = [
                'user_id' => $this->io->post('user_id'),
                'name' => $this->io->post('name'),
                'specialty' => $this->io->post('specialty'),
                'contact' => $this->io->post('contact')
            ];
            redirect('/doctors/add');
            return;
        }

        // Use io helper for sanitized input, allow empty user_id
        $user_id_raw = $this->io->post('user_id');
        $user_id = null;
        if (!empty($user_id_raw)) {
            if (!ctype_digit((string)$user_id_raw)) {
                $_SESSION['error'] = 'Invalid user selected.';
                redirect('/doctors/add');
                return;
            }
            $user_id = (int)$user_id_raw;
            // Ensure user exists and is role doctor
            $user = $this->UserModel->find($user_id);
            if (!$user || ($user['role'] ?? '') !== 'doctor') {
                $_SESSION['error'] = 'Selected user is not a valid doctor account.';
                redirect('/doctors/add');
                return;
            }
            // Ensure this user is not already assigned to a doctor
            $existing_ids = array_column($this->DoctorModel->all(), 'user_id');
            if (in_array($user_id, $existing_ids)) {
                $_SESSION['error'] = 'This user is already assigned to a doctor record.';
                redirect('/doctors/add');
                return;
            }
        }
        $doctor = [
            'user_id' => $user_id,
            'name' => trim($this->io->post('name')),
            'specialty' => trim($this->io->post('specialty')),
            'contact' => trim($this->io->post('contact'))
        ];

        $this->DoctorModel->insert($doctor);
        $_SESSION['success'] = 'Doctor added successfully.';
        redirect('/doctors');
    }

    /**
     * Show edit form
     */
    public function edit_form($id) {
        $role = $_SESSION['role'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0;

        $doctor = $this->DoctorModel->find($id);
        if (!$doctor) {
            echo '<h3>Doctor not found</h3>'; exit;
        }

        if ($role === 'admin' || ($role === 'doctor' && $doctor['user_id'] == $user_id)) {
            $specialties = $this->get_specialties();
            $data = [
                'doctor' => $doctor,
                'specialties' => $specialties,
                'flash_success' => $_SESSION['success'] ?? null,
                'flash_error' => $_SESSION['error'] ?? null,
                'errors' => $_SESSION['errors'] ?? null,
                'old' => $_SESSION['old'] ?? []
            ];
            unset($_SESSION['success'], $_SESSION['error'], $_SESSION['errors'], $_SESSION['old']);
            $this->call->view('/doctors/edit', $data);
        } else {
            echo '<h3>Access denied</h3>'; exit;
        }
    }

    /**
     * Update doctor
     */
    public function update($id) {
        $role = $_SESSION['role'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0;

        $doctor = $this->DoctorModel->find($id);
        if (!$doctor) {
            echo '<h3>Doctor not found</h3>'; exit;
        }

        if ($role === 'admin' || ($role === 'doctor' && $doctor['user_id'] == $user_id)) {
            // Use io helper and validate
            $this->call->library('Form_validation');
            $this->form_validation->name('name')->required('Doctor name is required.')->valid_name('Doctor name must contain only letters and spaces.');
            $this->form_validation->name('specialty')->required('Specialty is required.')->in_list(implode(',', $this->get_specialties()), 'Please select a valid specialty.');
            $this->form_validation->name('contact')->required('Contact number is required.')->custom_pattern('09[0-9]{9}', 'Contact must be a valid phone number starting with 09.')->max_length(11, 'Contact number must be 11 characters or less.');
            if (!$this->form_validation->run()) {
                $_SESSION['errors'] = $this->form_validation->get_errors();
                $_SESSION['old'] = [
                    'name' => $this->io->post('name'),
                    'specialty' => $this->io->post('specialty'),
                    'contact' => $this->io->post('contact')
                ];
                redirect('/doctors/edit/'.$id);
                return;
            }
            $updated = [
                'name' => trim($this->io->post('name')),
                'specialty' => trim($this->io->post('specialty')),
                'contact' => trim($this->io->post('contact'))
            ];
            $this->DoctorModel->update($id, $updated);
            $_SESSION['success'] = 'Doctor updated successfully.';
            redirect('/doctors');
        } else {
            echo '<h3>Access denied</h3>'; exit;
        }
    }

    /**
     * Delete doctor
     */
    public function delete($id) {
        $this->lauth->require_admin();
        
        // Check if doctor has appointments
        $sql = "SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ?";
        $stmt = $this->db->raw($sql, [$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete doctor with existing appointments.';
            redirect('/doctors');
            return;
        }
        
        $this->DoctorModel->delete($id);
        $_SESSION['success'] = 'Doctor deleted successfully.';
        redirect('/doctors');
    }

    /**
     * A small list of specialties for dropdowns. Expand as needed.
     */
    protected function get_specialties() {
        return [
            'General Practice',
            'Pediatrics',
            'Dermatology',
            'Cardiology',
            'Obstetrics and Gynecology',
            'Orthopedics',
            'Internal Medicine',
            'Neurology',
            'Psychiatry',
            'Radiology',
            'Otolaryngology',
            'Endocrinology'
        ];
    }
}