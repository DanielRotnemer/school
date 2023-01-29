<?php

    require_once 'vendor/autoload.php';

    $phones = ['+972533937845'];

    $MessageBird = new \MessageBird\Client('BBFDUtqguX4t2aJWg61PtReTp');
    $Message = new \MessageBird\Objects\Message();
    $Message->originator = 'school';    
    $Message->recipients = $phones;
    $Message->body = 'חוג מסויים הסתיים, הנך מוזמן/ת להשאיר משוב על החוג';
    $Message->datacoding = 'unicode';
    echo json_encode($MessageBird->messages->create($Message));
    exit;

?>