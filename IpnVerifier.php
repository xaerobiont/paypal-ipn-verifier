<?php
    /**
     *  PayPal IPN Verifier class
     *
     *  Verify PayPal IPN request via CURL
     *
     * @author     Klyukin Dmitry aka Zvook
     * @copyright  2014 Zvook
     * @version    1.0
     */
    class IpnVerifier{

        public $verify_url = 'https://www.paypal.com/cgi-bin/webscr/';
        public $sandbox_verify_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr/';

        /**
         * User PayPal sandbox
         * @var bool
         */
        public $sandbox = false;

        /**
         * Enable/Disable logging
         * @var bool
         */
        public $log = false;

        /**
         * Absolute path to log file
         * @var string
         */
        public $log_file = '';

        /**
         * POST data from PayPal
         * @var
         */
        private $request_data;

        /**
         * Verify IPN request
         * Returns true if request is verified, false if not
         * @param $post_data
         * @return bool
         * @throws Exception
         */
        public function verify($post_data){

            if (is_array($post_data) && !empty($post_data)){
                $this->request_data = $post_data;
            } else {
                throw new Exception('Invalid input parameters');
            }

            $url = $this->sandbox ? $this->sandbox_verify_url : $this->verify_url;
            $data = 'cmd=_notify-validate';

            foreach ($this->request_data as $name => $value){
                $data .= '&' . $name . '=' . urlencode($value);
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_TIMEOUT, 50);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($curl);
            $info = curl_getinfo($curl);

            if ($response === false) throw new Exception('CURL error ' . curl_errno($curl) . ': "' . curl_error($curl) . '"');

            $verified = 'UNEXPECTED RESPONSE';
            if (strpos($response, "VERIFIED") !== false){
                $verified = 'VERIFIED';
            } elseif (strpos($response, "INVALID") !== false) {
                $verified = 'INVALID';
            }

            if ($this->log) $this->log($this->request_data, $info['http_code'], $verified);

            return $verified == 'VERIFIED' ? true : false;
        }

        /**
         * Log writer
         * @param array $request
         * @param $response_code
         * @param $result
         */
        private function log(array $request, $response_code, $result){
            $row = date('Y-m-d H:i:s') . ':' . PHP_EOL;
            $row .= '--------------------------------------' . PHP_EOL;
            $row .= 'PayPal request:' . PHP_EOL;
            $row .= '---------------' . PHP_EOL;
            foreach ($request as $name => $value){
                $row .= $name . ': ' . $value . PHP_EOL;
            }
            $row .= '---------------' . PHP_EOL;
            $row .= 'Verifying response:' . PHP_EOL;
            $row .= '---------------' . PHP_EOL;
            $row .= 'HTTP code: ' . $response_code . PHP_EOL;
            $row .= 'Verifying result: ' . $result . PHP_EOL;
            $row .= '**************************************' . PHP_EOL;
            file_put_contents($this->log_file, $row, FILE_APPEND);
        }
    }