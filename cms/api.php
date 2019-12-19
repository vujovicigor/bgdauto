<?php
session_start();
//header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include("engine.php");
$req = array_merge($_GET, $_POST);
if (isset($req['_session_user_name']))      unset($req['_session_user_name']);
if (isset($req['_session_user_role']))           unset($req['_session_user_role']);
if (isset($_SESSION['_session_user_name']))   $req['_session_user_name']  = $_SESSION['_session_user_name'];
if (isset($_SESSION['_session_user_role']))        $req['_session_user_role']  = $_SESSION['_session_user_role'];

if ($req['query'] == '_session_destroy'){
  session_destroy();
  echo json_encode(array('message'=>'Ok'));
  return;
}

if ($req['query'] == '_authCheck'){
  // check is already loged in// TODO: fix, refresh token...
  if ( isset($_SESSION['_session_user_name']) && isset($_SESSION['_session_user_role']) ){
    echo json_encode(array(
      '_session_user_name'=>$_SESSION['_session_user_name'],
      '_session_user_role'=>$_SESSION['_session_user_role']
    ));
  } else echo json_encode(array('message'=>'Wrong credentials'));
  
  return;
}


if ($req['query'] == '_authSA'){
  // check is already loged in// TODO: fix, refresh token...
  if ( isset($_SESSION['_session_user_name']) && isset($_SESSION['_session_user_role']) ){
    echo json_encode(array(
      '_session_user_name'=>$_SESSION['_session_user_name'],
      '_session_user_role'=>$_SESSION['_session_user_role']
    ));
    return;
  }


  //echo json_encode()
  $resp = fetch('_authSA', $req);
  if ( $resp == new stdClass() ){
    echo json_encode(array('message'=>'Wrong credentials'));
    return;
  }
  echo json_encode($resp);
  $_SESSION['_session_user_name'] = $resp['_session_user_name'];
  $_SESSION['_session_user_role'] = $resp['_session_user_role'];
  return ;
}

//if ( isset($_SESSION['_session_user_role']) ) //&& ($_SESSION['_session_user_role']==2 || $_SESSION['_session_user_role']==4))
  apiJson( $req['query'], $req );
//else echo json_encode(array('_message'=>'No privilegies', '_message_type'=>'error', '_message_action'=>'reload'));
?>