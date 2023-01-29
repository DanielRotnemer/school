<?php

    header('Content-Type: text/html;charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    date_default_timezone_set("Asia/Jerusalem");

    require_once('C:\xampp\htdocs\NewSite\utilities\utilities.php');

    if (count($_COOKIE) === 0 || !file_exists('C:\xampp\tmp\sess_'.$_COOKIE[session_name()])) {
        session_id(UTILITIES::CreateSessionId());
    }
    else {
        session_id($_COOKIE[session_name()]);
    }
    session_start();

    // REGENERATE SESSION ID TO PREVENT SESSION HIJACKING ATTACK
    $primarySid = session_id(); 
    $saved = $_SESSION;
    session_commit();
    ini_set('session.use_strict_mode', 0);
    session_id(UTILITIES::CreateSessionId());
    session_start(); 
    $_SESSION = $saved;
    setcookie(session_name(), session_id(), 0, "/", $GLOBALS['SERVER_ADDRESS'], false, true);
    $_COOKIE[session_name()] = session_id();

    if (file_exists('C:\xampp\tmp\sess_'.$primarySid)) {
        unlink('C:\xampp\tmp\sess_'.$primarySid);
    } 

    SESSION::LogOut();

?>