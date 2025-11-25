<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class PaymentController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->call->library('lauth');
        $this->call->model('PaymentModel');
        $this->call->model('DoctorModel');
    }

    public function records() {
        $this->lauth->require_login();

        $context = $this->resolveRoleContext($_SESSION['role'] ?? 'admin');
        $filters = $this->buildFilters();

        $payments = $this->PaymentModel->all_with_relations($context['doctor_id'], $filters);

        $data = [
            'payments' => $payments,
            'role' => $context['role'],
            'filters' => [
                'status' => $filters['status'] ?? '',
                'from' => $filters['date_from'] ?? '',
                'to' => $filters['date_to'] ?? '',
            ],
            'status_options' => $this->statusOptions(),
        ];

        $this->call->view('/payments/records', $data);
    }

    public function export_pdf()
    {
        $this->lauth->require_login();

        $context = $this->resolveRoleContext($_SESSION['role'] ?? 'admin');
        $filters = $this->buildFilters();

        $payments = $this->PaymentModel->all_with_relations($context['doctor_id'], $filters);
        $totals = $this->summarize_payments($payments);

        $viewData = [
            'payments' => $payments,
            'filters' => [
                'status' => $filters['status'] ?? '',
                'from' => $filters['date_from'] ?? '',
                'to' => $filters['date_to'] ?? '',
            ],
            'role' => $context['role'],
            'generated_at' => date('M d, Y h:i A'),
            'totals' => $totals,
        ];

        $html = $this->render_pdf_view('payments/report_pdf', $viewData);

        report_generate([
            'title' => 'Payment Records Report',
            'html' => $html,
            'css' => $this->pdf_styles(),
            'filename' => 'payment-records-' . date('Ymd-His') . '.pdf',
        ]);
    }

    private function sanitizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function resolveRoleContext(string $role): array
    {
        $allowedRoles = ['admin', 'doctor', 'staff'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = 'admin';
        }

        $doctorId = null;

        if ($role === 'doctor') {
            $this->lauth->require_role('doctor');
            $doctor = $this->DoctorModel->find_by_user_id($_SESSION['user_id'] ?? 0);
            if (!$doctor) {
                exit('Doctor profile not linked to your account.');
            }
            $doctorId = (int) $doctor['id'];
        } elseif ($role === 'staff') {
            $this->lauth->require_role('staff');
        } else {
            $this->lauth->require_role('admin');
        }

        return [
            'role' => $role,
            'doctor_id' => $doctorId,
        ];
    }

    private function buildFilters(): array
    {
        $rawStatus = strtolower(trim($_GET['status'] ?? ''));
        $statusFilter = in_array($rawStatus, $this->statusOptions(), true) ? $rawStatus : '';

        $dateFromSanitized = $this->sanitizeDate($_GET['from'] ?? '');
        $dateToSanitized = $this->sanitizeDate($_GET['to'] ?? '');

        return [
            'status' => $statusFilter ?: null,
            'date_from' => $dateFromSanitized,
            'date_to' => $dateToSanitized,
        ];
    }

    private function statusOptions(): array
    {
        return ['pending','paid','expired','failed','cancelled','canceled','voided','refunded'];
    }

    private function render_pdf_view(string $view, array $data = []): string
    {
        $normalized = ltrim($view, '/');
        $path = APP_DIR . 'views/' . $normalized;
        if (substr($path, -4) !== '.php') {
            $path .= '.php';
        }

        if (!is_file($path)) {
            throw new RuntimeException("PDF view {$view} not found");
        }

        ob_start();
        extract($data, EXTR_SKIP);
        require $path;
        return ob_get_clean();
    }

    private function summarize_payments(array $payments): array
    {
        $totalAmount = 0;
        $statusBreakdown = [];

        foreach ($payments as $payment) {
            $totalAmount += (float)($payment['amount'] ?? 0);
            $status = strtolower($payment['status'] ?? 'pending');
            $statusBreakdown[$status] = ($statusBreakdown[$status] ?? 0) + 1;
        }

        ksort($statusBreakdown);

        return [
            'count' => count($payments),
            'amount' => $totalAmount,
            'status_breakdown' => $statusBreakdown,
        ];
    }

    private function pdf_styles(): string
    {
        return <<<'CSS'
body { font-family: "Inter", sans-serif; font-size: 12px; color: #1f2937; }
.report-header { text-align: center; margin-bottom: 20px; }
.report-header h1 { margin: 0; font-size: 20px; color: #111827; }
.report-meta { font-size: 11px; color: #6b7280; }
.summary-grid { display: flex; gap: 12px; margin-bottom: 18px; }
.summary-card { flex: 1; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; background: #f9fafb; }
.summary-card h3 { margin: 0 0 6px; font-size: 13px; text-transform: uppercase; color: #6b7280; }
.summary-card strong { font-size: 18px; color: #111827; }
table { width: 100%; border-collapse: collapse; }
thead { background: #f3f4f6; }
th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }
tbody tr:nth-child(odd) { background: #fcfcfd; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; text-transform: uppercase; }
.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-neutral { background: #e5e7eb; color: #374151; }
.status-table { margin-top: 10px; border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden; }
.status-table table { border: none; }
.status-table th, .status-table td { border: none; border-bottom: 1px solid #f3f4f6; }
.status-table tr:last-child td { border-bottom: none; }
CSS;
    }
}
