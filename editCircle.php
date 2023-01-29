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

    // CHECK IF THE MANAGER IS THE CURRENT LOGGED USER
    if (!isset($_SESSION['IdNumber']) || !isset($_SESSION['ManagerId']) || !isset($_GET['id'])) 
    {
        header("Location: http://$GLOBALS[SERVER_ADDRESS]/index");
        exit;
    }

    $document = PAGES::EditCirclePage($_GET['id']);

?>

<html>
    <head>
        <title>עריכת חוג</title>
        <link rel="stylesheet" href="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/editCircle.css?crm=<?php echo time(); ?>"/>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/editCircle.js?crm=<?php echo time(); ?>"></script>
        <script src="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/manager.js?crm=<?php echo time(); ?>"></script>
    </head>
    <body>
        <?php echo $document; ?>
    </body>
</html>