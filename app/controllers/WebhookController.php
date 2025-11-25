<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class WebhookController extends Controller {
    public function __construct()
    {
        parent::__construct();
        $this->call->model('AppointmentModel');
        $this->call->model('PaymentModel');
        $this->call->model('WebhookLogModel');
        $this->call->model('ScheduleModel');
    }

    public function xendit_invoice()
    {
        header('Content-Type: application/json');
        $token = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? ($_SERVER['HTTP_X_CALLBACKTOKEN'] ?? '');
        if (!xendit_verify_callback_token($token)) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid callback token']);
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!$payload) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid payload']);
            return;
        }

        $event = $payload['event'] ?? ($payload['event_type'] ?? '');
        $data = $payload['data'] ?? $payload;
        $invoice_id = $data['id'] ?? null;
        if (!$invoice_id) {
            http_response_code(422);
            echo json_encode(['message' => 'Invoice ID missing']);
            return;
        }

        $status = strtoupper($data['status'] ?? '');
        $paid_at = !empty($data['paid_at']) ? date('Y-m-d H:i:s', strtotime($data['paid_at'])) : null;
        $payment_channel = $data['payment_method'] ?? ($data['payment_channel'] ?? null);

        $this->WebhookLogModel->log_event([
            'invoice_id' => $invoice_id,
            'event' => $event,
            'status' => $status,
            'raw_payload' => json_encode($payload),
        ]);

        $this->PaymentModel->update_by_invoice($invoice_id, [
            'status' => strtolower($status ?: 'pending'),
            'payment_channel' => $payment_channel,
            'failure_reason' => $data['failure_reason'] ?? null,
            'paid_at' => $paid_at,
            'raw_payload' => json_encode($payload),
        ]);

        $appointment = $this->AppointmentModel->find_by_invoice($invoice_id);
        if ($appointment) {
            $successStatuses = ['PAID','SETTLED'];
            $failedStatuses = ['EXPIRED','VOIDED','FAILED','CANCELED'];

            if (in_array($status, $successStatuses, true)) {
                $this->AppointmentModel->update_payment_state_by_invoice($invoice_id, [
                    'payment_status' => 'paid',
                    'xendit_status' => $status,
                    'paid_at' => $paid_at ?? date('Y-m-d H:i:s'),
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                ]);
            } elseif (in_array($status, $failedStatuses, true)) {
                $this->AppointmentModel->update_payment_state_by_invoice($invoice_id, [
                    'payment_status' => strtolower($status),
                    'xendit_status' => $status,
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Payment ' . strtolower($status),
                    'cancelled_at' => date('Y-m-d H:i:s'),
                ]);
                if (!empty($appointment['slot_id'])) {
                    $this->ScheduleModel->set_booked($appointment['slot_id'], 0);
                }
            }
        }

        echo json_encode(['success' => true]);
    }
}
