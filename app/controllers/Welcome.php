<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use GuzzleHttp\Client;

class Welcome extends Controller {
	public function index() {
		Configuration::setXenditKey('xnd_development_CsB8on6R23wYLyCSQYw2ngABh0DrKXJKcSDlVGSyTGKS935mYPKrOUjlz6ytgR');

		// Local dev SSL bypass
        $guzzleClient = new Client(['verify' => false]);

		$apiInstance = new InvoiceApi($guzzleClient);

		$create_invoice_request = new Xendit\Invoice\CreateInvoiceRequest([
		'external_id' => 'test1234',
		'description' => 'Test Invoice',
		'amount' => 10000,
		'invoice_duration' => 172800,
		'currency' => 'PHP',
		'reminder_time' => 1
		]); 
		try {
			$result = $apiInstance->createInvoice($create_invoice_request, null);
				print_r($result);
		} catch (\Xendit\XenditSdkException $e) {
			echo 'Exception when calling InvoiceApi->createInvoice: ', $e->getMessage(), PHP_EOL;
			echo 'Full Error: ', json_encode($e->getFullError()), PHP_EOL;
		}
	}

	public function testEmail()
	{
		if (sendMail('jhonvincenthernandez1@gmail.com', 'MediTrack Test', 'This is a test email from LavaLust MediTrack+')) {
			echo "✅ Email sent successfully!";
			exit;
		} 
	}
}
?>