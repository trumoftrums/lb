<?php
/**
 * BigPipe Script of LdrMx Version 1.0
 * Plugins for Wowonder
 * @author Pepe Galvan - LdrMx
 */ 
 
 	/* get js and css and comprisse html
     * @param array js, css
     * @return script autoload for bigpipe function
     */
   function cHTML($params)
  { 
   global $wo;
  //ADD GLOBAL
  if(isset($params['global'])){$params['global'] = str_replace(' ', '', $params['global']); $global_list = array_filter(explode(',', $params['global']));
  foreach( $global_list as $global_id ) { global $$global_id; } }

 /*default variables*/
  $js = '';
  $css = '';
  $html = '';
  $BigPipe = '';

  if(isset($params['html'])){
  ob_start(); ob_get_level();
  /*url template comprisse */
  include './themes/' . $wo['config']['theme'] . '/layout/plugins/' . $params['html'] . '.phtml';
  $html = ob_get_contents(); ob_end_clean();
  /*$html = str_replace("\t",'',$html);
  $html = str_replace("\r",'',$html);
  $html = str_replace("\n",'',$html);
  */
  $html = preg_replace('/\s+/',' ',$html);
  if (ob_get_level()>1) {ob_end_flush();}
  }
  /*create script*/
  if(isset($params['css']) || isset($params['js'])){
  $BigPipe .= '<script>bigPipe.onPageletArrive({';
 
  /*load css names*/
  if(isset($params['css'])){
  $BigPipe .= '"css":[';
      $params['css'] = str_replace(' ', '', $params['css']);
      $params['css'] = str_replace('"', '', $params['css']);
      $params['css'] = str_replace('\'', '', $params['css']);
      $preload_list = array_filter(explode(',', $params['css']));
  $BP_first = TRUE;	
  foreach($preload_list as $css_id){ 
  if( !$BP_first )$BigPipe .= ', '; $BigPipe .= '"'.$css_id.'"'; $BP_first = FALSE; }
  $BigPipe .= ']';
  }
 
   /*load js names*/
  if(isset($params['js'])){
  if(isset($params['css'])){ $BigPipe .= ','; }
  $BigPipe .= '"js":[';
      $params['js'] = str_replace(' ', '', $params['js']);
      $params['js'] = str_replace('"', '', $params['js']);
      $params['js'] = str_replace('\'', '', $params['js']);
      $preload_list = array_filter(explode(',', $params['js']));
	   $BP_first = TRUE;	
   foreach( $preload_list as $js_id ) { 
	  if( !$BP_first )$BigPipe .= ', ';
	  $BigPipe .= '"'.$js_id.'"'; 
	  $BP_first = FALSE;
    }
  $BigPipe .= ']';
  }
  $BigPipe .='})</script>
  ';
  }
  /* return script code */
  return $BigPipe.$html;
 }

 	/* get template php, js and css
     * @param array template php, js, css
     * @return script autoload for bigpipe function
     */
  function BigPipe($params)
  { global $wo;

  //ADD GLOBAL
  if(isset($params['global'])){$params['global'] = str_replace(' ', '', $params['global']); $global_list = array_filter(explode(',', $params['global']));
  foreach( $global_list as $global_id ) { global $$global_id; } }

   /*default variables*/
    $dd = '';
    $js = '';
    $css = '';
    $BigPipe = '';	

  ob_start();
  // content html or php
  include './themes/' . $wo['config']['theme'] . '/layout/plugins/' . $params['html'] . '.phtml';
  $html = ob_get_contents();
  ob_end_clean();

  ob_get_level();
  /*$html = str_replace("\t",'',$html);
  $html = str_replace("\r",'',$html);
  $html = str_replace("\n",'',$html);
  */
  $html = preg_replace('/\s+/',' ',$html);
  if(isset($params['type'])){ if($params['type']== 'json'){$html = json_encode($html);}}
  //ob_end_flush();
  if (ob_get_level()>1) {ob_end_flush();}
  if(isset($params['DESC'])){
  $BigPipe .= '
  <!-- '.$params['DESC'].' -->
  '; 
  }
  $BigPipe .= '<code id="'.$params['code'].'" class="hidden"><!-- '.$html.' --></code>
  '; 
  /*create script*/
  $BigPipe .= '<script>bigPipe.onPageletArrive({';

  /*load position name*/
  if(isset($params['append'])){$BigPipe .= '"append":["'.$params['append'].'"],';}
  if(isset($params['appendTo'])){$BigPipe .= '"appendTo":["'.$params['appendTo'].'"],';}
  if(isset($params['prepend'])){$BigPipe .= '"prepend":["'.$params['prepend'].'"],';}
  if(isset($params['before'])){$BigPipe .= '"before":["'.$params['before'].'"],';}
  if(isset($params['after'])){$BigPipe .= '"after":["'.$params['after'].'"],';}
  if(isset($params['dd'])){$BigPipe .= '"display_dependency":["'.$params['dd'].'"],';}
  $BigPipe .= '"content":"'.$params['content'].'","container_id":"'.$params['code'].'"'; 

  /*load css names*/
  if(isset($params['css'])){
  $BigPipe .= ',"css":[';
      $params['css'] = str_replace('"', '', $params['css']);
      $params['css'] = str_replace('\'', '', $params['css']);
      $preload_list = array_filter(explode(',', $params['css']));
	   $BP_first = TRUE;	
  foreach( $preload_list as $css_id ) { 
	  if( !$BP_first )$BigPipe .= ', ';
	  $BigPipe .= '"'.$css_id.'"'; 
	  $BP_first = FALSE;
    }
  $BigPipe .= ']';
  }

  /*load js names*/
  if(isset($params['js'])){
  $BigPipe .= ',"js":[';
      $params['js'] = str_replace('"', '', $params['js']);
      $params['js'] = str_replace('\'', '', $params['js']);
      $preload_list = array_filter(explode(',', $params['js']));
	   $BP_first = TRUE;	
  foreach( $preload_list as $js_id ) { 
	  if( !$BP_first )$BigPipe .= ', ';
	  $BigPipe .= '"'.$js_id.'"'; 
	  $BP_first = FALSE;
    }
  $BigPipe .= ']';
  }
  if(isset($params['unique'])){ $BigPipe .= ',"unique":"true"'; } 
  /*get a id*/
  $BigPipe .= ',"id":"'.$params['content'].'"'; 
  $BigPipe .='})</script>
  ';
  return $BigPipe;
  }
?>