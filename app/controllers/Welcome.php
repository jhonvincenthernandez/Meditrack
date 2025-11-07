<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller {
	public function index() {
		$this->call->library('Ember');
		$this->call->view('welcome_page');
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