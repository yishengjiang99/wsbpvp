<?php

header("Content-Type: text/event-stream");
header('Cache-Control: no-cache');

while(true){
  $t=microtime();
  echo "event: tick\n";
  echo "data: ".microtime()." $s2\n\n";
  if ( connection_aborted() ) break;
  reallyflush();
  sleep(1);//float(explode(" ",$t)[0])*10e6);

}
function reallyflush(){
  while (ob_get_level() > 0) {
    ob_end_flush();
    }
    flush();
}