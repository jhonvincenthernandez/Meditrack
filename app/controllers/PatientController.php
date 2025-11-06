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
        $this->call->model('PatientModel');
    }

    public function index() {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';

    if ($q !== '') {
        $data['patients'] = $this->PatientModel->search($q);
    } else {
        $data['patients'] = $this->PatientModel->all();
    }

    $this->call->view('/patients/index', $data);
}

    public function add_form() {
        $this->call->view('/patients/add');
    }

    public function save() {
        $patient = [
            'name' => $_POST['name'],
            'age' => $_POST['age'],
            'gender' => $_POST['gender'],
            'contact' => $_POST['contact']
        ];
        $this->PatientModel->insert($patient);
        redirect('/patients');
    }

    public function edit_form($id) {
        $data['patient'] = $this->PatientModel->find($id);
        $this->call->view('/patients/edit', $data);
    }

    public function update($id) {
        $updated = [
            'name' => $_POST['name'],
            'age' => $_POST['age'],
            'gender' => $_POST['gender'],
            'contact' => $_POST['contact']
        ];
        $this->PatientModel->update($id, $updated);
        redirect('/patients');
    }

    public function delete($id) {
        $this->PatientModel->delete($id);
        redirect('/patients');
    }
}