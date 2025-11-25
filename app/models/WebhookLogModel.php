<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class WebhookLogModel extends Model {
    protected $table = 'xendit_webhook_logs';
    protected $primary_key = 'id';

    public function log_event(array $data)
    {
        $defaults = [
            'invoice_id' => null,
            'event' => null,
            'status' => null,
            'raw_payload' => null,
        ];

        return $this->insert(array_merge($defaults, $data));
    }
}
