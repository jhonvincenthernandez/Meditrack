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

        $this->call->model('PatientModel');
        $this->call->model('DoctorModel');
        $this->call->model('AppointmentModel');
        $this->call->model('PaymentModel');

        $patients = $this->PatientModel->all();
        $doctors = $this->DoctorModel->all();
        $appointments = $this->AppointmentModel->all_with_details();

        $statusCounts = [
            'scheduled' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0,
        ];

        $today = date('Y-m-d');
        $todaySchedule = [];
        $todayTs = strtotime($today . ' 00:00:00');

        $weekMap = [];
        for ($i = 0; $i < 7; $i++) {
            $dateKey = date('Y-m-d', strtotime("+$i day"));
            $weekMap[$dateKey] = [
                'date' => $dateKey,
                'label' => date('D, M d', strtotime($dateKey)),
                'total' => 0,
                'paid' => 0,
                'pending' => 0,
                'cancelled' => 0,
            ];
        }

        foreach ($appointments as $appt) {
            $status = strtolower(trim((string)($appt['status'] ?? 'scheduled')));
            if ($status === '') {
                $status = 'scheduled';
            }
            if (!array_key_exists($status, $statusCounts)) {
                $status = 'scheduled';
            }
            $statusCounts[$status]++;

            $slotDate = $appt['slot_date'] ?? null;
            $startTime = $appt['start_time'] ?? '00:00:00';

            if ($slotDate === $today) {
                $todaySchedule[] = [
                    'time' => $this->format_slot_time($slotDate, $startTime, $appt['end_time'] ?? null),
                    'start_ts' => strtotime($slotDate . ' ' . $startTime) ?: $todayTs,
                    'patient' => $appt['patient_name'] ?? 'N/A',
                    'doctor' => $appt['doctor_name'] ?? 'N/A',
                    'status' => $status,
                    'payment_status' => strtolower($appt['payment_status'] ?? 'pending'),
                    'amount' => (float)($appt['amount'] ?? 0),
                ];
            }

            if ($slotDate && isset($weekMap[$slotDate])) {
                $weekMap[$slotDate]['total']++;

                $paymentStatus = strtolower($appt['payment_status'] ?? 'pending');
                switch ($paymentStatus) {
                    case 'paid':
                        $weekMap[$slotDate]['paid']++;
                        break;
                    case 'cancelled':
                    case 'void':
                    case 'expired':
                        $weekMap[$slotDate]['cancelled']++;
                        break;
                    default:
                        $weekMap[$slotDate]['pending']++;
                        break;
                }
            }
        }

        usort($todaySchedule, function($a, $b) {
            return $a['start_ts'] <=> $b['start_ts'];
        });

        $upcomingWeek = array_values($weekMap);

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $weekEnd = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');

        $revenue = [
            'today' => $this->PaymentModel->sum_paid_between($todayStart, $todayEnd),
            'week' => $this->PaymentModel->sum_paid_between($weekStart, $weekEnd),
            'month' => $this->PaymentModel->sum_paid_between($monthStart, $monthEnd),
        ];

    $pendingSummary = array_merge(['count' => 0, 'amount' => 0], $this->PaymentModel->get_pending_summary() ?? []);
        $pendingPayments = $this->PaymentModel->get_pending_payments(6);

        $data = [
            'status_counts' => $statusCounts,
            'patient_count' => count($patients),
            'doctor_count' => count($doctors),
            'today_count' => count($todaySchedule),
            'today_schedule' => $todaySchedule,
            'today_label' => date('l, F d, Y'),
            'revenue' => $revenue,
            'pending_summary' => $pendingSummary,
            'pending_payments' => $pendingPayments,
            'upcoming_week' => $upcomingWeek,
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
        $this->call->model('PaymentModel');

        $doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id']);
        if (!$doctor) {
            echo '<h3>No doctor record linked to your account.</h3>';
            exit;
        }
        $doctor_id = (int)$doctor['id'];

        $allAppointments = $this->AppointmentModel->all_with_details();
        $appointments = array_values(array_filter($allAppointments, function($appointment) use ($doctor_id) {
            return (int)($appointment['doctor_id'] ?? 0) === $doctor_id;
        }));

    $today = date('Y-m-d');
    $weekStartDate = date('Y-m-d', strtotime('monday this week'));
    $weekEndDate = date('Y-m-d', strtotime('sunday this week'));
    $weekStartTs = strtotime($weekStartDate . ' 00:00:00');
    $weekEndTs = strtotime($weekEndDate . ' 23:59:59');

        $todaySchedule = [];
        $pendingPayments = [];
        $recentCompleted = [];
        $uniquePatients = [];

        $stats = [
            'today_total' => 0,
            'confirmed_today' => 0,
            'pending_payments' => 0,
            'week_completed' => 0,
        ];

        $weekMap = [];
        for ($i = 0; $i < 7; $i++) {
            $dateKey = date('Y-m-d', strtotime("+$i day"));
            $weekMap[$dateKey] = [
                'date' => $dateKey,
                'label' => date('D, M d', strtotime($dateKey)),
                'total' => 0,
                'paid' => 0,
                'pending' => 0,
                'cancelled' => 0,
            ];
        }

        foreach ($appointments as $appt) {
            $slotDate = $appt['slot_date'] ?? null;
            $startTime = $appt['start_time'] ?? '00:00:00';
            $endTime = $appt['end_time'] ?? null;
            $status = strtolower(trim((string)($appt['status'] ?? 'scheduled')));
            if ($status === '') {
                $status = 'scheduled';
            }
            $paymentStatus = strtolower(trim((string)($appt['payment_status'] ?? 'pending')));
            if ($paymentStatus === '') {
                $paymentStatus = 'pending';
            }
            $amount = (float)($appt['amount'] ?? $appt['consultation_fee'] ?? 0);
            $startTs = $slotDate ? strtotime($slotDate . ' ' . $startTime) : null;

            if (!empty($appt['patient_id'])) {
                $uniquePatients[(int)$appt['patient_id']] = true;
            }

            if ($slotDate === $today && $status !== 'cancelled') {
                $stats['today_total']++;
                if (in_array($status, ['confirmed', 'completed'], true)) {
                    $stats['confirmed_today']++;
                }

                $todaySchedule[] = [
                    'time' => $this->format_slot_time($slotDate, $startTime, $endTime),
                    'patient' => $appt['patient_name'] ?? 'Patient',
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'amount' => $amount,
                    'appointment_id' => (int)$appt['id'],
                    'invoice_url' => $appt['invoice_url'] ?? null,
                    'start_ts' => $startTs ?: 0,
                    'notes' => $appt['notes'] ?? '',
                ];
            }

            if ($status === 'completed') {
                $completedTs = !empty($appt['completed_at']) ? strtotime($appt['completed_at']) : ($startTs ?: 0);
                if ($completedTs && $completedTs >= $weekStartTs && $completedTs <= $weekEndTs) {
                    $stats['week_completed']++;
                }
                $recentCompleted[] = [
                    'patient' => $appt['patient_name'] ?? 'Patient',
                    'amount' => $amount,
                    'completed_at' => $appt['completed_at'] ?? null,
                    'slot_label' => $slotDate ? date('M d, Y · h:i A', $startTs ?: time()) : '—',
                    'completed_ts' => $completedTs,
                    'payment_status' => $paymentStatus,
                ];
            }

            $isPendingPayment = !in_array($paymentStatus, ['paid', 'cancelled', 'void', 'expired', 'refunded'], true);
            if ($isPendingPayment && $status !== 'cancelled') {
                $slotLabel = 'TBD';
                if ($slotDate && $startTs) {
                    $slotLabel = date('M d, Y · h:i A', $startTs);
                } elseif ($slotDate) {
                    $slotLabel = date('M d, Y', strtotime($slotDate));
                }

                $pendingPayments[] = [
                    'patient' => $appt['patient_name'] ?? 'Patient',
                    'slot_label' => $slotLabel,
                    'amount' => $amount,
                    'payment_status' => $paymentStatus,
                    'appointment_id' => (int)$appt['id'],
                    'invoice_url' => $appt['invoice_url'] ?? null,
                    'slot_ts' => $startTs ?? PHP_INT_MAX,
                ];
            }

            if ($slotDate && isset($weekMap[$slotDate])) {
                $weekMap[$slotDate]['total']++;
                switch ($paymentStatus) {
                    case 'paid':
                        $weekMap[$slotDate]['paid']++;
                        break;
                    case 'cancelled':
                    case 'void':
                    case 'expired':
                        $weekMap[$slotDate]['cancelled']++;
                        break;
                    default:
                        $weekMap[$slotDate]['pending']++;
                        break;
                }
            }
        }

        usort($todaySchedule, function($a, $b) {
            return $a['start_ts'] <=> $b['start_ts'];
        });

        usort($pendingPayments, function($a, $b) {
            return $a['slot_ts'] <=> $b['slot_ts'];
        });
        $pendingPayments = array_slice($pendingPayments, 0, 5);

        usort($recentCompleted, function($a, $b) {
            return $b['completed_ts'] <=> $a['completed_ts'];
        });
        $recentCompleted = array_slice($recentCompleted, 0, 5);

        $stats['pending_payments'] = count($pendingPayments);

        $upcomingWeek = array_values($weekMap);

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $weekStart = $weekStartDate . ' 00:00:00';
        $weekEnd = $weekEndDate . ' 23:59:59';
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');

        $revenue = [
            'today' => $this->PaymentModel->sum_paid_between_for_doctor($doctor_id, $todayStart, $todayEnd),
            'week' => $this->PaymentModel->sum_paid_between_for_doctor($doctor_id, $weekStart, $weekEnd),
            'month' => $this->PaymentModel->sum_paid_between_for_doctor($doctor_id, $monthStart, $monthEnd),
        ];

        $data = [
            'doctor' => $doctor,
            'stats' => $stats,
            'today_schedule' => $todaySchedule,
            'today_label' => date('l, F d, Y'),
            'revenue' => $revenue,
            'pending_payments' => $pendingPayments,
            'upcoming_week' => $upcomingWeek,
            'recent_completed' => $recentCompleted,
            'total_patients' => count($uniquePatients),
            'appointment_total' => count($appointments),
        ];

        $this->call->view('/dashboard/doctor', $data);
    }



    public function staff() {
        $this->call->library('lauth');
        $this->lauth->require_login();
        $this->lauth->require_role('staff');

        $this->call->model('PatientModel');
        $this->call->model('DoctorModel');
        $this->call->model('AppointmentModel');
        $this->call->model('PaymentModel');

        $patients = $this->PatientModel->all();
        $doctors = $this->DoctorModel->all();
        $appointments = $this->AppointmentModel->all_with_details();

        $today = date('Y-m-d');
        $todayLabel = date('l, F d, Y');
        $todayTs = strtotime($today . ' 00:00:00');

        $stats = [
            'today_total' => 0,
            'confirmed_today' => 0,
            'pending_payments' => 0,
            'cancelled_today' => 0,
        ];

        $todaySchedule = [];
        $recentCompleted = [];

        $weekMap = [];
        for ($i = 0; $i < 7; $i++) {
            $dateKey = date('Y-m-d', strtotime("+$i day"));
            $weekMap[$dateKey] = [
                'date' => $dateKey,
                'label' => date('D, M d', strtotime($dateKey)),
                'total' => 0,
                'paid' => 0,
                'pending' => 0,
                'cancelled' => 0,
            ];
        }

        foreach ($appointments as $appt) {
            $slotDate = $appt['slot_date'] ?? null;
            $startTime = $appt['start_time'] ?? '00:00:00';
            $endTime = $appt['end_time'] ?? null;
            $status = strtolower(trim((string)($appt['status'] ?? 'scheduled')));
            if ($status === '') {
                $status = 'scheduled';
            }
            $paymentStatus = strtolower(trim((string)($appt['payment_status'] ?? 'pending')));
            if ($paymentStatus === '') {
                $paymentStatus = 'pending';
            }
            $amount = (float)($appt['amount'] ?? $appt['consultation_fee'] ?? 0);
            $startTs = $slotDate ? strtotime($slotDate . ' ' . $startTime) : null;

            if ($slotDate === $today) {
                $slotDateLabel = '—';
                if (!empty($slotDate)) {
                    $slotDateTs = strtotime($slotDate);
                    $slotDateLabel = $slotDateTs ? date('M d, Y', $slotDateTs) : $slotDate;
                }
                if ($status !== 'cancelled') {
                    $stats['today_total']++;
                    if (in_array($status, ['confirmed', 'completed'], true)) {
                        $stats['confirmed_today']++;
                    }
                } else {
                    $stats['cancelled_today']++;
                }

                $todaySchedule[] = [
                    'date_label' => $slotDateLabel,
                    'time' => $this->format_slot_time($slotDate, $startTime, $endTime),
                    'patient' => $appt['patient_name'] ?? 'Patient',
                    'doctor' => $appt['doctor_name'] ?? 'Doctor',
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'amount' => $amount,
                    'appointment_id' => (int)($appt['id'] ?? 0),
                    'invoice_url' => $appt['invoice_url'] ?? null,
                    'start_ts' => $startTs ?: $todayTs,
                    'notes' => $appt['notes'] ?? '',
                ];
            }

            if ($status === 'completed') {
                $completedTs = !empty($appt['completed_at']) ? strtotime($appt['completed_at']) : ($startTs ?: 0);
                $recentCompleted[] = [
                    'patient' => $appt['patient_name'] ?? 'Patient',
                    'doctor' => $appt['doctor_name'] ?? 'Doctor',
                    'amount' => $amount,
                    'completed_at' => $appt['completed_at'] ?? null,
                    'slot_label' => $slotDate ? date('M d, Y · h:i A', $startTs ?: time()) : '—',
                    'payment_status' => $paymentStatus,
                    'completed_ts' => $completedTs,
                ];
            }

            if ($slotDate && isset($weekMap[$slotDate])) {
                $weekMap[$slotDate]['total']++;
                switch ($paymentStatus) {
                    case 'paid':
                        $weekMap[$slotDate]['paid']++;
                        break;
                    case 'cancelled':
                    case 'void':
                    case 'expired':
                        $weekMap[$slotDate]['cancelled']++;
                        break;
                    default:
                        $weekMap[$slotDate]['pending']++;
                        break;
                }
            }
        }

        usort($todaySchedule, function($a, $b) {
            return $a['start_ts'] <=> $b['start_ts'];
        });

        usort($recentCompleted, function($a, $b) {
            return ($b['completed_ts'] ?? 0) <=> ($a['completed_ts'] ?? 0);
        });
        $recentCompleted = array_slice($recentCompleted, 0, 6);

        $upcomingWeek = array_values($weekMap);

        $pendingSummary = $this->PaymentModel->get_pending_summary();
        $pendingPaymentsRaw = $this->PaymentModel->get_pending_payments(6);
        $pendingPayments = array_map(function($pay) {
            $slotLabel = 'Schedule pending';
            if (!empty($pay['slot_date'])) {
                $slotTs = strtotime($pay['slot_date'] . ' ' . ($pay['start_time'] ?? '00:00:00'));
                if ($slotTs) {
                    $slotLabel = date('M d, Y · h:i A', $slotTs);
                } else {
                    $slotDateOnly = strtotime($pay['slot_date']);
                    $slotLabel = $slotDateOnly ? date('M d, Y', $slotDateOnly) : $pay['slot_date'];
                }
            } elseif (!empty($pay['created_at'])) {
                $createdTs = strtotime($pay['created_at']);
                $slotLabel = $createdTs ? date('M d, Y · h:i A', $createdTs) : $pay['created_at'];
            }

            return [
                'patient' => $pay['patient_name'] ?? 'Patient',
                'doctor' => $pay['doctor_name'] ?? 'Doctor',
                'amount' => (float)($pay['amount'] ?? 0),
                'payment_status' => strtolower($pay['status'] ?? 'pending'),
                'slot_label' => $slotLabel,
                'appointment_id' => (int)($pay['appointment_id'] ?? 0),
                'invoice_id' => $pay['invoice_id'] ?? null,
            ];
        }, $pendingPaymentsRaw);

        $stats['pending_payments'] = (int)($pendingSummary['count'] ?? 0);

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $weekEnd = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');

        $revenue = [
            'today' => $this->PaymentModel->sum_paid_between($todayStart, $todayEnd),
            'week' => $this->PaymentModel->sum_paid_between($weekStart, $weekEnd),
            'month' => $this->PaymentModel->sum_paid_between($monthStart, $monthEnd),
        ];

        $data = [
            'stats' => $stats,
            'today_schedule' => $todaySchedule,
            'today_label' => $todayLabel,
            'revenue' => $revenue,
            'pending_payments' => $pendingPayments,
            'pending_summary' => $pendingSummary,
            'upcoming_week' => $upcomingWeek,
            'recent_completed' => $recentCompleted,
            'patient_count' => count($patients),
            'doctor_count' => count($doctors),
            'appointment_total' => count($appointments),
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

    private function build_status_groups(array $appointments): array {
        $buckets = [
            'scheduled' => [],
            'completed' => [],
            'cancelled' => [],
        ];

        foreach ($appointments as $entry) {
            $status = strtolower(trim((string)($entry['status'] ?? 'scheduled')));
            if ($status === '') {
                $status = 'scheduled';
            }
            if (!array_key_exists($status, $buckets)) {
                $status = 'scheduled';
            }
            $buckets[$status][] = $entry;
        }

        $sorter = function(array $items) {
            usort($items, function($a, $b) {
                $ta = strtotime(($a['slot_date'] ?? '1970-01-01') . ' ' . ($a['start_time'] ?? '00:00:00'));
                $tb = strtotime(($b['slot_date'] ?? '1970-01-01') . ' ' . ($b['start_time'] ?? '00:00:00'));
                return $tb <=> $ta;
            });
            return $items;
        };

        foreach ($buckets as $key => $items) {
            $buckets[$key] = $sorter($items);
        }

        $counts = [
            'scheduled' => count($buckets['scheduled']),
            'completed' => count($buckets['completed']),
            'cancelled' => count($buckets['cancelled']),
        ];

        return ['items' => $buckets, 'counts' => $counts];
    }

        private function format_slot_time(?string $date, ?string $start, ?string $end): string
        {
            if (empty($date)) {
                return '—';
            }

            $startTime = !empty($start) ? $start : '00:00:00';
            $startTs = strtotime($date . ' ' . $startTime);
            if ($startTs === false) {
                return date('M d', strtotime($date));
            }

            $label = date('H:i', $startTs);
            if (!empty($end)) {
                $endTs = strtotime($date . ' ' . $end);
                if ($endTs !== false) {
                    $label .= ' - ' . date('H:i', $endTs);
                }
            }

            return $label;
        }
}