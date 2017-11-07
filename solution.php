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
    $sql = "
    SELECT `date`
    FROM scores s
    GROUP BY s.`date`
    HAVING count(s.`date`) >= :num
    ORDER BY s.`date` DESC
    ";

    $query = $pdo->prepare($sql);
    $query->bindParam(':num', $n, PDO::PARAM_INT);
    $query->execute();

    $results = $query->fetchAll(PDO::FETCH_COLUMN, 'date');

    return $results;
}

function users_with_top_score_on_date($pdo, $date)
{
    $subsql = "
    SELECT max(score)
    FROM scores s
    WHERE s.`date` = :date
    ";

    $sql = "
    SELECT s.`user_id`
    FROM scores s 
    WHERE s.`score` = ($subsql)
    AND s.`date` = :date
    ";

    $query = $pdo->prepare($sql);
    $query->bindParam(':date', $date, PDO::PARAM_STR);
    $query->execute();

    $results = $query->fetchAll(PDO::FETCH_COLUMN, 'user_id');

    return $results;
}

function dates_when_user_was_in_top_n($pdo, $user_id, $n)
{
    // YOUR CODE GOES HERE
}
