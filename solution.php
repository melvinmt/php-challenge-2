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
        SELECT date
        FROM scores
        GROUP BY date
        HAVING COUNT(date) >= ?
        ORDER BY date DESC
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
    // query to grab data
    $sql = "
        SELECT
            user_id,
            score,
            date
        FROM scores
        ORDER BY date, score DESC;
    ";
    
    $statement = $pdo->query($sql);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    // create a ranked set from the results.
    // NOTE: this could be cached to avoid redundant queries
    // depending on params
    $ranking = array();

    foreach($results as $row)
    {
        // make sure the date exists in the ranking
        if(!key_exists($row['date'], $ranking))
            $ranking[$row['date']] = array();

        // simplify referencing the date
        $rank_date = &$ranking[$row['date']];

        // does the score exist?
        if(!key_exists($row['score'], $rank_date))
            $rank_date[$row['score']] = array();
        
        // add the user to the proper rank
        $rank_date[$row['score']][] = $row['user_id'];
    }

    // check date's that meet the parameters in the ranking
    $results = array();
    foreach($ranking as $date => $ranks)
    {
        // values to prep ranking
        $current_rank = 0;
        foreach($ranks as $rank => $users)
        {
            // matches, add value
            if(in_array($user_id, $users)) {
                if($current_rank < $n) {
                    $results[] = $date;
                }
            }
            else
            {
                // make sure we account for dupe users
                $current_rank += count($users);
            }
        }
    }
    // put dates in descending order
    rsort($results);

    return $results;
}
