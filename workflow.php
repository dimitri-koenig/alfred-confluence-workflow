<?php

require_once('workflows-library.php');
require_once('helper-functions.php');

$config = (require_once 'config.php');

if ($config['useLocalKeychain'])
{
	$config = array_merge($config, getCredentialsFromLocalKeychain());
}

$options = [
	CURLOPT_USERPWD => $config['username'] . ':' . $config['password']
];

try
{
	$wf = new Workflows();

	$response = $wf->request($config['hostUrl'] . '/rest/searchv3/latest/search?queryString=' . urlencode($input), $options);
	$jsonResponse = json_decode($response);

	if ($jsonResponse->errorMessages)
	{
		foreach ($jsonResponse->errorMessages as $errorMessage)
		{
			$wf->result('confluence-response-error', $input, 'Error message', $errorMessage, 'icon.png');
		}
	}

	if ($jsonResponse->total === 0)
	{
		$wf->result('confluence-no-results', $input, 'No Suggestions', 'No search suggestions for "' . $input . '" found', 'icon.png');
	}

	if ($jsonResponse->total > 0)
	{
		foreach ($jsonResponse->results as $result)
		{
			$wf->result('confluence-' . $result->id, $config['hostUrl'] . $result->url, removeHighlight($result->title), $result->friendlyDate . ' | ' . removeHighlight($result->bodyTextHighlights), '');
		}
	}
}
catch (Exception $e)
{
	$wf->result('confluence-request-error', $input, 'Search Request Error', 'Error when searching for "' . $searchWords, 'icon.png');
}

echo $wf->toxml();
