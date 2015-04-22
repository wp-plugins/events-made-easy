<?php

function eme_generate_worldpay_signature($worldpay_md5,$params_arr,$instId,$cartId,$currency,$amount) {
    $defaultSignatureFields = ['instId', 'cartId', 'currency', 'amount'];
    $defaults = [
    'instId' => $instId,
    'cartId' => $cartId,
    'currency' => $currency,
    'amount' => $amount
    ];
    $parameters = array_intersect_key($params_arr, array_flip($defaultSignatureFields));
    return md5((string) $worldpay_md5.':'.implode(':', array_merge($defaults, $parameters)));
}

?>
