<?php

require_once('workflows-library.php');

$config = (require_once 'config.php');

if ($config['useLocalKeychain']) {
	exec('security find-internet-password -j "' . $_ENV['alfred_workflow_bundleid'] . '" -g 2>&1; echo $?', $keychainData);

	$protocol = '';
	$server = '';
	foreach ($keychainData as $singleLine) {
		if (stripos($singleLine, '"acct"') !== FALSE) {
			$config['username'] = preg_replace('/^.*"([^"]+)"\w*$/', '$1', $singleLine);
			continue;
		}
		if (stripos($singleLine, 'password:') !== FALSE) {
			$config['password'] = preg_replace('/^.*"([^"]+)"\w*$/', '$1', $singleLine);
			continue;
		}
		if (stripos($singleLine, '"ptcl"') !== FALSE) {
			$protocol = preg_replace('/^.*"([^"]+)"\w*$/', '$1', $singleLine);
			continue;
		}
		if (stripos($singleLine, '"srvr"') !== FALSE) {
			$server = preg_replace('/^.*"([^"]+)"\w*$/', '$1', $singleLine);
			continue;
		}
	}
	$config['hostUrl'] = ($protocol === 'htps' ? 'https://' : 'http://') . $server;
}

$options = array(
	CURLOPT_USERPWD => $config['username'] . ':' . $config['password']
);

try {
	$wf = new Workflows();

	$response = $wf->request($config['hostUrl'] . '/rest/searchv3/latest/search?queryString=' . urlencode($input), $options);
	$jsonResponse = json_decode($response);

	if ($jsonResponse->errorMessages) {
		foreach ($jsonResponse->errorMessages as $errorMessage) {
			$wf->result('confluence-response-error', $input, 'Error message', $errorMessage, 'icon.png');
		}
	}

	if ($jsonResponse->total === 0) {
		$wf->result('confluence-no-results', $input, 'No Suggestions', 'No search suggestions for "' . $input . '" found', 'icon.png');
	}

	if ($jsonResponse->total > 0) {
		foreach ($jsonResponse->results as $result) {
			$wf->result('confluence-' . $result->id, $config['hostUrl'] . $result->url, removeHighlight($result->title), $result->friendlyDate . ' | ' . removeHighlight($result->bodyTextHighlights), '');
		}
	}
} catch (Exception $e) {
	$wf->result('confluence-request-error', $input, 'Search Request Error', 'Error when searching for "' . $searchWords, 'icon.png');
}

echo $wf->toxml();


function removeHighlight($input) {
	return preg_replace('/@@@[^@]+@@@(.*)@@@[^@]+@@@/Uis', '$1', $input);
}

?>