<?php
/** Standalone regression for Font Awesome 7 + Elementor Kit modes. */
define('ABSPATH', __DIR__);
define('HOUR_IN_SECONDS', 3600);
define('DAY_IN_SECONDS', 86400);
define('MINUTE_IN_SECONDS', 60);
define('ELEMENTOR_ASSETS_URL', 'https://example.test/elementor/assets/');
define('ELEMENTOR_VERSION', '4.3.5');
$case=$argv[1]??'no-kit';
$valid=array('no-kit','invalid-kit','css-small','css-mixed','full-pro','full-plus','css-stale','css-api-offline');
if(!in_array($case,$valid,true)){fwrite(STDERR,"UNKNOWN_CASE\n");exit(2);}
$GLOBALS['case']=$case;
$GLOBALS['kit_id']='no-kit'===$case?'':('invalid-kit'===$case?'invalid-id':'0123456789');
$GLOBALS['filters']=array();$GLOBALS['actions']=array();$GLOBALS['styles']=array();$GLOBALS['inline']=array();
$GLOBALS['scripts']=array();$GLOBALS['transients']=array();$GLOBALS['options']=array();$GLOBALS['requests']=array();
$GLOBALS['frontend']=false;
function check($yes,$why){if(!$yes){fwrite(STDERR,"FAIL $why\n");exit(3);}}
function add_filter($hook,$fn,$priority=10){$GLOBALS['filters'][$hook][]=array($priority,$fn);}
function add_action($hook,$fn,$priority=10){$GLOBALS['actions'][$hook][]=array($priority,$fn);}
function apply_filters($hook,$value){$f=$GLOBALS['filters'][$hook]??array();usort($f,function($a,$b){return $a[0]<=>$b[0];});foreach($f as $row)$value=call_user_func($row[1],$value);return $value;}
function get_option($name,$default=false){return 'elementor_font_awesome_pro_kit_id'===$name?$GLOBALS['kit_id']:($GLOBALS['options'][$name]??$default);}
function add_option($key,$val,$deprecated='',$autoload='no'){if(array_key_exists($key,$GLOBALS['options']))return false;$GLOBALS['options'][$key]=$val;return true;}
function update_option($key,$val,$autoload=null){$GLOBALS['options'][$key]=$val;return true;}
function delete_option($key){unset($GLOBALS['options'][$key]);}
function get_transient($key){return $GLOBALS['transients'][$key]??false;}
function set_transient($key,$val,$ttl=0){$GLOBALS['transients'][$key]=$val;return true;}
function home_url($path=''){return 'https://example.test/'.$path;}
function did_action($name){return 0;}
function doing_action($name){return false;}
function is_admin(){return !$GLOBALS['frontend'];}
function wp_doing_ajax(){return false;}
function wp_json_encode($v){return json_encode($v);}
function is_wp_error($r){return false;}
function wp_remote_retrieve_response_code($r){return $r['code'];}
function wp_remote_retrieve_body($r){return $r['body'];}
function plugins_url($path,$file){return 'https://example.test/plugins/hashe-fa7-elementor/'.$path;}
function wp_enqueue_style($name,$url=null,$deps=array(),$version=''){$GLOBALS['styles'][$name]=$url;}
function wp_register_style($name,$url=null,$deps=array(),$version=''){$GLOBALS['styles'][$name]=$url;}
function wp_add_inline_style($name,$content){$GLOBALS['inline'][$name]=($GLOBALS['inline'][$name]??'').$content;return true;}
function wp_enqueue_script($name,$url,$deps=array(),$version='', $footer=false){$GLOBALS['scripts'][$name]=$url;}
function family_rows(){return array(
array('prefix'=>'fas','family'=>'classic','style'=>'solid','shorthand'=>'solid'),
array('prefix'=>'far','family'=>'classic','style'=>'regular','shorthand'=>'regular'),
array('prefix'=>'fab','family'=>'brands','style'=>'brands','shorthand'=>'brands'),
array('prefix'=>'fal','family'=>'classic','style'=>'light','shorthand'=>'light'),
array('prefix'=>'fad','family'=>'duotone','style'=>'solid','shorthand'=>'duotone-solid'),
array('prefix'=>'fat','family'=>'classic','style'=>'thin','shorthand'=>'thin'),
array('prefix'=>'fass','family'=>'sharp','style'=>'solid','shorthand'=>'sharp-solid'),
array('prefix'=>'fajr','family'=>'jelly','style'=>'regular','shorthand'=>'jelly-regular'),
array('prefix'=>'fans','family'=>'notdog','style'=>'solid','shorthand'=>'notdog-solid'),
array('prefix'=>'fasldr','family'=>'slab-duo','style'=>'regular','shorthand'=>'slab-duo-regular'));}
function response($status,$data){return array('code'=>$status,'body'=>is_string($data)?$data:json_encode($data));}
function wp_remote_get($url,$args=array()){
 $GLOBALS['requests'][]=$url;$case=$GLOBALS['case'];$id='0123456789';
 if(strpos($url,'https://kit.fontawesome.com/'.$id.'.js')===0){
  $full=in_array($case,array('full-pro','full-plus'),true);
  $config=array('token'=>$id,'version'=>'7.3.1','method'=>$full?'js':'css','license'=>'pro',
   'subsetPath'=>$full?'':'assets/'.$id.'/111/kit.css','startupFilename'=>$full?($case==='full-plus'?'pro-plus.min.js':'pro.min.js'):'');
  return response(200,'window.FontAwesomeKitConfig='.json_encode($config).';');
 }
 if(strpos($url,'https://api.fontawesome.com/releases/7.3.1/family-styles')===0)return response(200,array('familyStyles'=>family_rows()));
 if(strpos($url,'https://api.fontawesome.com/releases/7.3.1/icon-packs')===0){
  $pro=array_slice(family_rows(),0,7);$plus=array_slice(family_rows(),7);
  $simple=function($rows){return array_map(function($x){return array('family'=>$x['family'],'style'=>$x['style']);},$rows);};
  return response(200,array('iconPacks'=>array(array('name'=>'pro-pack','familyStyles'=>$simple($pro)),array('name'=>'plus-pack','familyStyles'=>$simple($plus))),
   'iconCollections'=>array(array('name'=>'pro-collection','iconPacks'=>array('pro-pack')),array('name'=>'pro-plus-collection','iconPacks'=>array('pro-pack','plus-pack')))));
 }
 if(strpos($url,'https://ka-p.fontawesome.com/assets/'.$id.'/111/kit.css')===0){
  $css='url("pro-fa-jelly-regular-400-0.woff2") url("pro-fa-notdog-solid-900-0.woff2") url("pro-fa-slab-duo-regular-400-0.woff2")';
  if('css-mixed'===$case)$css.=' url("pro-fa-light-300-0.woff2") url("pro-fa-duotone-solid-900-0.woff2")';
  return response(200,$css);
 }
 if(strpos($url,'https://ka-p.fontawesome.com/releases/v7.3.1/js/')===0)return response(200,str_repeat('X',60000));
 if(strpos($url,'https://api.fontawesome.com/releases/7.3.1/icons-minimal')===0){
  if('css-api-offline'===$case)return response(503,'');
  $styles=array_column(family_rows(),'prefix');
  $variants=function($list){return array_map(function($p){return array('prefix'=>$p);},$list);};
  $icons=array();
  foreach(array('star','0','flag','font-awesome') as $n)$icons[]=array('id'=>$n,'familyStylesByLicense'=>array('free'=>$variants(array('fas','far','fab')),'pro'=>$variants($styles)));
  return response(200,array('icons'=>$icons,'page'=>1,'pageSize'=>500,'totalIconCount'=>4,'totalPageCount'=>1));
 }
 return response(404,'');
}
if('css-stale'===$case)$GLOBALS['transients']['hashe_fa7_additive_kit_v3_0123456789']=array('styles'=>array('fas'),'revision'=>'obsolete','version'=>'7.3.1');
require __DIR__.'/../hashe-fa7-elementor/hashe-fa7-elementor.php';
$base=array();
foreach(array('fa-regular'=>'Regular','fa-solid'=>'Solid','fa-brands'=>'Brands') as $k=>$name){
 $base[$k]=array('name'=>$k,'label'=>'Font Awesome - '.$name,'native'=>true,'url'=>'https://example.test/free/'.$k.'.css','ver'=>'5.15.3','icons'=>array('star'));
}
$core=$base;
if('no-kit'!==$case)add_filter('elementor/icons_manager/native',function($tabs){
 foreach(array('fa-regular'=>'Regular','fa-solid'=>'Solid','fa-brands'=>'Brands','fa-light'=>'Light','fa-duotone'=>'Duotone') as $k=>$name)
  $tabs[$k]=array('name'=>$k,'label'=>'Font Awesome - '.$name.' Pro','native'=>true,'url'=>false,'ver'=>'5.15.1-pro','icons'=>array('star'));
 return $tabs;
},10);
$native=apply_filters('elementor/icons_manager/native',$base);
$added=apply_filters('elementor/icons_manager/additional_tabs',array());
$kit=hashe_fa7_additive_kit();$styles=$kit['styles']??array();
foreach(array('fa-regular'=>'far','fa-solid'=>'fas','fa-brands'=>'fab') as $tab=>$prefix){
 check(isset($native[$tab]),'three Core native categories');
 if($kit&&!in_array($prefix,$styles,true))check($native[$tab]===$core[$tab],'native Free definition when omitted');
}
check(isset($native['fa-light'])===(in_array('fal',$styles,true)||'invalid-kit'===$case),'native Light entitlement');
check(isset($native['fa-duotone'])===(in_array('fad',$styles,true)||'invalid-kit'===$case),'native Duotone entitlement');
$expected=array();
if('no-kit'===$case)$expected=array('hashe-fa7-free-fas','hashe-fa7-free-far','hashe-fa7-free-fab');
elseif($kit)foreach($styles as $p)if(!in_array($p,array('fas','far','fab','fal','fad'),true))$expected[]='hashe-fa7-kit-'.$p;
if('css-api-offline'===$case)$expected=array();
sort($expected);$actual=array_keys($added);sort($actual);
check($actual===$expected,'additional library membership');
foreach($added as $id=>$tab){check(count($tab['icons'])>0,'nonempty icon tab');foreach($tab['icons'] as $n)check(is_string($n),'numeric icon names are strings');if(strpos($id,'hashe-fa7-kit-')===0)check(in_array('0',$tab['icons'],true),'numeric 0 preserved');}
if('full-pro'===$case)check(count($styles)===7,'dynamic Pro collection');
if('full-plus'===$case)check(count($styles)===10,'dynamic Pro Plus collection');
if('css-stale'===$case)check(!in_array('fas',$styles,true)&&in_array('fajr',$styles,true),'republished Kit invalidates stale inventory');
if('no-kit'!==$case&&$kit){
 hashe_fa7_enqueue_native_core_free_fallback();
 foreach(array('fas','far','fab') as $p){
  $needed=!in_array($p,$styles,true);
  check(isset($GLOBALS['styles']['hashe-fa7-native-free-'.$p])===$needed,'missing native Free asset '.$p);
  check((strpos($GLOBALS['inline']['hashe-fa7-native-free-core']??'','body .'.$p)!==false)===$needed,'missing native Free font rule '.$p);
 }
}
check(strpos(implode(' ',$GLOBALS['requests']),'/webfonts/pro-fa-')===false,'no unauthorized Pro font shard');
$GLOBALS['frontend']=true;
$front=apply_filters('elementor/icons_manager/native',$base);
if('no-kit'!==$case)check(isset($front['fa-light'])&&isset($front['fa-duotone']),'saved frontend native references retained');
check(!is_file(__DIR__.'/../hashe-fa7-elementor/data/native-pro-unicode.json'),'no bundled Pro map');
echo 'PASS '.$case.': native tabs, dynamically licensed Kit styles, Free fallback and saved icon references'.PHP_EOL;
