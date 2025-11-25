<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Helper: common_helper.php
 * 
 * Automatically generated via CLI.
 */

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function report_generate(array $options = []): void
{
    $defaults = [
        'title' => 'MediTrack+ Report',
        'html' => '<p>No data provided.</p>',
        'css' => '',
        'filename' => 'report.pdf',
        'download' => true,
        'orientation' => 'P',
        'format' => 'A4',
    ];

    $config = array_merge($defaults, $options);

    $mpdf = new \Mpdf\Mpdf([
        'format' => $config['format'],
        'orientation' => $config['orientation'],
    ]);

    $mpdf->SetTitle($config['title']);

    if (!empty($config['css'])) {
        $mpdf->WriteHTML($config['css'], \Mpdf\HTMLParserMode::HEADER_CSS);
    }

    $mpdf->WriteHTML($config['html'], \Mpdf\HTMLParserMode::HTML_BODY);

    $destination = $config['download'] ? 'D' : 'I';
    $mpdf->Output($config['filename'], $destination);
    exit;
}

function sendMail($to, $subject, $messageBody)
{
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'jhonvincenthernandez9@gmail.com';                     //SMTP username
        $mail->Password   = 'sxsg obrz zkra iaqx';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('jhonvincenthernandez9@gmail.com', 'Meditrack Admin');
        $mail->addAddress($to);     //Add a recipient
        

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $messageBody;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function format_msisdn(?string $number): ?string
{
    if (empty($number)) {
        return null;
    }
    $digits = preg_replace('/[^0-9]/', '', $number);
    if ($digits === '') {
        return null;
    }
    if (strpos($digits, '63') === 0) {
        return '+' . $digits;
    }
    if ($digits[0] === '0') {
        $digits = '63' . substr($digits, 1);
    }
    return '+' . $digits;
}

function xendit_settings(string $key = null)
{
    $settings = config_item('xendit') ?: [];
    if ($key === null) {
        return $settings;
    }
    return $settings[$key] ?? null;
}

function xendit_http_client(array $options = []): Client
{
    $base = [
        'base_uri' => xendit_settings('base_url') ?? 'https://api.xendit.co',
        'timeout' => 20,
        'verify' => !(xendit_settings('skip_ssl') ?? false),
        'auth' => [xendit_settings('secret_key'), ''],
    ];

    return new Client(array_merge($base, $options));
}

function xendit_create_invoice(array $payload): array
{
    $client = xendit_http_client();
    try {
        $response = $client->post('/v2/invoices', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    } catch (RequestException $e) {
        $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
        throw new RuntimeException('Xendit invoice error: ' . $body, 0, $e);
    }
}

function xendit_get_invoice(string $invoice_id): array
{
    $client = xendit_http_client();
    try {
        $response = $client->get('/v2/invoices/' . $invoice_id, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    } catch (RequestException $e) {
        $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
        throw new RuntimeException('Unable to fetch invoice: ' . $body, 0, $e);
    }
}

function xendit_expire_invoice(string $invoice_id): array
{
    $client = xendit_http_client();
    try {
        $response = $client->post('/v2/invoices/' . $invoice_id . '/expire!', [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    } catch (RequestException $e) {
        $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
        throw new RuntimeException('Unable to expire invoice: ' . $body, 0, $e);
    }
}

function xendit_verify_callback_token(?string $token): bool
{
    $expected = xendit_settings('callback_token');
    if (empty($expected)) {
        return false;
    }
    $incoming = $token ?? '';
    return hash_equals($expected, $incoming);
}
