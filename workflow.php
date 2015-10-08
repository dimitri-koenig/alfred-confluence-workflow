<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('UTC');

require_once('workflows-library.php');
require_once('helper-functions.php');

$wf = new Workflows();

$config = (require_once 'config.php');

if ($config['useLocalKeychain']) {
    $config = array_merge($config, getCredentialsFromLocalKeychain());
}

if (empty($config['username']) || empty($config['password'])) {
    $wf->result('confluence-auth-error', '', 'Auth config incomplete', '', 'icon.png');
    echo $wf->toxml();
    die('');
}

$options = array(
    CURLOPT_USERPWD => $config['username'] . ':' . $config['password']
);

if (!isset($mode)) {
    $mode = 'search';
}

try {
    if ($mode === 'search') {
        $response = $wf->request($config['hostUrl'] . '/rest/searchv3/latest/search?queryString=' . rawurlencode(utf8_encode($input)), $options);
        $jsonResponse = json_decode($response);

        if (!is_object($jsonResponse)) {
            throw new Exception($response);
        }

        if (isset($jsonResponse->errorMessages)) {
            foreach ($jsonResponse->errorMessages as $errorMessage) {
                $wf->result('confluence-response-error', $input, 'Error message', $errorMessage, 'icon.png');
            }
        }

        if ($jsonResponse->total === 0) {
            $wf->result('confluence-no-results', $input, 'No Suggestions', 'No search suggestions for "' . $input . '" found', 'icon.png');
        }

        if ($jsonResponse->total > 0) {
            foreach ($jsonResponse->results as $result) {
                $wf->result('confluence-' . $result->id, $config['hostUrl'] . $result->url, removeHighlight($result->title), sprintf('%s | %s | %s', $result->searchResultContainer->name, $result->friendlyDate, removeHighlight($result->bodyTextHighlights)), '');
            }
        }
    }
    if ($mode === 'recently-viewed') {
        $response = $wf->request($config['hostUrl'] . '/rest/recentlyviewed/latest/recent', $options);
        $jsonResponse = json_decode($response);

        if (isset($jsonResponse->errorMessages)) {
            foreach ($jsonResponse->errorMessages as $errorMessage) {
                $wf->result('confluence-response-error', $input, 'Error message', $errorMessage, 'icon.png');
            }
        }

        if (is_array($jsonResponse)) {
            foreach ($jsonResponse as $result) {
                $wf->result(sprintf('confluence-%s-%s', $result->id, $result->lastSeen), $config['hostUrl'] . $result->url, $result->title, sprintf('%s - Space: %s', date('H:i d.m.', intval($result->lastSeen/1000)), $result->space), '');
            }
        }
    }
} catch (Exception $e) {
    $wf->result('confluence-request-error', $input, 'Search Request Error', strip_tags($e->getMessage()), 'icon.png');
}

echo $wf->toxml();
