<?php

function echo_line($line)
{
    global $GLOBAL_LOG_LEVEL;
    if ($GLOBAL_LOG_LEVEL == 'debug') {
        echo $line . "\n";
    }
}

function getParams($keys)
{
    $r = $_GET;
    $params = [];
    foreach ($keys as $k) {
        if (isset($r[$k])) {
            $params[$k] = $r[$k];
        } else {
            $params[$k] = "";
        }

    }
    return $params;
}

function showIframe($uri, $params, $title = '')
{
    $url = "/wsbpvp/iframes/$uri?1";
    foreach ($params as $k => $val) {
        $val = is_array($val) ? json_encode($val) : $val;
        $url .= "&$k=$val";
    }
    if ($title) {
        $url .= "&title=$title";
    }

    return "<div><iframe src=$url height=500px width=100%></iframe></div>";
}
function echof($str)
{
    echo $str;
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
}
