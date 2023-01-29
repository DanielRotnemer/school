<?php

    header('Content-Type: text/html;charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    date_default_timezone_set("Asia/Jerusalem");

    require_once('C:\xampp\htdocs\NewSite\utilities\utilities.php');

    if (count($_COOKIE) === 0 || !isset($_COOKIE[session_name()]) || !file_exists('C:\xampp\tmp\sess_'.$_COOKIE[session_name()])) {
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' || count($_POST) != 0)
    {
        // HANDLE POST REQUEST
        $variables = ['login', 'username', 'password'];
        $error = '';
        foreach ($variables as $key => $value) 
        {
            if (!isset($_POST[$variables[$key]])) 
            {
                $error = 'אנא מלא\י את כל השדות הנדרשים';
                break;
            }
        }
        if (!isset($_POST['student']) && !isset($_POST['instructor']) && !isset($_POST['manager'])) {
            $error = 'אנא מלא\י את כל השדות הנדרשים';
        }
        if ($error == '') {           
            $error = SESSION::LogIn($_POST['username'], $_POST['password']);            
        }
        $document = PAGES::LogIn($error);
    }
    else {
        $document = PAGES::LogIn();
    }    

?>

<html>
    <head>
        <title>כניסה</title>
        <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <link rel="stylesheet" href="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/login.css?crm=<?php echo time(); ?>"/>
        <script src="http://<?php echo $GLOBALS['SERVER_ADDRESS']; ?>/login.js?crm=<?php echo time(); ?>"></script>
    </head>
    <body>
        <form method="post" autocomplete="off" enctype="multipart/form-data" accept-charset="UTF-8">
            <?php echo $document; ?>
        </form>
    </body>
</html>