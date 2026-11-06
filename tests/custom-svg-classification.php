<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__).'/hashe-fa7-elementor/includes/custom-kit-icons.php';
$mono='<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h5v5z"/></svg>';
$duo='<svg xmlns="http://www.w3.org/2000/svg"><path opacity=".4" d="M0 0h5v5z"/><path d="M1 1h3v3z"/></svg>';
$multi='<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h5v5z"/><path d="M1 1h3v3z"/></svg>';
$tests=[
 ['name'=>'single_color','svg'=>$mono,'want'=>false],
 ['name'=>'duotone_two_layer','svg'=>$duo,'want'=>true],
 ['name'=>'multiple_opaque_paths','svg'=>$multi,'want'=>false],
 ['name'=>'xml_entity_rejected','svg'=>'<!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>','want'=>null],
 ['name'=>'non_svg_rejected','svg'=>'<html><body><path d="M0 0"/></body></html>','want'=>null],
 ['name'=>'no_paths_rejected','svg'=>'<svg xmlns="http://www.w3.org/2000/svg"><circle r="5"/></svg>','want'=>null],
 ['name'=>'oversized_svg_rejected','svg'=>str_repeat('X',262145),'want'=>null],
];
foreach($tests as $case){
  $actual=hashe_fa7_custom_svg_is_duotone($case['svg']);
  if($actual!==$case['want']){
    fwrite(STDERR,"FAIL_".$case['name']." got=".var_export($actual,true)."\n");exit(4);
  }
  echo "PASS_".$case['name']."\n";
}
echo "SVG_CLASSIFICATION_TESTS=PASS\n";
