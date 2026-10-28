<?php
/* Manual, real-network metadata smoke test. Does NOT use WordPress database. */
define( 'ABSPATH', __DIR__ );
$GLOBALS['requests'] = 0;
function hashe_fa7_additive_specs() {
    $out=array();
    foreach (explode(' ','fab fas far fal fat fad fadr fadl fadt fass fasr fasl fast fasds fasdr fasdl fasdt') as $p) {$out[$p]=true;}
    return $out;
}
function is_wp_error($v){return false;}
function wp_remote_retrieve_response_code($r){return $r['code'];}
function wp_remote_retrieve_body($r){return $r['body'];}
function wp_remote_get($url,$args=array()){
    $GLOBALS['requests']++;
    $context=stream_context_create(array('http'=>array(
        'method'=>'GET','timeout'=>8,'ignore_errors'=>true,
        'header'=>"Accept: application/json\r\nUser-Agent: HasheFA7Test/1.1.1\r\n",
    )));
    $body=@file_get_contents($url,false,$context);
    $code=0;
    if(isset($http_response_header) && preg_match('#HTTP/\S+ (\d+)#',$http_response_header[0],$m)){$code=(int)$m[1];}
    return array('code'=>$code,'body'=>$body===false?'':$body);
}
require __DIR__.'/../hashe-fa7-elementor/includes/catalog-provider.php';
$t=microtime(true);
$names=hashe_fa7_download_public_catalogs('7.3.1');
if(!$names){fwrite(STDERR,"API TEST FAILED\n");exit(1);}
foreach(array('fas','far','fab','fass','fasr','fasl','fad') as $p){
    printf("%s free=%d pro=%d\n",$p,count($names['free'][$p]),count($names['pro'][$p]));
}
if($GLOBALS['requests']<2 || count($names['pro']['fass'])<1000 || count($names['free']['fab'])<100){
    fwrite(STDERR,"BAD ACTUAL DATA\n");exit(2);
}
printf("PASS actual Font Awesome API requests=%d elapsed=%.2fs\n",$GLOBALS['requests'],microtime(true)-$t);
