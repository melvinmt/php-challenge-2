<?php
// Jeff Mastin
// jeff@typhip.com

function parse_request($request, $secret)
{
    // use regex to verify data and pull out signature/payload
    $request = strtr($request, '-_', '+/');
    $pattern = '/^([\w\d+\/=]+)\.([\w\d+\/=]+)$/';
    $matches = array();
    if(!preg_match($pattern, $request, $matches)) {
        return false;
    }

    // decode signature and payload
    $signature = base64_decode($matches[1]);
    $payload = base64_decode($matches[2]);

    // check signature
    if($signature != hash_hmac('sha256', $payload, $secret)) {
        return false;
    }

    // all check's pass and key matches, return payload
    return json_decode($payload, true);
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
