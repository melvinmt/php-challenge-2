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
    $sql = "
    SELECT `date`, `user_id`, `score`
    FROM scores s
    ORDER BY `score` DESC
    ";

    $query = $pdo->prepare($sql);
    $query->execute();

    $results = $query->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

    $dates = array();

    foreach ($results as $date => $userInfos) {
        $topN = getTopNUsers($userInfos, $n);

        if (in_array($user_id, $topN)) {
            $dates[] = $date;
        }
    }
    sort($dates);

    return array_reverse($dates);
}

function getTopNUsers($userInfos, $n) {
    $users = array();

    // get list of unique scores (should already be sorted from high to low)
    $scores = array_unique(
        array_map(
            function($userInfo) {
                return $userInfo['score'];
            },
            $userInfos
        )
    );

    // keep getting users by descending score, as long as number of users found is < n
    foreach ($scores as $score) {
        if (count($users) >= $n) {
            break;
        }

        $usersWithScore = array_filter(
            $userInfos,
            function($userInfo) use ($score) {
                return ($userInfo['score'] == $score);
            }
        );

        foreach ($usersWithScore as $userWithScore) {
            $users[] = $userWithScore['user_id'];
        }
    }

    return $users;
}
