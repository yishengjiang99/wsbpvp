<?php
header("Content-Type: text/event-stream");
while(true){
  $t=microtime();
  echo "event: tick\n";
  echo "data: ".microtime()." $s2\n\n";
  if ( connection_aborted() ) break;
  ob_flush();
  sleep(1);//float(explode(" ",$t)[0])*10e6);
}