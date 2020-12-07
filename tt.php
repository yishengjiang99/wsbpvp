<?php

date_default_timezone_set("America/Los_Angeles");
header("Access-Control-Allow-Origin: *");
$offset=0x3800;
$chunk=128*2*4;
//https://grep32bit.blob.core.windows.net/pcm?resttype=container&comp=list
$file = isset($_GET['f']) ? $_GET['f']: './song-f32le.pcm';
$stream = fopen($file, 'rb') || die("file not found");
$seek = isset($_GET['seek']) ? $_GET['seek'] : 0;
$seek = isset($_GET['n']) ? $_GET['n'] : 44100*2*4*10;

$offset = $seek * 44100*2*4;
while (1) {
    
  echo stream_get_contents($stream, $chunk,$offset);


  $offset+=$chunk;

  while (ob_get_level() > 0) {
    ob_end_flush();
  }
  flush();


  // break the loop if the client aborted the connection (closed the page)
  
  if ( connection_aborted() ) break;
  sleep(128/44100);
}
