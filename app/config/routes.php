<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/

/* =======================
   🔐 AUTHENTICATION
======================= */
$router->match('/auth/login', 'AuthController::login', ['GET', 'POST']);
$router->get('/auth/logout', 'AuthController::logout');

/* =======================
   🧭 DASHBOARDS
======================= */
$router->get('/', 'AuthController::login'); // Default to login page
$router->get('/dashboard_admin', 'DashboardController::admin');
$router->get('/dashboard_doctor', 'DashboardController::doctor');
$router->get('/dashboard_staff', 'DashboardController::staff');

/* =======================
   👥 USER MANAGEMENT (Admin only)
======================= */
$router->get('/users', 'UserController::index');
$router->get('/users/add_form', 'UserController::add_form');
$router->post('/users/save', 'UserController::save');
$router->get('/users/edit_form/{id}', 'UserController::edit_form');
$router->post('/users/update/{id}', 'UserController::update');
$router->get('/users/delete/{id}', 'UserController::delete');

/* =======================
   🧾 PATIENT MANAGEMENT
======================= */
$router->get('/patients', 'PatientController::index');
$router->get('/patients/add', 'PatientController::add_form');
$router->post('/patients/save', 'PatientController::save');
$router->get('/patients/edit/{id}', 'PatientController::edit_form');
$router->post('/patients/update/{id}', 'PatientController::update');
$router->get('/patients/delete/{id}', 'PatientController::delete');

/* =======================
   👨‍⚕️ DOCTOR MANAGEMENT (Admin only)
======================= */
$router->get('/doctors', 'DoctorController::index');
$router->get('/doctors/add', 'DoctorController::add_form');
$router->post('/doctors/save', 'DoctorController::save');
$router->get('/doctors/edit/{id}', 'DoctorController::edit_form');
$router->post('/doctors/update/{id}', 'DoctorController::update');
$router->get('/doctors/delete/{id}', 'DoctorController::delete');

/* =======================
   📅 APPOINTMENT MANAGEMENT
======================= */
$router->get('/appointments', 'AppointmentController::index');
$router->get('/appointments/add', 'AppointmentController::add_form');
$router->get('/appointments/doc_add', 'AppointmentController::doc_add');
$router->post('/appointments/save_doc_add', 'AppointmentController::save_doc_add');
$router->post('/appointments/save_admin', 'AppointmentController::save_admin');
$router->post('/appointments/save', 'AppointmentController::save');
// Staff add appointment
$router->get('/appointments/staff_add', 'AppointmentController::staff_add');
$router->post('/appointments/save_staff_add', 'AppointmentController::save_staff_add');
$router->get('/appointments/edit/{id}', 'AppointmentController::edit_form');
$router->post('/appointments/update/{id}', 'AppointmentController::update');
$router->get('/appointments/delete/{id}', 'AppointmentController::delete');
// Status actions
$router->get('/appointments/{id}/complete', 'AppointmentController::complete');
$router->get('/appointments/{id}/cancel', 'AppointmentController::cancel');

/* Doctor schedule management (admin) */
$router->get('/schedules', 'ScheduleController::index'); // admin
$router->get('/schedules/manage', 'ScheduleController::manage'); // doctor manage
$router->get('/schedules/add_form', 'ScheduleController::add_form');
$router->post('/schedules/save', 'ScheduleController::save');
$router->post('/schedules/doctor_save', 'ScheduleController::doctor_save');
$router->get('/schedules/edit_form/{id}', 'ScheduleController::edit_form');
$router->post('/schedules/update/{id}', 'ScheduleController::update');
$router->get('/schedules/delete/{id}', 'ScheduleController::delete');
$router->get('/schedules/doctor_delete/{id}', 'ScheduleController::doctor_delete');

// Add these under appointment routes
$router->get('/appointments/getDoctorDates/{doctor_id}', 'AppointmentController::getDoctorDates');
$router->get('/appointments/getAvailableSlots/{doctor_id}/{date}', 'AppointmentController::getAvailableSlots');



