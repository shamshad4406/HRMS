<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ZohoMailService
{
    private $apiKey;
    private $fromEmail;
    private $fromAlias;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('ZOHO_MAIL_API_KEY', 'Zoho-enczapikey PHtE6r1ZS7u/2TUm9UQI4vXsEJTyZN4qr+M0LlYT5dxFC/4GFk0A/t0rm2S0qxt7XaZKHPDPnt8+uLict+qCIW/uMz1MWGqyqK3sx/VYSPOZsbq6x00Yt10fdEXfVYTndtdj0CbfvtbSNA==');
        $this->fromEmail = env('ZOHO_MAIL_FROM', 'support@innobles.com');
        $this->fromAlias = env('ZOHO_MAIL_FROM_ALIAS', 'Erent');
        $this->apiUrl = env('ZOHO_API_URL', 'https://api.zeptomail.in/v1.1/email');
    }

    /**
     * Send email using Zoho Mail API
     *
     * @param string $to
     * @param string $subject
     * @param string $html
     * @return bool
     */
    public function sendEmail($to, $subject, $html)
    {
        if (!$to || !$subject || !$html) {
            return false;
        }

        if (!$this->apiKey || !$this->fromEmail || !$this->fromAlias) {
            return false;
        }

        $params = [
            'from' => $this->fromAlias . ' <' . $this->fromEmail . '>',
            'to' => $to,
            'subject' => $subject,
            'html' => $html
        ];

        try {
            $result = $this->sendEmailZoho($params);
            return $result ? true : false;
        } catch (\Exception $e) {
            Log::error('Zoho Mail Error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Send email via Zoho API
     *
     * @param array $payload
     * @return mixed
     */
    private function sendEmailZoho($payload)
    {
        $headers = [
            "accept: application/json",
            "authorization:" . $this->apiKey,
            "cache-control: no-cache",
            "content-type: application/json"
        ];

        $data = [];

        // Set from email param
        $from = [];
        if (preg_match('/^(.*?)\s*<(.+?)>$/', $payload['from'], $matches)) {
            $from = [
                'name' => trim($matches[1]),
                'address' => trim($matches[2])
            ];
        }
        $data['from'] = $from;

        // Set to email param
        $to_array = [];
        $to = explode(',', $payload['to']);

        foreach ($to as $to_val) {
            $to_array[]['email_address'] = ['address' => trim($to_val)];
        }

        $data['to'] = $to_array;

        // Set cc email param
        if (isset($payload['cc'])) {
            $cc_array = [];
            $cc = explode(',', $payload['cc']);
            foreach ($cc as $cc_val) {
                $cc_array[]['email_address'] = ['address' => trim($cc_val)];
            }
            $data['cc'] = $cc_array;
        }

        // Set bcc email param
        if (isset($payload['bcc'])) {
            $bcc_array = [];
            $bcc = explode(',', $payload['bcc']);
            foreach ($bcc as $bcc_val) {
                $bcc_array[]['email_address'] = ['address' => trim($bcc_val)];
            }
            $data['bcc'] = $bcc_array;
        }

        // Set subject and html body email param
        $data['subject'] = $payload['subject'];
        $data['htmlbody'] = $payload['html'];

        // Check if there any attachment
        if (isset($payload['attachment']) && !empty($payload['attachment'])) {
            $attachments_array = [];
            foreach ($payload['attachment'] as $akey => $file) {
                $url = $file['filePath'];
                $filename = $file['filename'];

                $content = file_get_contents($url);

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($content);

                $attachments_array[] = [
                    'name' => $filename,
                    'content' => base64_encode($content),
                    'mime_type' => $mimeType
                ];
            }

            $data['attachments'] = $attachments_array;
        }

        // Send with cURL
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::error('cURL Error: ' . $err);
            return false;
        }

        return $response;
    }
}
