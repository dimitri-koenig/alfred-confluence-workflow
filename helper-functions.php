<?php

function getBundleId()
{
    return isset($_ENV['alfred_workflow_bundleid']) ? $_ENV['alfred_workflow_bundleid'] : $_SERVER['alfred_workflow_bundleid'];
}

function getCredentialsFromLocalKeychain()
{
    $keychainData = shell_exec('security find-internet-password -j "' . getBundleId() . '" -g 2>&1; echo $?');

    $protocol = findValue('/"ptcl".*"([^"]+)"\W*/Uis', $keychainData);
    $server = findValue('/"srvr".*"([^"]+)"\W*/Uis', $keychainData);

    $config = array(
        'hostUrl'  => ($protocol === 'htps' ? 'https://' : 'http://') . $server,
        'username' => findValue('/"acct".*"([^"]+)"\W*/Uis', $keychainData),
        'password' => findValue('/password:\W*"([^"]+)"/', $keychainData)
    );

    return $config;
}

function findValue($pattern, $haystack)
{
    $matches = array();
    preg_match($pattern, $haystack, $matches);

    return count($matches) ? trim(array_pop($matches)) : null;
}

function removeHighlight($input)
{
    return preg_replace('/@@@[^@]+@@@(.*)@@@[^@]+@@@/Uis', '$1', $input);
}
