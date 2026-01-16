<?
function syncMailchimp($data) {
	$apiKey = '----------------';
	$listId = '----------------';

	$memberId = md5(strtolower($data['email']));
	$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
	$url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/';

	$json = json_encode([
		'email_address' => $data['email'],
		'status'        => $data['status'], // "subscribed","unsubscribed","cleaned","pending"
		// 'merge_fields'  => [
		// 	'FNAME'     => $data['firstname'],
		// 	'LNAME'     => $data['lastname']
		// ]
	]);

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
	/*
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	*/
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

	ob_start();
		$result = curl_exec($ch);
	$answer = @ob_get_contents();
	ob_get_clean();

	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return array(
		"HTTP_CODE" => $httpCode,
		"result" => $result,
		"DATA" => $answer
	);
}
?>