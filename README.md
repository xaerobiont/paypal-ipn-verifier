PHP PayPal IPN Verifier
=======================

Usage
-----

<?php

  public function yourIpnListener(){
    $request = $_POST; // get paypal post request
    $iv = new IpnVerifier();
    $iv->sandbox = true; // false by default
    // if you want to log process
    $iv->log = true; // false by default
    $iv->log_file = '/absolute/path/to/writable/log/file.log';
    $verified = $iv->verify($request);
  }

?>
