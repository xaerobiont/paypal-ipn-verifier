PHP PayPal IPN Verifier
-----------------------

Simple IPN verifier via cURL with log option

Usage
-----

    public function yourIpnListener(){
        
      $request = $_POST; // get paypal post request
      $iv = new IpnVerifier();
      $iv->sandbox = true; // false by default
      // if you want to log process
      $iv->log = true; // false by default
      $iv->log_file = '/absolute/path/to/writable/log/file.log';
      $verified = $iv->verify($request);
        
    }

Paypal IPN simulator:
https://developer.paypal.com/webapps/developer/applications/ipn_simulator
