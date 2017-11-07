<?php
// YOUR NAME AND EMAIL GO HERE
// Jonathan Hao - jhao19@gmail.com

function parse_request($request, $secret)
{
    list($signatureHash, $payloadHash) = explode('.',$request);

    $hashedSignatureFromRequest = base64_decode($signatureHash);
    $payloadDecoded = base64_decode($payloadHash);
    $payload = json_decode($payloadDecoded, true);

    $realHashedSignature = hash_hmac('sha256', $payloadDecoded, $secret);

    if ($hashedSignatureFromRequest == $realHashedSignature) {
        return $payload;
    } else {
        return false;
    }
}

function dates_with_at_least_n_scores($pdo, $n)
{
    // YOUR CODE GOES HERE
}

function users_with_top_score_on_date($pdo, $date)
{
    // YOUR CODE GOES HERE
}

function dates_when_user_was_in_top_n($pdo, $user_id, $n)
{
    // YOUR CODE GOES HERE
}
