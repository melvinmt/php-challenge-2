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
    // Prepare SQL statement with query for dates with >n score
    /*$sql = '
        SELECT
            date
        FROM
            scores
        GROUP BY
            date
        HAVING
            COUNT(date) >= ?
        ORDER BY
            date DESC
    ';
    
    // execute statement and return results
    $statement->execute(array($n));*/

    // prepared statements don't seem to work on sqlite? use query
    $sql = "
        SELECT date
        FROM scores
        GROUP BY date
        HAVING COUNT(date) >= $n
        ORDER BY date DESC
    ";

    // get the statement, then return the results flattened
    $statement = $pdo->query($sql);
    $results = $statement->fetchAll(PDO::FETCH_COLUMN);
    return $results;
}

function users_with_top_score_on_date($pdo, $date)
{
    // subquery to disambiguate multiple user's with the same score
    $subsql = "
        SELECT MAX(score)
        FROM scores
        WHERE date = '$date'
    ";
    // main sql query
    $sql = "
        SELECT user_id
        FROM scores
        WHERE
            score = ($subsql)
            AND date = '$date'
    ";
    
    // get the statement, then return the results flattened
    $statement = $pdo->query($sql);
    $results = $statement->fetchAll(PDO::FETCH_COLUMN);
    return $results;
}

function dates_when_user_was_in_top_n($pdo, $user_id, $n)
{
    // YOUR CODE GOES HERE
}
