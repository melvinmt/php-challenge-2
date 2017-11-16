<?php
// Daniel Morgan <dan@danmorgan.net>


function parse_request($request, $secret)
{
	//Quick sanity check
	if (substr_count($request,'.') > 2) return false;
	//Split request into encoded signature/payload
	list ($signatureEncoded, $payloadEncoded) = explode('.', $request);
	//Decode signature and payload
	$signature = base64_decode($signatureEncoded);
	$payload = base64_decode($payloadEncoded);
	//SHA256 payload against shared secret, match against signature
	if (hash_hmac('sha256', $payload, $secret, false) == $signature) return json_decode($payload, true);
	return false;
}

function dates_with_at_least_n_scores($pdo, $n)
{
	// Count the number of records for a given date, compare against N
	$sql = "
		SELECT 
			date
		FROM 
			scores
		GROUP BY 
			date
		HAVING 
			COUNT(date) >= $n 
		ORDER BY 
			date DESC
	";
	return $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN, 0);
}

function users_with_top_score_on_date($pdo, $date)
{
	//Find user and score for all scores matching top score on given date
	$sql = "
		SELECT 
			user_id,
			score
		FROM 
			scores
		WHERE 
			score = (
				SELECT
					MAX(score)
				FROM
					scores
				WHERE date = '$date'
			)
		AND
			date = '$date' 
	";
	return $pdo->query($sql)->fetchALL(PDO::FETCH_COLUMN, 0);
}

function dates_when_user_was_in_top_n($pdo, $user_id, $n)
{
	// Query a subset per each date, counting how many scores are greater than the other subset of records, This generates a 'rank' which we can use later in our where clause of our outer query
	$sql = "
		SELECT
			DISTINCT s1.date
		FROM
			scores as s1
		WHERE
			user_id = $user_id
		AND
			(
				SELECT 
					COUNT(*)
				FROM 
					scores as s2
				WHERE 
					s2.score > s1.score
				AND
					s1.date = s2.date
			)+1 <= $n 
		ORDER BY
			date DESC
	";
	$query = $pdo->query($sql);
	$results = $query->fetchAll(PDO::FETCH_COLUMN, 0);
	return $results;
}
