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

    // Pass $users to the view
    $this->call->view('/doctors/add', ['users' => $users]);
    }

    /**
     * Save doctor
     */
    public function save() {
        $this->lauth->require_admin();

        $doctor = [
            'user_id' => $_POST['user_id'] ?? null, // optional link to users table
            'name' => $_POST['name'] ?? '',
            'specialty' => $_POST['specialty'] ?? '',
            'contact' => $_POST['contact'] ?? ''
        ];

        $this->DoctorModel->insert($doctor);
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
            $this->call->view('/doctors/edit', ['doctor' => $doctor]);
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
            $updated = [
                'name' => $_POST['name'] ?? '',
                'specialty' => $_POST['specialty'] ?? '',
                'contact' => $_POST['contact'] ?? ''
            ];
            $this->DoctorModel->update($id, $updated);
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
        $this->DoctorModel->delete($id);
        redirect('/doctors');
    }
}