<?php
$key = openssl_pkey_new([
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
]);
openssl_pkey_export_to_file($key, 'private.pem');