<?php
    
    date_default_timezone_set("Asia/Jerusalem");
    session_start();
    require_once('C:\xampp\htdocs\NewSite\utilities\utilities.php');
    require_once 'vendor/autoload.php';
    
    $day = date('w');
    $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
    $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
    $currentDateTime = date("Y-m-d H:i:s");
    $currentDate = date('Y-m-d');
    $monthStart = date('Y-m-01').' 00:00:00'; 
    $monthEnd = date("Y-m-t").' 23:00:00';
    $monthEndDate = date("Y-m-t");
    $dayStart = date('Y-m-d').' 00:00:00'; 
    $dayEnd = date('Y-m-d').' 23:59:59';

    file_put_contents('C:\xampp\htdocs\testAuto.txt', $currentDateTime);  

    /* -------------------------------- REDUCE MONTHLY SALARIES FROM THE GENERAL BUDGET ---------------------------------- */

    // CHECK IF TODAY IS THE END OF THE MONTH, IF SO - REDUCE THE INSTRUCTORS SALARIES.
    if (strtotime($currentDateTime) > strtotime($monthEnd) && !file_exists('C:\xampp\htdocs\salariesReduced'.intval(date('m')).'.txt'))
    {
        // SELECT ALL THE SALARIES TO REDUCE FOR THIS MONTH
        $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
        $stmt = $pdo->prepare("SELECT * FROM instructors_salaries WHERE BINARY Date=?");
        $stmt->execute([$monthEndDate]); 
        $salriesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        
        $totalSalaries = 0;
        foreach ($salriesResult as $sKey => $sValue) {
            $totalSalaries += floatval($sValue['Salary']);
        }
        
        // GET THE CURRENT BUDGET FROM THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
        $stmt = $pdo->prepare("SELECT * FROM total_budget");
        $stmt->execute(); 
        $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalBudget = floatval($totalBudgetResult[0]['TotalSchoolBudget']);
        
        // REDUCE THE INSTRUCTOR SALARIES FROM THE TOTAL BUDGET
        $totalBudget -= $totalSalaries;
        $stmt = $pdo->prepare("UPDATE total_budget SET TotalSchoolBudget=?");
        $stmt->execute([$totalBudget]); 
        $pdo = null;

        file_put_contents('C:\xampp\htdocs\salariesReduced'.intval(date('m')).'.txt', 'Reduced slaries ('.$totalSalaries.') for month '.date('m').', Don\'t delete this file.');
    }
    else if (file_exists('C:\xampp\htdocs\salariesReduced'.(intval(date('m')) - 1).'.txt')) {
        unlink('C:\xampp\htdocs\salariesReduced'.(intval(date('m')) - 1).'.txt');
    }

    /* -------------------------------- SENT NOTIFICATIONS ABOUT FINISHED CIRCLES ---------------------------------- */

    // SELECT ALL THE CIRCLES
    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
    $stmt = $pdo->prepare("SELECT CircleId,CircleName,CircleInstructorId FROM circles_table WHERE BINARY EndDate>=? AND EndDate<=? AND NotificationSent=?");
    $stmt->execute([$dayStart, $currentDateTime, 'NO']); 
    $todayFinishedCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;

    // CHECK IF THERE IS A CIRCLE THAT IS FINISHED TODAY, IF SO - SEND A MESSAGE TO STUDENTS TO LEAVE COMMENT
    foreach ($todayFinishedCirclesResult as $tfcKey => $tfcValue)
    {
        // SELECT ALL THE MEETINGS OF THIS CIRCLE
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT MeetingId FROM circle_meetings WHERE BINARY CircleId=?");
        $stmt->execute([$tfcValue['CircleId']]); 
        $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        $circleParticipantsIds = [];        
        foreach ($circleMeetingsResult as $cmKey => $cmValue)
        {
            // GET MEETING PARTICIPANTS
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");
            $stmt->execute([$cmValue['MeetingId']]); 
            $meetingParticipantsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            foreach ($meetingParticipantsResult as $mpKey => $mpValue)
            {
                if (!in_array($mpValue['StudentId'], $circleParticipantsIds)) {
                    array_push($circleParticipantsIds, $mpValue['StudentId']);
                }
            }
        }

        // SEND MESSAGE TO EVERY STUDENT ABOUT THIS CIRCLE
        $message = 'חוג '.$tfcValue['CircleName'].' הסתיים, הנך מוזמן/ת להשאיר משוב על החוג';
        $pdo = UTILITIES::PDO_DB_Connection('school_messages');
        foreach ($circleParticipantsIds as $cpKey => $cpValue)
        {
            $stmt = $pdo->prepare("INSERT INTO messages_table (Active,MessageText,CreationDate,ToSpecificStudent) VALUES (?,?,?,?)");
            $stmt->execute(['YES', $message, $currentDateTime, $cpValue]); 
        }
        $pdo = null;

        // UPDATE THIS CIRCLE AND MARK IT AS NOTIFICATION SENT
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("UPDATE circles_table SET NotificationSent=? WHERE BINARY CircleId=?");
        $stmt->execute(['YES', $tfcValue['CircleId']]); 
        $pdo = null;

        // SELECT THE PHONE NUMBER OF ALL THE PARTICIPANTS TO SEND SMS MESSAGE
        $phones = [];
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        foreach ($circleParticipantsIds as $cpKey => $cpValue)
        {
            $stmt = $pdo->prepare("SELECT Phone FROM students WHERE BINARY StudentId=?");
            $stmt->execute([$cpValue]);
            $studentPhoneResult = $stmt->fetchAll(PDO::FETCH_ASSOC); 

            if (count($studentPhoneResult) > 0) {
                array_push($phones, '+972'.$studentPhoneResult[0]['Phone']);   
                file_put_contents('C:\xampp\htdocs\testAuto.txt', $currentDateTime.' | '.'+972'.$studentPhoneResult[0]['Phone']);             
            }
        }
        $pdo = null;

        if (count($phones) > 0)
        {
            try {
                $MessageBird = new \MessageBird\Client('BBFDUtqguX4t2aJWg61PtReTp');
                $Message = new \MessageBird\Objects\Message();
                $Message->originator = 'School';
                $Message->recipients = $phones;
                $Message->body = 'חוג '.$tfcValue['CircleName'].' הסתיים, הנך מוזמן/ת להשאיר משוב על החוג';
                $Message->datacoding = 'unicode';
            }
            catch (Exception $ex) {
                file_put_contents('C:\xampp\htdocs\testAuto.txt', $currentDateTime.' | '.$ex->getMessage());
            }            
        }

        // SELECT THE PHONE NUMBER OF THE INSTRUCTOR OF THIS CIRCLE
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("SELECT Phone FROM instructors WHERE BINARY InstructorId=?");
        $stmt->execute([$tfcValue['CircleInstructorId']]); 
        $instructorPhoneResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // SEND SMS TO THE CIRCLE INSTRUCTOR
        if (count($instructorPhoneResult) > 0)
        {
            try {
                $MessageBird = new \MessageBird\Client('BBFDUtqguX4t2aJWg61PtReTp');
                $Message = new \MessageBird\Objects\Message();
                $Message->originator = 'School';
                $Message->recipients = array('+972'.$instructorPhoneResult[0]['Phone']);
                $Message->body = 'חוג '.$tfcValue['CircleName'].' הסתיים, הנך מוזמן/ת להשאיר משוב על החוג';
                $Message->datacoding = 'unicode';                
            }
            catch (Exception $ex) {
                file_put_contents('C:\xampp\htdocs\testAuto.txt', $currentDateTime.' | '.$ex->getMessage());
            }  
        }

        $pdo = UTILITIES::PDO_DB_Connection('school_messages');        
        $stmt = $pdo->prepare("INSERT INTO messages_table (Active,MessageText,CreationDate,ToSpecificInstructor) VALUES (?,?,?,?)");
        $stmt->execute(['YES', $message, $currentDateTime, $tfcValue['CircleInstructorId']]); 
        $pdo = null;
    }

?>