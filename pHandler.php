<?php

    // AN HTTP HANDLER TO HANDLE AJAX CALLS

    date_default_timezone_set("Asia/Jerusalem");
    session_start();
    require_once('C:\xampp\htdocs\NewSite\utilities\utilities.php');

    // MARK THE MESSAGE AS READ
    if (isset($_POST['readMsgId']) && isset($_POST['readMsgByType']) && isset($_POST['readMsgById']))
    {        
        $pdo = UTILITIES::PDO_DB_Connection('school_messages');
        $stmt = $pdo->prepare("SELECT ReadBy".$_POST['readMsgByType']."s FROM messages_table WHERE MessageId=?");
        $stmt->execute([$_POST['readMsgId']]); 
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $read = $result[0]["ReadBy".$_POST['readMsgByType']."s"];
        if (empty($read)) {
            $read = "|".$_SESSION[$_POST['readMsgByType'].'Id']."|";
        }
        else {
            $read .= $_SESSION[$_POST['readMsgByType'].'Id']."|";
        }

        $stmt = $pdo->prepare("UPDATE messages_table SET ReadBy".$_POST['readMsgByType']."s=? WHERE BINARY MessageId=?");      
        $stmt->execute([$read, $_POST['readMsgId']]); 
        $pdo = null; 
        echo 'read successfully';
        exit;
    }

    // CREATE A NEW PERMANENT CIRCLE 
    if (isset($_POST['newCircleName']) && isset($_POST['newCircleDescription']))
    {
        // CHECK IF THERE IS ALREADY A CIRCLE WITH THIS NAME
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT PermanentCircleName FROM permanent_circles WHERE PermanentCircleName=?");
        $stmt->execute([$_POST['newCircleName']]); 
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result || count($result) > 0) {
            echo 'חוג זה כבר קיים ברשימת החוגים במערכת';
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO permanent_circles (PermanentCircleName,PermanentCircleDescription) VALUES (?,?)");
        $stmt->execute([$_POST['newCircleName'], $_POST['newCircleDescription']]); 
        $pdo = null;

        echo 'added successfully';
        exit;
    }

    // ADD A NEW INSTRUCTOR TO THE SYSTEM
    if (isset($_POST['instructorFirstName']) && isset($_POST['instructorLastName']) && isset($_POST['instructorMail']) 
        && isset($_POST['instructorPhone']) && isset($_POST['instructorId']) && isset($_POST['instructorBirthDate']))
    {
        // CHECK FOR EMPTY VARIABLES
        if (empty($_POST['instructorFirstName']) || empty($_POST['instructorLastName']) || empty($_POST['instructorMail']) ||
            empty($_POST['instructorPhone']) || empty($_POST['instructorId']) || empty($_POST['instructorBirthDate']))
        {
            echo 'אנא מלא/י את כל השדות הנדרשים';
            exit;
        }

        // CHECK IF THERE IS ALREADY AN INSTRUCTOR WITH THE SAME ID
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("SELECT IdNumber FROM instructors WHERE IdNumber=?");
        $stmt->execute([$_POST['instructorId']]); 
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result || count($result) > 0) {
            echo 'מדריך זה כבר קיים ברשימת המדריכים במערכת';
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO instructors (FirstName,LastName,Phone,Email,IdNumber,BirthDate) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$_POST['instructorFirstName'], $_POST['instructorLastName'], $_POST['instructorPhone'], $_POST['instructorMail'],
            $_POST['instructorId'], $_POST['instructorBirthDate']]); 
        $pdo = null;

        echo 'added successfully';
        exit;        
    }

    // ADD A NEW STUDENT TO THE SYSTEM
    if (isset($_POST['studentFirstName']) && isset($_POST['studentLastName']) && isset($_POST['studentMail']) 
        && isset($_POST['studentPhone']) && isset($_POST['studentId']) && isset($_POST['studentBirthDate']))
    {
        // CHECK FOR EMPTY VARIABLES
        if (empty($_POST['studentFirstName']) || empty($_POST['studentLastName']) || empty($_POST['studentMail']) ||
            empty($_POST['studentPhone']) || empty($_POST['studentId']) || empty($_POST['studentBirthDate']))
        {
            echo 'אנא מלא/י את כל השדות הנדרשים';
            exit;
        }

        // CHECK IF THERE IS ALREADY AN STUDENT WITH THE SAME ID
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("SELECT IdNumber FROM students WHERE IdNumber=?");
        $stmt->execute([$_POST['studentId']]); 
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result || count($result) > 0) {
            echo 'תלמיד זה כבר קיים ברשימת התלמידים במערכת';
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO students (FirstName,LastName,Phone,Email,IdNumber,BirthDate) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$_POST['studentFirstName'], $_POST['studentLastName'], $_POST['studentPhone'], $_POST['studentMail'],
            $_POST['studentId'], $_POST['studentBirthDate']]); 
        $pdo = null;

        echo 'added successfully';
        exit;        
    }

    // SCHEDULE A NEW CIRCLE
    if (isset($_POST['circleName']) && isset($_POST['circleInstructorName']) && isset($_POST['circleDescription']) && isset($_POST['circleSchedule']))
    {
        $day = date('w');
        $weekStart = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('Y-m-d', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");        

        // CHECK IF THIS CIRCLE IS ALREADY SCHEDULED FOR THIS WEEK
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT CircleId FROM circles_table WHERE BINARY CircleName=? AND StartDate>=?");
        $stmt->execute([$_POST['circleName'], $weekStart]); 
        $scheduledCircleResult = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        $pdo = null; 

        if (count($scheduledCircleResult) > 0)
        {
            echo 'החוג המבוקש כבר שובץ לשבוע הנוכחי, לא ניתן ליצור עבורו מערכת שבועית נוספת';
            exit;
        }

        // CHECK IF THERE ARE MEETINGS FOR THIS CIRCLE
        $circleMeetings = json_decode(stripslashes($_POST['circleSchedule']));
        $full = false;
        for ($i = 0; $i < count($circleMeetings); $i++)
        {
            if (count($circleMeetings[$i]) > 0) {
                $full = true;
                break;
            }
        }
        if ($full == false) {
            echo 'הוסף/י מפגשים לחוג';
            exit;
        }
        
        $startDate = ''; $endDate = ''; 
        
        // SET THE START DATE AS THE START OF THE FIRST MEETING
        for ($i = $day; $i < count($circleMeetings); $i++)
        {
            if (count($circleMeetings[$i]) > 0 && $startDate == '')
            {
                $startTime = $circleMeetings[$i][0][0];
                $startDate = date('Y-m-d', strtotime('+'.($i - $day).' days')).' '.$startTime;
                break;
            }
        }    
        
        // SET THE END DATE AS THE END OF THE LAST MEETING
        for ($i = count($circleMeetings) - 1; $i >= $day; $i--)
        {
            if (count($circleMeetings[$i]) > 0 && $endDate == '')
            {
                $endTime = $circleMeetings[$i][count($circleMeetings[$i]) - 1][1];
                $endDate = date('Y-m-d', strtotime('+'.($i - $day).' days')).' '.$endTime;
                break;
            }
        }
        
        // VALIDATE START DATE
        if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($startDate)) 
        {
            echo 'שגיאה: זמן שגוי עבור המפגש הראשון של החוג';
            exit;
        }

        // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS CIRCLE, (AGAINST OTHER CIRCLES)
        $instructorIdNumber = explode(' - ', $_POST['circleInstructorName'])[1];
        $instructorId = -1;
        
        // SELECT THE ID OF THE INSTRUCTOR
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("SELECT InstructorId FROM instructors WHERE BINARY IdNumber=?");
        $stmt->execute([$instructorIdNumber]); 
        $instructorIdResults = $stmt->fetchAll(PDO::FETCH_ASSOC);        
        foreach ($instructorIdResults as $row) {
            $instructorId = $row['InstructorId'];
        }
        $pdo = null; 

        if ($instructorId == -1)
        {
            echo 'המדריך ששובץ עבור חוג זה לא קיים במערכת';
            exit;
        }

        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate>=?");
        $stmt->execute([$instructorId, $currentDateTime]); 
        $currentInstructorCircles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        foreach ($currentInstructorCircles as $row) 
        {
            if (strtotime($startDate) <= strtotime($row['EndDate']) && strtotime($startDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר חוג אחר בזמנים שנבחרו עבור חוג זה';
                exit;
            }

            if (strtotime($endDate) <= strtotime($row['EndDate']) && strtotime($endDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר חוג אחר בזמנים שנבחרו עבור חוג זה';
                exit;
            }

            if (strtotime($row['StartDate']) >= strtotime($startDate) && strtotime($endDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר חוג אחר בזמנים שנבחרו עבור חוג זה';
                exit;
            }
        }    
        
        // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS CIRCLE, (AGAINST OTHER EVENTS)
        $pdo = UTILITIES::PDO_DB_Connection('school_events');
        $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM events_table WHERE BINARY NeedInstructor=? AND EventInstructorId=? AND EndDate>=?");
        $stmt->execute(['YES', $instructorId, $currentDateTime]); 
        $currentInstructorEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        foreach ($currentInstructorEvents as $row) 
        {
            if (strtotime($startDate) <= strtotime($row['EndDate']) && strtotime($startDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר אירוע בזמנים שנבחרו עבור חוג זה';
                exit;
            }

            if (strtotime($endDate) <= strtotime($row['EndDate']) && strtotime($endDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר אירוע בזמנים שנבחרו עבור חוג זה';
                exit;
            }

            if (strtotime($row['StartDate']) >= strtotime($startDate) && strtotime($endDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר אירוע בזמנים שנבחרו עבור חוג זה';
                exit;
            }
        }

        // INSERT THE CIRCLE TO THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare('INSERT INTO circles_table (CircleName,StartDate,EndDate,
                                CircleInstructorId,CircleDescription) VALUES (?,?,?,?,?)');
        $stmt->execute([$_POST['circleName'], $startDate, $endDate, $instructorId, $_POST['circleDescription']]); 

        // SELECT THE CIRCLE ID FROM THE DATABASE
        $stmt = $pdo->prepare('SELECT CircleId FROM circles_table WHERE BINARY StartDate=? AND EndDate=? AND CircleInstructorId=?');
        $stmt->execute([$startDate, $endDate, $instructorId]);
        $circleIdResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $circleId = $circleIdResult[0]['CircleId'];
        
        // INSERT MEETINGS TO THE DATABASE
        foreach ($circleMeetings as $cmDay => $meetings)
        {
            $offset = $cmDay - $day;
            $meetingDate = date('Y-m-d', strtotime('+'.$offset.' days'));
            if ($offset < 0) { $meetingDate = date('Y-m-d', strtotime($offset.' days')); }

            foreach ($meetings as $mKey => $mValue)
            {    
                $start = $meetingDate.' '.$mValue[0];
                $end = $meetingDate.' '.$mValue[1];
                $stmt = $pdo->prepare('INSERT INTO circle_meetings (StartDate,EndDate,CircleId,WeekDay) VALUES (?,?,?,?)');
                $stmt->execute([$start, $end, $circleId, $cmDay]); 
            }            
        }        
        $pdo = null;

        echo 'החוג נוסף בהצלחה';
        exit;
    }

    // SCHEDULE A NEW EVENT
    if (isset($_POST['newEventName']) && isset($_POST['newEventDescription']) && isset($_POST['newEventInstructor']) && isset($_POST['newEventStartDate'])
        && isset($_POST['newEventStartHour']) && isset($_POST['newEventEndDate']) && isset($_POST['newEventEndHour']))
    {
        // CHECK FOR EMPTY FIELDS
        if (empty($_POST['newEventName']) || empty($_POST['newEventDescription']) || empty($_POST['newEventStartDate']) || empty($_POST['newEventStartHour'])
            || empty($_POST['newEventEndDate']) || empty($_POST['newEventEndHour']))
        {
            echo 'השלם/י את השדות החסרים';
            exit;
        }

        $day = date('w');
        $weekStart = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('Y-m-d', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");

        $startDate = str_replace('-', '/', $_POST['newEventStartDate']);
        $startDate = date('Y-m-d', strtotime($startDate)).' '.$_POST['newEventStartHour'];
        
        $endDate = str_replace('-', '/', $_POST['newEventEndDate']);
        $endDate = date('Y-m-d', strtotime($endDate)).' '.$_POST['newEventEndHour'];

        // VALIDATE START DATE
        if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($startDate)) 
        {
            echo 'שגיאה: זמן שגוי עבור תחילת האירוע';
            exit;
        }

        // VALIDATE START DATE
        if (strtotime($startDate) > strtotime($endDate)) 
        {
            echo 'שגיאה: זמן שגוי עבור סיום האירוע';
            exit;
        }
        
        $needInstructor = 'NO';
        $instructorId = -1;

        // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS EVENT
        if ($_POST['newEventInstructor'] != 'ללא מדריך')
        {
            // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS EVENT, (AGAINST OTHER CIRCLES)
            $instructorIdNumber = explode(' - ', $_POST['newEventInstructor'])[1];
            $needInstructor = 'YES';

            // SELECT THE ID OF THE INSTRUCTOR
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            $stmt = $pdo->prepare("SELECT InstructorId FROM instructors WHERE BINARY IdNumber=?");
            $stmt->execute([$instructorIdNumber]); 
            $instructorIdResults = $stmt->fetchAll(PDO::FETCH_ASSOC);        
            foreach ($instructorIdResults as $row) {
                $instructorId = $row['InstructorId'];
            }
            $pdo = null; 

            if ($instructorId == -1)
            {
                echo 'המדריך ששובץ עבור אירוע זה לא קיים במערכת';
                exit;
            }

            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate>=?");
            $stmt->execute([$instructorId, $currentDateTime]); 
            $currentInstructorCircles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            foreach ($currentInstructorCircles as $row) 
            {
                if (strtotime($startDate) <= strtotime($row['EndDate']) && strtotime($startDate) >= strtotime($row['StartDate']))
                {
                    echo 'המדריך שנבחר עבור אירוע זה מעביר חוג אחר בזמנים שנבחרו עבור אירוע זה';
                    exit;
                }

                if (strtotime($endDate) <= strtotime($row['EndDate']) && strtotime($endDate) >= strtotime($row['StartDate']))
                {
                    echo 'המדריך שנבחר עבור אירוע זה מעביר חוג אחר בזמנים שנבחרו עבור אירוע זה';
                    exit;
                }

                if (strtotime($row['StartDate']) >= strtotime($startDate) && strtotime($endDate) >= strtotime($row['StartDate']))
                {
                    echo 'המדריך שנבחר עבור אירוע זה מעביר חוג אחר בזמנים שנבחרו עבור אירוע זה';
                    exit;
                }
            }    
            
            // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS EVENT, (AGAINST OTHER EVENTS)
            $pdo = UTILITIES::PDO_DB_Connection('school_events');
            $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM events_table WHERE BINARY NeedInstructor=? AND EventInstructorId=? AND EndDate>=?");
            $stmt->execute(['YES', $instructorId, $currentDateTime]); 
            $currentInstructorEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            foreach ($currentInstructorEvents as $row) 
            {
                if (strtotime($startDate) <= strtotime($row['EndDate']) && strtotime($startDate) >= strtotime($row['StartDate']))
                {
                    echo 'המדריך שנבחר עבור אירוע זה מעביר אירוע בזמנים שנבחרו עבור אירוע זה';
                    exit;
                }

                if (strtotime($endDate) <= strtotime($row['EndDate']) && strtotime($endDate) >= strtotime($row['StartDate']))
                {
                    echo 'המדריך שנבחר עבור אירוע זה מעביר אירוע בזמנים שנבחרו עבור אירוע זה';
                    exit;
                }

                if (strtotime($row['StartDate']) >= strtotime($startDate) && strtotime($endDate) >= strtotime($row['StartDate']))
                {
                    echo 'המדריך שנבחר עבור אירוע זה מעביר אירוע בזמנים שנבחרו עבור אירוע זה';
                    exit;
                }
            }
        }

        // UPLOAD THE CIRCLE TO THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('school_events');
        $columns = $instructorId == -1 ? '(EventName,EndDate,StartDate,EventDescription,NeedInstructor) VALUES (?,?,?,?,?)' :
            '(EventName,EndDate,StartDate,EventInstructorId,EventDescription,NeedInstructor) VALUES (?,?,?,?,?,?)';
        $values = $instructorId == -1 ? [$_POST['newEventName'], $endDate, $startDate, $_POST['newEventDescription'], 'NO'] : 
            [$_POST['newEventName'], $endDate, $startDate, $instructorId, $_POST['newEventDescription'], 'YES'];
        $stmt = $pdo->prepare('INSERT INTO events_table '.$columns);
        $stmt->execute($values); 
        $pdo = null;

        echo 'האירוע נוסף בהצלחה';
        exit;
    }
    
    if (isset($_POST['requestType']) && isset($_POST['wantedCircleId']) && isset($_POST['targetAction']))
    {
        $day = date('w');
        $weekStart = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('Y-m-d', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");

        // RETURN THE CIRCLE DATA FOR STUDENT REGISTRATION OR EDIT BY THE STUDENT
        if (($_POST['targetAction'] == 'register' || $_POST['targetAction'] == 'change') && isset($_SESSION['StudentId']) && $_POST['requestType'] == 'getCircleSchedule')
        {
            // SELECT ALL THE MEETINGS OF THIS CIRCLE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT MeetingId,StartDate,EndDate,WeekDay FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
            $stmt->execute([$_POST['wantedCircleId']]); 
            $desiredCircleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$desiredCircleMeetingsResult || count($desiredCircleMeetingsResult) == 0)
            {
                echo 'לא ניתן להירשם לחוג המבוקש';
                exit;
            }

            // ORGANIZE THE CIRCLE MEETINGS BY DAYES IN AN ARRAY
            $desiredCircleMeetingsArray = [[], [], [], [], [], [], []];
            $data = 'SUCCESS'; $registeredMeetings = '';
            foreach ($desiredCircleMeetingsResult as $dcmKey => $dcmValue)
            {
                // CHECK IF THIS STUDENT IS REGISTERED TO THIS MEETING
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY StudentId=? AND MeetingId=?");
                $stmt->execute([$_SESSION['StudentId'], $dcmValue['MeetingId']]); 
                $studentRegisteredResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                $action = 'בחר/י';
                if (count($studentRegisteredResult) > 0)
                {
                    $action = 'הסר/י';
                    $comma = $registeredMeetings == '' ? '' : ',';
                    $registeredMeetings .= $comma.$dcmKey;
                }
                
                $meetingDate = explode(' ', $dcmValue['StartDate'])[0];
                $meetingDate = str_replace('-', '/', $meetingDate);
                $meetingDate = date('d/m/Y', strtotime($meetingDate));  
                $startHour = explode(' ', $dcmValue['StartDate'])[1];
                $endHour = explode(' ', $dcmValue['EndDate'])[1];

                array_push($desiredCircleMeetingsArray[$dcmValue['WeekDay']], [$meetingDate, $startHour, $endHour, $action]);
            }
            $data .= $registeredMeetings.'|
            <div class="circle-meetings-outer-wrapper">';

            foreach ($desiredCircleMeetingsArray as $dcmKey => $dcmValue)
            {
                $hDay = 'יום ראשון';
                if ($dcmKey == 1) { $hDay = 'יום שני'; }
                if ($dcmKey == 2) { $hDay = 'יום שלישי'; }
                if ($dcmKey == 3) { $hDay = 'יום רביעי'; }
                if ($dcmKey == 4) { $hDay = 'יום חמישי'; }
                if ($dcmKey == 5) { $hDay = 'יום ששי'; }
                if ($dcmKey == 6) { $hDay = 'יום שבת'; }

                $offset = $dcmKey - $day;
                $unixDate = date('Y-m-d', strtotime('+'.$offset.' days'));
                if ($offset < 0) { $unixDate = date('Y-m-d', strtotime($offset.' days')); }
                $date = str_replace('-', '/', $unixDate);
                $date = date('d/m/Y', strtotime($date));

                $top = $dcmKey == 0 ? ' style="margin-top: 0;"' : '';
                $data .=
                '<div class="circle-registration-day"'.$top.'>'.$hDay.'&nbsp;-&nbsp;'.$date.'</div>';

                if (count($dcmValue) == 0) {
                    $data .='<div class="meeting-w">לא נמצאו מפגשים ליום זה</div>';
                }
                else
                {
                    foreach ($dcmValue as $mKey => $mValue)
                    {
                        $startDate = str_replace('/', '-', $mValue[0]);
                        $startDate = date('Y-m-d', strtotime($startDate));  
                        $start = $startDate.' '.$mValue[1];
                        $class = strtotime($currentDateTime) > strtotime($start) ? 
                            'disabled-choose-meeting' : 'choose-meeting'; 
        
                        $data .=
                        '<div class="meeting-w" style="height: 30px;">
                            <div class="'.$class.' animated-transition noselect">'.$mValue[3].'</div>
                            <div class="meeting-times">מ: '.$mValue[1].' עד: '.$mValue[2].'</div>
                        </div>';
                    }
                }
            }
            
            $data .= '</div>';
            echo $data;
            exit;
        }

        // RETURN THE CIRCLE DATA FOR STUDENT REGISTRATION BY THE INSTRUCTOR
        if ($_POST['targetAction'] == 'registerByInstructor' && isset($_SESSION['InstructorId']) && $_POST['requestType'] == 'getCircleSchedule')
        {
            // SELECT ALL THE MEETINGS OF THIS CIRCLE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT MeetingId,StartDate,EndDate,WeekDay FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
            $stmt->execute([$_POST['wantedCircleId']]); 
            $desiredCircleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$desiredCircleMeetingsResult || count($desiredCircleMeetingsResult) == 0)
            {
                echo 'לא ניתן להירשם לחוג המבוקש';
                exit;
            }

            // ORGANIZE THE CIRCLE MEETINGS BY DAYES IN AN ARRAY
            $desiredCircleMeetingsArray = [[], [], [], [], [], [], []];
            $data = 'SUCCESS'; $registeredMeetings = '';
            foreach ($desiredCircleMeetingsResult as $dcmKey => $dcmValue)
            {
                // CHECK IF THIS STUDENT IS REGISTERED TO THIS MEETING
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY StudentId=? AND MeetingId=?");
                $stmt->execute([$_POST['studentId'], $dcmValue['MeetingId']]); 
                $studentRegisteredResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                $action = 'בחר/י';
                if (count($studentRegisteredResult) > 0)
                {
                    $action = 'הסר/י';
                    $comma = $registeredMeetings == '' ? '' : ',';
                    $registeredMeetings .= $comma.$dcmKey;
                }
                
                $meetingDate = explode(' ', $dcmValue['StartDate'])[0];
                $meetingDate = str_replace('-', '/', $meetingDate);
                $meetingDate = date('d/m/Y', strtotime($meetingDate));  
                $startHour = explode(' ', $dcmValue['StartDate'])[1];
                $endHour = explode(' ', $dcmValue['EndDate'])[1];

                array_push($desiredCircleMeetingsArray[$dcmValue['WeekDay']], [$meetingDate, $startHour, $endHour, $action]);
            }
            $data .= $registeredMeetings.'|
            <div class="circle-meetings-outer-wrapper">';

            foreach ($desiredCircleMeetingsArray as $dcmKey => $dcmValue)
            {
                $hDay = 'יום ראשון';
                if ($dcmKey == 1) { $hDay = 'יום שני'; }
                if ($dcmKey == 2) { $hDay = 'יום שלישי'; }
                if ($dcmKey == 3) { $hDay = 'יום רביעי'; }
                if ($dcmKey == 4) { $hDay = 'יום חמישי'; }
                if ($dcmKey == 5) { $hDay = 'יום ששי'; }
                if ($dcmKey == 6) { $hDay = 'יום שבת'; }

                $offset = $dcmKey - $day;
                $unixDate = date('Y-m-d', strtotime('+'.$offset.' days'));
                if ($offset < 0) { $unixDate = date('Y-m-d', strtotime($offset.' days')); }
                $date = str_replace('-', '/', $unixDate);
                $date = date('d/m/Y', strtotime($date)); 

                $data .=
                '<div class="circle-registration-day">'.$hDay.'&nbsp;-&nbsp;'.$date.'</div>';

                if (count($dcmValue) == 0) {
                    $data .='<div class="meeting-w">לא נמצאו מפגשים ליום זה</div>';
                }
                else
                {
                    foreach ($dcmValue as $mKey => $mValue)
                    {
                        $startDate = str_replace('/', '-', $mValue[0]);
                        $startDate = date('Y-m-d', strtotime($startDate));  
                        $start = $startDate.' '.$mValue[1];
                        $class = strtotime($currentDateTime) > strtotime($start) ? 
                            'disabled-choose-meeting' : 'choose-meeting'; 

                        $data .=
                        '<div class="meeting-w" style="height: 30px;">
                            <div class="'.$class.' animated-transition noselect">'.$mValue[3].'</div>
                            <div class="meeting-times">מ: '.$mValue[1].' עד: '.$mValue[2].'</div>
                        </div>';
                    }
                }
            }

            $data .= '</div>';
            echo $data;
            exit;
        }
    }

    if (isset($_POST['listType']))
    {
        $day = date('w');
        $weekStart = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('Y-m-d', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $monthStart = date('Y-m-01').' 00:00:00';        
        $currentDateTime = date("Y-m-d H:i:s");

        // RETURN THE LIST OF ALL STUDENTS THAT ARE REGISTERED TO CIRCLES WITH THIS INSTRUCTOR 
        if ($_POST['listType'] == 'studentsForInstructor' && isset($_SESSION['InstructorId']))
        {
            // GET ALL THE CIRCLES WITH THIS INSTRUCTOR
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT CircleId,CircleName FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate>=?");
            $stmt->execute([$_SESSION['InstructorId'], $currentDateTime]); 
            $circlesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$circlesResults || count($circlesResults) == 0)
            {
                echo 'SUCCESS
                <div class="alt-text">לא קיימים חוגים נוספים בשבוע הנוכחי בהנחייתך</div>';
                exit;
            }

            $studentsArray = []; /* [ [CircleName, [participants ids]], [CircleName, [participants ids]] ... ] */

            // SELECT THE MEETING OF EACH CIRCLE
            foreach ($circlesResults as $cKey => $cValue)
            {
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT MeetingId FROM circle_meetings WHERE BINARY CircleId=?");
                $stmt->execute([$cValue['CircleId']]); 
                $meetingsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                // SELECT THE PARTICIPANTS OF EACH MEETING
                $inserted = false;
                foreach ($meetingsResults as $mKey => $mValue)
                {
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");
                    $stmt->execute([$mValue['MeetingId']]); 
                    $participantsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                    $pdo = null;

                    if (count($participantsResults) > 0)
                    {
                        if ($inserted == false)
                        {
                            array_push($studentsArray, [$cValue['CircleName'], []]);                            
                            $inserted = true;
                        } 
                        foreach ($participantsResults as $pKey => $pValue) 
                        {
                            if (!in_array($pValue, $studentsArray[count($studentsArray) - 1][1])) {
                                array_push($studentsArray[count($studentsArray) - 1][1], $pValue);
                            }                            
                        }                 
                    }
                }
            }

            $students = []; 
            $circles = [];

            foreach ($studentsArray as $sKey => $sValue)
            {
                $studentIds = $sValue[1];
                $circleName = $sValue[0];

                foreach ($studentIds as $siKey => $siValue)
                {
                    if (!in_array($siValue, $students))
                    {
                        array_push($students, $siValue);
                        array_push($circles, $circleName);
                    }
                    else
                    {
                        $indexOfStudent = array_search($siValue, $students);
                        $circles[$indexOfStudent] .= ', '.$circleName;
                    }
                }
            }
            
            // SELECT THE DETAILS OF EACH STUDENT
            $studentDetails = [];
            foreach ($students as $sKey => $sValue)
            {
                $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM students WHERE BINARY StudentId=?");
                $stmt->execute([$sValue['StudentId']]); 
                $studentDetailsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                if (!$studentDetailsResults || count($studentDetailsResults) == 0) {
                    array_push($studentDetails, 'התלמיד לא קיים במערכת');
                }
                else {
                    array_push($studentDetails, $studentDetailsResults[0]['FirstName'].' '.$studentDetailsResults[0]['LastName'].' - '.$studentDetailsResults[0]['IdNumber']);
                }
            }

            if (count($studentDetails) == 0)
            {
                echo 'עדיין לא נרשמו תלמידים לחוגים בהדרכתך';
                exit;
            }

            // PREPARE DATA
            $data = 'SUCCESS
            <div class="student-list-items-w">';
            foreach ($studentDetails as $sdKey => $sdValue)
            {
                $top = $sdKey == 0 ? ' style="margin-top: 0;"' : '';
                $data .= 
                '<div class="circle-students-list-item"'.$top.'>
                    <div class="student-name-li-w">'.$sdValue.'</div>
                    <div class="student-circles-li-w">'.$circles[$sdKey].'</div>
                </div>';
            }            
            $data .=
            '</div>';

            echo $data;
            exit;            
        }

        // RETURN A LIST OF ALL THE STUDENT OF AN INSTRUCTOR FOR SENDING A MESSAGE
        if ($_POST['listType'] == 'selectStudentsForInstructor' && isset($_SESSION['InstructorId']))
        {
            // GET ALL THE CIRCLES WITH THIS INSTRUCTOR
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT CircleId,CircleName FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate>=?");
            $stmt->execute([$_SESSION['InstructorId'], $currentDateTime]); 
            $circlesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$circlesResults || count($circlesResults) == 0)
            {
                echo 'SUCCESS
                <div class="alt-text" style="margin-top: 25px;">לא נמצאו תלמידים עבורך</div>';
                exit;
            }

            $studentsArray = []; /* [ [CircleName, [participants ids]], [CircleName, [participants ids]] ... ] */

            // SELECT THE MEETING OF EACH CIRCLE
            foreach ($circlesResults as $cKey => $cValue)
            {
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT MeetingId FROM circle_meetings WHERE BINARY CircleId=?");
                $stmt->execute([$cValue['CircleId']]); 
                $meetingsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                // SELECT THE PARTICIPANTS OF EACH MEETING
                $inserted = false;
                foreach ($meetingsResults as $mKey => $mValue)
                {
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");
                    $stmt->execute([$mValue['MeetingId']]); 
                    $participantsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                    $pdo = null;

                    if (count($participantsResults) > 0)
                    {
                        if ($inserted == false)
                        {
                            array_push($studentsArray, [$cValue['CircleName'], []]);                            
                            $inserted = true;
                        } 
                        foreach ($participantsResults as $pKey => $pValue) 
                        {
                            if (!in_array($pValue, $studentsArray[count($studentsArray) - 1][1])) {
                                array_push($studentsArray[count($studentsArray) - 1][1], $pValue);
                            }                            
                        }                 
                    }
                }
            }

            $students = []; 
            $circles = [];

            foreach ($studentsArray as $sKey => $sValue)
            {
                $studentIds = $sValue[1];
                $circleName = $sValue[0];

                foreach ($studentIds as $siKey => $siValue)
                {
                    if (!in_array($siValue, $students))
                    {
                        array_push($students, $siValue);
                        array_push($circles, $circleName);
                    }
                    else
                    {
                        $indexOfStudent = array_search($siValue, $students);
                        $circles[$indexOfStudent] .= ', '.$circleName;
                    }
                }
            }
            
            // SELECT THE DETAILS OF EACH STUDENT
            $studentDetails = [];
            foreach ($students as $sKey => $sValue)
            {
                $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber,StudentId FROM students WHERE BINARY StudentId=?");
                $stmt->execute([$sValue['StudentId']]); 
                $studentDetailsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                if (!$studentDetailsResults || count($studentDetailsResults) == 0) {
                    continue; /* array_push($studentDetails, 'התלמיד לא קיים במערכת'); */ 
                }
                else {
                    array_push($studentDetails, $studentDetailsResults[0]['FirstName'].' '.$studentDetailsResults[0]['LastName'].' - '.$studentDetailsResults[0]['IdNumber'].'|'.$studentDetailsResults[0]['StudentId']);
                }
            }

            if (count($studentDetails) == 0)
            {
                echo 'עדיין לא נרשמו תלמידים לחוגים בהדרכתך';
                exit;
            }

            // PREPARE DATA
            $data = 'SUCCESS
            <div class="student-list-items-w" style="margin-top: 25px;">';
            foreach ($studentDetails as $sdKey => $sdValue)
            {
                $studentId = substr($sdValue, strpos($sdValue, '|') + 1);
                $studentData = substr($sdValue, 0, strpos($sdValue, '|'));
                $top = $sdKey == 0 ? ' style="margin-top: 0;"' : '';
                $data .= 
                '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'">
                    <div class="student-name-li-w" style="width: calc(100% - 100px);">'.$studentData.'</div>
                    <div class="student-circles-li-w" style="width: calc(100% - 100px);">'.$circles[$sdKey].'</div>  
                    <div class="remove-entity-btn noselect animated-transition" selectForMsg="'.$studentId.'"><a>בחר/י</a></div>                  
                </div>';
            }            
            $data .=
            '</div>';

            echo $data;
            exit;
        }

        // RETURN THE LIST OF ALL STUDENTS IN THE SYSTEM 
        if ($_POST['listType'] == 'studentsForManager' && isset($_SESSION['ManagerId']))
        {
            // GET THE STUDENTS
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            $stmt = $pdo->prepare("SELECT StudentId,IdNumber,FirstName,LastName FROM students");
            $stmt->execute(); 
            $studentsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (count($studentsResults) == 0) {
                echo 'SUCCESS
                <div class="alt-text">לא קיימים תלמידים במערכת</div>';
                exit;
            }

            $data = 'SUCCESS
            <div class="student-list-items-w">';
            foreach ($studentsResults as $studentKey => $studentValue)
            {
                $top = $studentKey == 0 ? ' margin-top: 0;' : '';
                $data .= 
                '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'" studentId="'.$studentValue['StudentId'].'">
                    <div class="student-name-li-w" style="width: calc(100% - 100px);">'.$studentValue['FirstName'].' '.$studentValue['LastName'].'</div>
                    <div class="student-circles-li-w" style="width: calc(100% - 100px);">'.$studentValue['IdNumber'].'</div>  
                    <div class="remove-entity-btn noselect animated-transition" remove="student"><a>הסר/י</a></div>                  
                </div>';
            }            
            $data .=
            '</div>';

            echo $data;
            exit;
        }

        // RETURN THE LIST OF ALL INSTRUCTORS IN THE SYSTEM
        if ($_POST['listType'] == 'instructorsForManager' && isset($_SESSION['ManagerId']))
        {
            // GET THE INSTRUCTORS
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            $stmt = $pdo->prepare("SELECT InstructorId,IdNumber,FirstName,LastName FROM instructors");
            $stmt->execute(); 
            $instructorsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (count($instructorsResults) == 0) {
                echo 'SUCCESS
                <div class="alt-text">לא קיימים מדריכים במערכת</div>';
                exit;
            }

            $data = 'SUCCESS
            <div class="student-list-items-w">';
            foreach ($instructorsResults as $instructorKey => $instructorValue)
            {
                // GET ALL THE CIRCLES BY THIS INSTRUCTOR
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT CircleId,CircleName FROM circles_table WHERE StartDate>=? AND CircleInstructorId=?");
                $stmt->execute([$weekStart, $instructorValue['InstructorId']]); 
                $circlesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                $circles = '';
                foreach ($circlesResults as $cKey => $cValue)
                {
                    $circles .= $cValue['CircleName'];
                    if ($cKey < count($circlesResults) - 1) {
                        $circles .= ', ';
                    }
                }

                if ($circles == '') {
                    $circles = 'לא מעביר חוגים לשבוע זה';
                }

                $top = $instructorKey == 0 ? ' margin-top: 0;' : '';
                $data .= 
                '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'" instructorId="'.$instructorValue['InstructorId'].'">
                    <div class="student-name-li-w" style="width: calc(100% - 100px);">'.$instructorValue['FirstName'].' '.$instructorValue['LastName'].'</div>
                    <div class="student-circles-li-w" style="width: calc(100% - 100px);">'.$instructorValue['IdNumber'].'</div> 
                    <div class="student-circles-li-w" style="width: calc(100% - 100px);">חוגים:&nbsp;'.$circles.'</div>  
                    <div class="remove-entity-btn noselect animated-transition" remove="instructor"><a>הסר/י</a></div>                  
                </div>';
            }            
            $data .=
            '</div>';

            echo $data;
            exit;
        } 

        // RETURN THE LIST OF ALL MESSAGES SENT FROM THE MANAGER
        if ($_POST['listType'] == 'messagesOfManager' && isset($_SESSION['ManagerId']))
        {
            // GET THE MESSAGES
            $pdo = UTILITIES::PDO_DB_Connection('school_messages');
            $stmt = $pdo->prepare("SELECT * FROM messages_table WHERE BINARY ToSpecificStudent=? AND FromInstructor=? AND CreationDate>=?");
            $stmt->execute([-1, -1, $monthStart]); 
            $messagesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$messagesResults || count($messagesResults) == 0) {
                echo 'SUCCESS
                <div class="alt-text">לא קיימים הודעות במערכת</div>';
                exit;
            }

            $data = 'SUCCESS
            <div class="student-list-items-w">';
            foreach ($messagesResults as $messageKey => $messageValue)
            {
                $creationDate1 = substr($messageValue['CreationDate'], 0, strpos($messageValue['CreationDate'], ' '));
                $creationDate1 = str_replace('-', '/', $creationDate1);
                $creationDate1 = date('d/m/Y', strtotime($creationDate1));
                $creationDate2 = substr($messageValue['CreationDate'], strpos($messageValue['CreationDate'], ' ') + 1);
                
                $type = $messageValue['Active'] == 'YES' ? 'remove' : 'activate';
                $text = 'הסר/י';
                if ($messageValue['Active'] == 'NO') { $text = 'שחזר/י'; }

                $top = $messageKey == 0 ? ' margin-top: 0;' : '';
                $data .= 
                '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'" messageId="'.$messageValue['MessageId'].'">
                    <div class="student-name-li-w" style="width: calc(100% - 100px);">'.htmlentities($messageValue['MessageText']).'</div>';
                    if ($messageValue['ToSpecificInstructor'] != -1) 
                    {
                        // SELECT THE DETAILS OF THE INSTRUCTOR WHO RECEIVED THIS MESSAGE
                        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                        $stmt = $pdo->prepare("SELECT * FROM instructors WHERE BINARY InstructorId=?");
                        $stmt->execute([$messageValue['ToSpecificInstructor']]); 
                        $instructorResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                        $pdo = null;

                        $instName = 'נשלח ל: המדריך לא קיים במערכת'; 
                        if (count($instructorResults) > 0) {
                            $instName = $instructorResults[0]['FirstName'].' '.$instructorResults[0]['LastName'].' - '.$instructorResults[0]['IdNumber'];
                        }
                        $data .= '<div class="student-circles-li-w" style="width: calc(100% - 100px); color: #454545;">'.$instName.'</div>';
                    }
                    else {
                        $data .= '<div class="student-circles-li-w" style="width: calc(100% - 100px); color: #454545;">נשלח לכולם</div>';
                    }
                    $data .=
                    '<div class="student-circles-li-w" style="width: calc(100% - 100px);">'.$creationDate1.'&nbsp;'.$creationDate2.'</div> 
                    <div class="remove-entity-btn noselect animated-transition" message="'.$type.'"><a>'.$text.'</a></div>                  
                </div>';
            }            
            $data .=
            '</div>';

            echo $data;
            exit;
        }

        // RETURN THE LIST OF ALL MESSAGES SENT FROM AN INSTRUCTOR
        if ($_POST['listType'] == 'messagesOfInstructor' && isset($_SESSION['InstructorId']))
        {
            // GET THE MESSAGES
            $pdo = UTILITIES::PDO_DB_Connection('school_messages');
            $stmt = $pdo->prepare("SELECT * FROM messages_table WHERE BINARY FromInstructor=? AND CreationDate>=?");
            $stmt->execute([$_SESSION['InstructorId'], $weekStart]); 
            $messagesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$messagesResults || count($messagesResults) == 0) {
                echo 'SUCCESS
                <div class="alt-text">לא קיימות במערכת הודעות שנשלחו ממך בשבוע בנוכחי</div>';
                exit;
            }

            $data = 'SUCCESS
            <div class="student-list-items-w">';
            foreach ($messagesResults as $messageKey => $messageValue)
            {
                // SELECT THE STUDENT DETAILS
                $studentName = '';
                if ($messageValue['ToSpecificStudent'] != -1)
                {
                    $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                    $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM students WHERE BINARY StudentId=?");
                    $stmt->execute([$messageValue['ToSpecificStudent']]); 
                    $studentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                    $pdo = null;

                    if (count($studentResults) > 0) {
                        $studentName = 'נשלח ל: '.$studentResults[0]['FirstName'].' '.$studentResults[0]['LastName'].' - '.$studentResults[0]['IdNumber'];
                    }
                    else {
                        $studentName = 'נשלח ל: הסטודנט לא קיים במערכת';
                    }
                }                

                $creationDate1 = substr($messageValue['CreationDate'], 0, strpos($messageValue['CreationDate'], ' '));
                $creationDate1 = str_replace('-', '/', $creationDate1);
                $creationDate1 = date('d/m/Y', strtotime($creationDate1));
                $creationDate2 = substr($messageValue['CreationDate'], strpos($messageValue['CreationDate'], ' ') + 1);
                
                $type = $messageValue['Active'] == 'YES' ? 'remove' : 'activate';
                $text = 'הסר/י';
                if ($messageValue['Active'] == 'NO') { $text = 'שחזר/י'; }

                $top = $messageKey == 0 ? ' margin-top: 0;' : '';
                $data .= 
                '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'" messageId="'.$messageValue['MessageId'].'">
                    <div class="student-name-li-w" style="width: calc(100% - 100px);">'.htmlentities($messageValue['MessageText']).'</div>';
                    if ($studentName != '') {
                        $data .= '<div class="student-circles-li-w" style="width: calc(100% - 100px); color: #454545;">'.$studentName.'</div>';
                    }
                    $data .=
                    '<div class="student-circles-li-w" style="width: calc(100% - 100px);">'.$creationDate1.'&nbsp;'.$creationDate2.'</div> 
                    <div class="remove-entity-btn noselect animated-transition" message="'.$type.'"><a>'.$text.'</a></div>                  
                </div>';
            }            
            $data .=
            '</div>';

            echo $data;
            exit;
        }

        // RETURN THE SPECIFIC CIRCLE MEETING ATTENDEES FOR ATENDANCE REPORT
        if ($_POST['listType'] == 'specificCircleMeetingParticipants' && isset($_POST['participantsCircleId']) && isset($_POST['participantsMeetingId']))
        {
            // SELECT ALL THE MEETINGS OF THIS CIRCLE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT MeetingId FROM circle_meetings WHERE BINARY CircleId=?");
            $stmt->execute([$_POST['participantsCircleId']]); 
            $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$circleMeetingsResult || count($circleMeetingsResult) == 0)
            {
                echo 'SUCCESS
                <div class="alt-text">החוג כבר לא קיים במערכת</div>';
                exit;
            }

            // SELECT THE WANTED MEETING
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT MeetingId FROM circle_meetings WHERE BINARY MeetingId=?");
            $stmt->execute([$_POST['participantsMeetingId']]); 
            $circleMeetingResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$circleMeetingResult || count($circleMeetingResult) == 0)
            {
                echo 'SUCCESS
                <div class="alt-text">המפגש כבר לא קיים במערכת</div>';
                exit;
            }

            // SELECT ALL THE PARTICIPANTS OF THE WANTED MEETING
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");
            $stmt->execute([$_POST['participantsMeetingId']]); 
            $meetingParticipantsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            $meetingParticipants = [];
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            foreach ($meetingParticipantsResult as $mpKey => $mpValue)
            {
                // SELECT THE DETAILS OF THE STUDENTS                
                $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM students WHERE BINARY StudentId=?");
                $stmt->execute([$mpValue['StudentId']]); 
                $studentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);                  
                
                if (count($studentResults) > 0)
                {
                    array_push($meetingParticipants, [$studentResults[0]['FirstName'], $studentResults[0]['LastName'], 
                        $studentResults[0]['IdNumber']]);
                }     
                else 
                {
                    array_push($meetingParticipants, ['התלמיד לא קיים במערכת']);
                }           
            }
            $pdo = null;

            $data = 'SUCCESS
            <div class="student-list-items-w">';
            foreach ($meetingParticipants as $mpKey => $mpValue)
            {
                $top = $mpKey == 0 ? ' margin-top: 0;' : '';
                if (count($mpValue) > 1)
                {
                    $data .= 
                    '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'">
                        <div class="student-name-li-w">'.$mpValue[0].' '.$mpValue[1].'</div>
                        <div class="student-circles-li-w">'.$mpValue[2].'</div>         
                    </div>';
                }  
                else
                {
                    $data .= 
                    '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'">
                        <div class="student-name-li-w">'.$mpValue[0].'</div>     
                    </div>';
                }              
            }            
            $data .=
            '</div>';

            echo $data;
            exit;
        }

        // RETURN THE COMMENTS FOR SPECIFIC CIRCLE
        if ($_POST['listType'] == 'specificCircleComments' && isset($_POST['commentsCircleId']))
        {
            // GET THE COMMENTS
            $commentsResults = [];
            if (isset($_SESSION['ManagerId']))
            {
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT * FROM circle_comments_table WHERE BINARY CircleId=?");
                $stmt->execute([$_POST['commentsCircleId']]); 
                $commentsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;
            }
            else
            {
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY CircleId=?");
                $stmt->execute([$_POST['commentsCircleId']]); 
                $circlesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  

                if (count($circlesResults) > 0 && strtotime($circlesResults[0]['EndDate']) >= strtotime($weekStart) 
                    && strtotime($currentDateTime) > strtotime($circlesResults[0]['EndDate']))
                {
                    if (isset($_SESSION['InstructorId']))
                    {
                        $stmt = $pdo->prepare("SELECT * FROM circle_comments_table WHERE BINARY CircleId=?");
                        $stmt->execute([$_POST['commentsCircleId']]); 
                    }
                    else if (isset($_SESSION['StudentId']))
                    {
                        $stmt = $pdo->prepare("SELECT * FROM circle_comments_table WHERE BINARY CircleId=? AND FromStudentId!=?");
                        $stmt->execute([$circlesResults[0]['CircleId'], -1]);
                    }                    
                    $commentsResults = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                }                
                $pdo = null;
            } 

            $marginTop = isset($_SESSION['ManagerId']) ? '' : ' style="margin-top: 20px;"';
            
            if (count($commentsResults) > 0)
            {
                $data = 'SUCCESS
                <div class="student-list-items-w"'.$marginTop.'>';
                foreach ($commentsResults as $cKey => $cValue)
                {
                    if (isset($_SESSION['InstructorId']) || isset($_SESSION['ManagerId']))
                    {
                        // SELECT COMMENTER DETAILS
                        $commenterResults = [];
                        if (intval($cValue['FromStudentId']) != -1)
                        {
                            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                            $stmt = $pdo->prepare("SELECT * FROM students WHERE BINARY StudentId=?");
                            $stmt->execute([$cValue['FromStudentId']]); 
                            $commenterResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                            $pdo = null;
                        } 
                        else 
                        {
                            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                            $stmt = $pdo->prepare("SELECT * FROM instructors WHERE BINARY InstructorId=?");
                            $stmt->execute([$cValue['FromInstructorId']]); 
                            $commenterResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                            $pdo = null;
                        }                       

                        $commenterDetails = '';
                        if (!$commenterResults || count($commenterResults) == 0) 
                        {
                            if (intval($cValue['FromStudentId']) != -1) {
                                $commenterDetails = 'הסטודנט לא קיים במערכת';
                            }
                            else {
                                $commenterDetails = 'המדריך לא קיים במערכת';
                            }
                        }
                        else 
                        {
                            $commenterDetails = '';
                            if (intval($cValue['FromStudentId']) == -1) {
                                $commenterDetails = 'מדריך החוג - ';
                            }
                            $commenterDetails .= $commenterResults[0]['FirstName'].' '.$commenterResults[0]['LastName'].' - '.$commenterResults[0]['IdNumber'];
                        }

                        $top = $cKey == 0 ? ' margin-top: 0;' : '';
                        $data .= 
                        '<div class="circle-students-list-item" style="border-bottom: 1px solid #454545; direction: rtl; text-align: right;'.$top.'">
                            <div class="student-name-li-w">'.$commenterDetails.'</div>
                            <div class="student-circles-li-w" style="color: #454545;">דירוג: '.$cValue['Rate'].'</div>';
                            if (intval($cValue['OnStudentId']) != -1)
                            {
                                // SELECT THE STUDENT DETAILS
                                $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                                $stmt = $pdo->prepare("SELECT IdNumber,FirstName,LastName FROM students WHERE BINARY StudentId=?");
                                $stmt->execute([$cValue['OnStudentId']]); 
                                $studentResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                                $pdo = null;

                                $studentData = 'אודות התלמיד: התלמיד לא קיים במערכת';
                                if (count($studentResult) > 0) {
                                    $studentData = 'אודות התלמיד: '.$studentResult[0]['FirstName'].' '.$studentResult[0]['LastName'].' - '.$studentResult[0]['IdNumber'];
                                }

                                $data .=
                                '<div class="student-circles-li-w" style="color: #454545;">'.$studentData.'</div> ';
                            }
                            $data .=
                            '<div class="student-circles-li-w" style="margin-bottom: 15px;">'.$cValue['Comment'].'</div>         
                        </div>';
                    }   
                    else
                    {
                        // SELECT STUDENT DETAILS
                        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                        $stmt = $pdo->prepare("SELECT * FROM students WHERE BINARY StudentId=?");
                        $stmt->execute([$cValue['FromStudentId']]); 
                        $commenterResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                        $pdo = null;

                        $commenterDetails = 'הסטודנט לא קיים במערכת';
                        if (count($commenterResults) > 0) {
                            $commenterDetails = $commenterResults[0]['FirstName'].' '.$commenterResults[0]['LastName'];
                        }

                        $top = $cKey == 0 ? ' margin-top: 0;' : '';
                        $data .= 
                        '<div class="circle-students-list-item" style="border-bottom: 1px solid #454545; direction: rtl; text-align: right;'.$top.'">
                            <div class="student-name-li-w">'.$commenterDetails.'</div>
                            <div class="student-circles-li-w" style="color: #454545;">דירוג: '.$cValue['Rate'].'</div>
                            <div class="student-circles-li-w" style="margin-bottom: 15px;">'.$cValue['Comment'].'</div>         
                        </div>';
                    }                 
                }  
                $data .=
                '</div>';
            }
            else 
            {
                $data = 'SUCCESS
                <div class="student-list-items-w"'.$marginTop.'>
                    <div class="alt-text">לא נמצאו במערכת משובים עבור חוג זה</div>
                </div>';
            }  

            echo $data;
            exit;
        }

        // RETURN THE CIRCLES LIST FOR THIS STUDENT
        if ($_POST['listType'] == 'circlesListForStudent' && isset($_SESSION['StudentId']))
        {
            // GET THE CIRCLES
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY StartDate>=? ORDER BY StartDate ASC");
            $stmt->execute([$weekStart]); 
            $circlesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$circlesResults || count($circlesResults) == 0)
            {
                echo 'SUCCESS
                <div class="alt-text">עדיין לא קיימים חוגים במערכת לשבוע הנוכחי</div>';
                exit;
            }

            $participatedCircles = []; /* [ [[circle details], [meeting details]] ] */
            foreach ($circlesResults as $cKey => $cValue)
            {
                // GET THE MEETINGS OF THIS CIRCLE
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
                $stmt->execute([$cValue['CircleId']]); 
                $circleMeetingsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                $circleAdded = false;

                // CHECK IF THIS STUDENT IS PARTICIPATING IN MEETINGS OF THIS CIRCLE
                foreach ($circleMeetingsResults as $cmKey => $cmValue)
                {
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY MeetingId=? AND StudentId=?");
                    $stmt->execute([$cmValue['MeetingId'], $_SESSION['StudentId']]); 
                    $circleParticipantsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                    $pdo = null;

                    // THIS STUDENT IS PARTICIPATING IN THIS MEETING
                    if (count($circleParticipantsResults) > 0)
                    {
                        if ($circleAdded === false) {
                            array_push($participatedCircles, [$cValue, [$cmValue]]);
                            $circleAdded = true;
                        }
                        else {
                            array_push($participatedCircles[count($participatedCircles) - 1][1], $cmValue);
                        }
                    }
                }
            }

            if (count($participatedCircles) > 0)
            {
                $data = 'SUCCESS
                <div class="student-list-items-w">';

                foreach ($participatedCircles as $pcKey => $pcValue)
                {
                    $circleDetails = $pcValue[0];
                    $meetingsDetailsArray = $pcValue[1];

                    // SELECT THE INSTRUCTOR NAME OF THIS CIRCLE
                    $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                    $stmt = $pdo->prepare("SELECT FirstName,LastName FROM instructors WHERE BINARY InstructorId=?");
                    $stmt->execute([$circleDetails['CircleInstructorId']]); 
                    $instructorResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                    $pdo = null;

                    $instructorName = '';
                    if (!$instructorResults || count($instructorResults) == 0) {
                        $instructorName = 'המדריך לא קיים במערכת';
                    }
                    else {
                        $instructorName = $instructorResults[0]['FirstName'].' '.$instructorResults[0]['LastName'];
                    }

                    $top = $pcKey == 0 ? ' style="margin-top: 0;"' : '';
                    $data .=
                    '<div class="participated-circle-item"'.$top.'>
                        <div class="participated-circle-name">'.$circleDetails['CircleName'].'</div>
                        <div class="participated-circle-item-txt" style="text-align: right;">שם המדריך: '.$instructorName.'</div>';

                    $currentDay = -1;                    
                    foreach ($meetingsDetailsArray as $mdKey => $mdValue)
                    {     
                        $hDay = 'יום ראשון';
                        if ($mdValue['WeekDay'] == 1) { $hDay = 'יום שני'; }
                        if ($mdValue['WeekDay'] == 2) { $hDay = 'יום שלישי'; }
                        if ($mdValue['WeekDay'] == 3) { $hDay = 'יום רביעי'; }
                        if ($mdValue['WeekDay'] == 4) { $hDay = 'יום חמישי'; }
                        if ($mdValue['WeekDay'] == 5) { $hDay = 'יום שישי'; }
                        if ($mdValue['WeekDay'] == 6) { $hDay = 'יום שבת'; }

                        $offset = $mdValue['WeekDay'] - $day;
                        $meetingDate = date('d/m/Y', strtotime('+'.$offset.' days'));
                        if ($offset < 0) { $meetingDate = date('d/m/Y', strtotime($offset.' days')); }

                        $start = explode(' ', $mdValue['StartDate'])[1];
                        $end = explode(' ', $mdValue['EndDate'])[1];

                        if ($currentDay != $mdValue['WeekDay']) 
                        {
                            $data .= '<div class="participated-circle-item-txt" style="color: #000; margin-top: 12px;">'.$hDay.' - '.$meetingDate.'</div>';
                            $currentDay = $mdValue['WeekDay'];
                        }
                        $data .= '<div class="participated-circle-item-txt">מ: '.$start.' עד: '.$end.'</div>';
                    }
                    $data .= '</div>';
                }

                $data .= 
                '</div>';

                echo $data;
                exit;
            }
            else
            {
                echo 'SUCCESS
                <div class="alt-text">עדיין לא נרשמת לחוגים עבור השבוע הנוכחי</div>';
                exit;
            }
        }

        // RETURN THE CIRCLES LIST FOR INSTRUCTOR
        if ($_POST['listType'] == 'circlesListForInstructor' && isset($_SESSION['InstructorId']))
        {
            // GET THE CIRCLES
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY StartDate>=? AND CircleInstructorId=?");
            $stmt->execute([$weekStart, $_SESSION['InstructorId']]); 
            $circlesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$circlesResults || count($circlesResults) == 0)
            {
                echo 'SUCCESS
                <div class="alt-text">לא קיימים חוגים נוספים בהדרכתך לשבוע זה</div>';
                exit;
            }
            
            $data = 'SUCCESS
            <div class="student-list-items-w">';
            foreach ($circlesResults as $cKey => $cValue)
            {
                // SELECT THE MEETINGS OF THIS CIRCLE
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
                $stmt->execute([$cValue['CircleId']]); 
                $meetingsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                $pdo = null;

                $top = $cKey == 0 ? ' style="margin-top: 0;"' : '';
                $data .=
                '<div class="participated-circle-item"'.$top.'>
                    <div class="participated-circle-name">'.$cValue['CircleName'].'</div>
                    <div class="participated-circle-item-txt" style="text-align: right;">שם המדריך: '.$_SESSION['FirstName'].' '.$_SESSION['LastName'].'</div>';

                $currentDay = -1;
                foreach ($meetingsResults as $mKey => $mValue)
                {
                    $hDay = 'יום ראשון';
                    if ($mValue['WeekDay'] == 1) { $hDay = 'יום שני'; }
                    if ($mValue['WeekDay'] == 2) { $hDay = 'יום שלישי'; }
                    if ($mValue['WeekDay'] == 3) { $hDay = 'יום רביעי'; }
                    if ($mValue['WeekDay'] == 4) { $hDay = 'יום חמישי'; }
                    if ($mValue['WeekDay'] == 5) { $hDay = 'יום שישי'; }
                    if ($mValue['WeekDay'] == 6) { $hDay = 'יום שבת'; }    

                    $offset = $mValue['WeekDay'] - $day;
                    $meetingDate = date('d/m/Y', strtotime('+'.$offset.' days'));
                    if ($offset < 0) { $meetingDate = date('d/m/Y', strtotime($offset.' days')); }

                    $start = explode(' ', $mValue['StartDate'])[1];
                    $end = explode(' ', $mValue['EndDate'])[1];

                    if ($currentDay != $mValue['WeekDay']) 
                    {
                        $data .= '<div class="participated-circle-item-txt" style="color: #000; margin-top: 12px;">'.$hDay.' - '.$meetingDate.'</div>';
                        $currentDay = $mValue['WeekDay'];
                    }
                    $data .= '<div class="participated-circle-item-txt">מ: '.$start.' עד: '.$end.'</div>';  
                }

                $data .= '</div>';
            }            
            $data .= '</div>';

            echo $data;
            exit;
        }

        // RETURN THE EVENTS LIST FOR INSTRUCTOR
        if ($_POST['listType'] == 'eventsListForInstructor' && isset($_SESSION['InstructorId']))
        {
            // GET THE EVENTS
            $pdo = UTILITIES::PDO_DB_Connection('school_events');
            $stmt = $pdo->prepare("SELECT * FROM events_table WHERE BINARY StartDate>=? AND EventInstructorId=?");
            $stmt->execute([$weekStart, $_SESSION['InstructorId']]); 
            $eventsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            if (!$eventsResults || count($eventsResults) == 0)
            {
                echo 'SUCCESS
                <div class="alt-text">לא קיימים אירועים בהדרכתך לשבוע זה</div>';
                exit;
            }
            
            $data = 'SUCCESS
            <div class="student-list-items-w">';
            foreach ($eventsResults as $eKey => $eValue)
            {
                $startDate = explode(' ', $eValue['StartDate'])[0];
                $endDate = explode(' ', $eValue['EndDate'])[0]; 

                $dayStart = intval(date('w', strtotime($startDate)));
                $dayEnd = intval(date('w', strtotime($endDate)));

                $hDayStart = 'יום ראשון';
                if ($dayStart == 1) { $hDayStart = 'יום שני'; }
                if ($dayStart == 2) { $hDayStart = 'יום שלישי'; }
                if ($dayStart == 3) { $hDayStart = 'יום רביעי'; }
                if ($dayStart == 4) { $hDayStart = 'יום חמישי'; }
                if ($dayStart == 5) { $hDayStart = 'יום שישי'; }
                if ($dayStart == 6) { $hDayStart = 'יום שבת'; }  
                
                $hDayEnd = 'יום ראשון';
                if ($dayEnd == 1) { $hDayEnd = 'יום שני'; }
                if ($dayEnd == 2) { $hDayEnd = 'יום שלישי'; }
                if ($dayEnd == 3) { $hDayEnd = 'יום רביעי'; }
                if ($dayEnd == 4) { $hDayEnd = 'יום חמישי'; }
                if ($dayEnd == 5) { $hDayEnd = 'יום שישי'; }
                if ($dayEnd == 6) { $hDayEnd = 'יום שבת'; }  

                $offset = $dayStart - $day;
                $eventStartDate = date('d/m/Y', strtotime('+'.$offset.' days'));
                if ($offset < 0) { $eventStartDate = date('d/m/Y', strtotime($offset.' days')); }
                $eventStartDate .= ' '.explode(' ', $eValue['StartDate'])[1];

                $offset = $dayEnd - $day;
                $eventEndDate = date('d/m/Y', strtotime('+'.$offset.' days'));
                if ($offset < 0) { $eventEndDate = date('d/m/Y', strtotime($offset.' days')); }
                $eventEndDate .= ' '.explode(' ', $eValue['EndDate'])[1];

                $top = $eKey == 0 ? ' style="margin-top: 0;"' : '';
                $data .=
                '<div class="participated-circle-item"'.$top.'>
                    <div class="participated-circle-name">'.$eValue['EventName'].'</div>
                    <div class="participated-circle-item-txt" style="text-align: right;">שם המדריך: '.$_SESSION['FirstName'].' '.$_SESSION['LastName'].'</div>
                    <div class="participated-circle-item-txt" style="text-align: right;">תיאור האירוע: '.$eValue['EventDescription'].'</div>
                    <div class="participated-circle-item-txt" style="text-align: center; color: #000;">תאריך התחלה:</div>
                    <div class="participated-circle-item-txt" style="text-align: center;">'.$hDayStart.' - '.$eventStartDate.'</div>
                    <div class="participated-circle-item-txt" style="text-align: center; color: #000;">תאריך סיום:</div>
                    <div class="participated-circle-item-txt" style="text-align: center;">'.$hDayEnd.' - '.$eventEndDate.'</div>
                </div>';
            }            
            $data .= '</div>';

            echo $data;
            exit;
        }
    }

    // REGISTER A STUDENT TO SPECIFIC MEETINGS OF A CIRCLE
    if (isset($_POST['meetings']) && isset($_POST['circleId']) && isset($_POST['operation']))
    {
        $wantedMeetings = json_decode(stripslashes($_POST['meetings'])); /* 0,1,2 -> ordinal index of meetings wanted */
        $previouslyRegisteredMeetings = json_decode(stripslashes($_POST['prevRegisteredMeetings'])); /* 0,1,2 -> ordinal index of meetings previously registered */
        
        $day = date('w');
        $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");

        // REGISTER A STUDENT TO CIRCLE BY THE INSTRUCTOR
        if (isset($_POST['studentId']) && isset($_SESSION['InstructorId']))
        {
            $studentId = $_POST['studentId'];

            // GET ALL THE MEETINGS OF THIS CIRCLE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
            $stmt->execute([$_POST['circleId']]); 
            $desiredCircleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            foreach ($desiredCircleMeetingsResult as $dcmKey => $dcmValue)
            {
                if (in_array($dcmKey, $wantedMeetings) && !in_array($dcmKey, $previouslyRegisteredMeetings))
                {                    
                    // CHECK IF THIS MEETING COLLIDES WITH OTHER CIRCLES
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY StartDate>? AND CircleId!=? ORDER BY StartDate ASC");
                    $stmt->execute([$currentDateTime, $_POST['circleId']]); 
                    $otherMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                    $pdo = null;

                    foreach ($otherMeetingsResult as $omKey => $omValue)
                    {
                        $iThStart = $omValue['StartDate'];
                        $iThEnd = $omValue['EndDate'];  
                        
                        $wStart = $dcmValue['StartDate'];
                        $wEnd = $dcmValue['EndDate']; 

                        // CHECK IF THIS STUDENT IS PARTICIPATING IN THE OTHER MEETING
                        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                        $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY MeetingId=? AND StudentId=?");
                        $stmt->execute([$omValue['MeetingId'], $studentId]); 
                        $omStudentResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                        $pdo = null;
                        
                        if (strtotime($iThStart) <= strtotime($wEnd) && strtotime($iThStart) >= strtotime($wStart))
                        {
                            if (count($omStudentResult) > 0)
                            {
                                echo 'שגיאה: מפגש שבחרת בשעות '.$wStart.' - '.$wEnd.' מתנגש עם מפגש מחוג אחר שהסטודנט רשום אליו, נסה שוב';
                                exit;
                            }                            
                        }
            
                        if (strtotime($iThEnd) <= strtotime($wEnd) && strtotime($iThEnd) >= strtotime($wStart))
                        {
                            if (count($omStudentResult) > 0)
                            {
                                echo 'שגיאה: מפגש שבחרת בשעות '.$wStart.' - '.$wEnd.' מתנגש עם מפגש מחוג אחר שהסטודנט רשום אליו, נסה שוב';
                                exit;
                            } 
                        }
            
                        if (strtotime($wStart) >= strtotime($iThStart) && strtotime($iThEnd) >= strtotime($wStart))
                        {
                            if (count($omStudentResult) > 0)
                            {
                                echo 'שגיאה: מפגש שבחרת בשעות '.$wStart.' - '.$wEnd.' מתנגש עם מפגש מחוג אחר שהסטודנט רשום אליו, נסה שוב';
                                exit;
                            } 
                        }
                    }                    
                }
            }

            foreach ($desiredCircleMeetingsResult as $dcmKey => $dcmValue)
            {                
                // CHECK IF MEETING START DATE IS PASSED
                if (strtotime($currentDateTime) <= strtotime($dcmValue['StartDate'])) {
                    continue;
                }
                else if (in_array($dcmKey, $wantedMeetings) && !in_array($dcmKey, $previouslyRegisteredMeetings))
                {
                    echo 'זמן תחילת מפגש מספר '.$dcmKey.' עבר';
                    exit;
                }
            }

            foreach ($desiredCircleMeetingsResult as $dcmKey => $dcmValue)
            {
                // REMOVE FUTURE REGISTERED MEETINGS OF THIS USER TO THIS CIRCLE FROM THE DATABAE
                if (strtotime($currentDateTime) <= strtotime($dcmValue['StartDate']))
                {
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("DELETE FROM circle_participants WHERE BINARY MeetingId=? AND StudentId=?");
                    $stmt->execute([$dcmValue['MeetingId'], $studentId]); 
                    $pdo = null;
                }

                // INSERT NEW REGISTERED MEETINGS TO THE DATABASE
                if (in_array($dcmKey, $wantedMeetings))
                {                    
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("INSERT INTO circle_participants (StudentId,MeetingId) VALUES (?,?)");
                    $stmt->execute([$studentId, $dcmValue['MeetingId']]); 
                    $pdo = null;
                }                
            }

            echo 'ההרשמה בוצעה בהצלחה';
            exit;
        }
        // REGISTER A STUDENT TO CIRCLE BY THE STUDENT
        else if (isset($_SESSION['StudentId']))
        {
            $studentId = $_SESSION['StudentId'];

            // GET ALL THE MEETINGS OF THIS CIRCLE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
            $stmt->execute([$_POST['circleId']]); 
            $desiredCircleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
            $pdo = null;

            foreach ($desiredCircleMeetingsResult as $dcmKey => $dcmValue)
            {
                if (in_array($dcmKey, $wantedMeetings) && !in_array($dcmKey, $previouslyRegisteredMeetings))
                {                    
                    // CHECK IF THIS MEETING COLLIDES WITH OTHER CIRCLES
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY StartDate>? AND CircleId!=? ORDER BY StartDate ASC");
                    $stmt->execute([$currentDateTime, $_POST['circleId']]); 
                    $otherMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                    $pdo = null;

                    foreach ($otherMeetingsResult as $omKey => $omValue)
                    {
                        $iThStart = $omValue['StartDate'];
                        $iThEnd = $omValue['EndDate'];  
                        
                        $wStart = $dcmValue['StartDate'];
                        $wEnd = $dcmValue['EndDate']; 

                        // CHECK IF THIS STUDENT IS PARTICIPATING IN THE OTHER MEETING
                        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                        $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY MeetingId=? AND StudentId=?");
                        $stmt->execute([$omValue['MeetingId'], $studentId]); 
                        $omStudentResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                        $pdo = null;
                        
                        if (strtotime($iThStart) <= strtotime($wEnd) && strtotime($iThStart) >= strtotime($wStart))
                        {
                            if (count($omStudentResult) > 0)
                            {
                                echo 'שגיאה: מפגש שבחרת בשעות '.$wStart.' - '.$wEnd.' מתנגש עם מפגש שלך מחוג אחר שאתה רשום אליו, נסה שוב';
                                exit;
                            }                            
                        }
            
                        if (strtotime($iThEnd) <= strtotime($wEnd) && strtotime($iThEnd) >= strtotime($wStart))
                        {
                            if (count($omStudentResult) > 0)
                            {
                                echo 'שגיאה: מפגש שבחרת בשעות '.$wStart.' - '.$wEnd.' מתנגש עם מפגש שלך מחוג אחר שאתה רשום אליו, נסה שוב';
                                exit;
                            } 
                        }
            
                        if (strtotime($wStart) >= strtotime($iThStart) && strtotime($iThEnd) >= strtotime($wStart))
                        {
                            if (count($omStudentResult) > 0)
                            {
                                echo 'שגיאה: מפגש שבחרת בשעות '.$wStart.' - '.$wEnd.' מתנגש עם מפגש שלך מחוג אחר שאתה רשום אליו, נסה שוב';
                                exit;
                            } 
                        }
                    }                    
                }
            }

            foreach ($desiredCircleMeetingsResult as $dcmKey => $dcmValue)
            {                
                // CHECK IF MEETING START DATE IS PASSED
                if (strtotime($currentDateTime) <= strtotime($dcmValue['StartDate'])) {
                    continue;
                }
                else if (in_array($dcmKey, $wantedMeetings) && !in_array($dcmKey, $previouslyRegisteredMeetings))
                {
                    echo 'זמן תחילת מפגש מספר '.$dcmKey.' עבר';
                    exit;
                }
            }

            foreach ($desiredCircleMeetingsResult as $dcmKey => $dcmValue)
            {
                // REMOVE FUTURE REGISTERED MEETINGS OF THIS USER TO THIS CIRCLE FROM THE DATABAE
                if (strtotime($currentDateTime) <= strtotime($dcmValue['StartDate']))
                {
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("DELETE FROM circle_participants WHERE BINARY MeetingId=? AND StudentId=?");
                    $stmt->execute([$dcmValue['MeetingId'], $studentId]); 
                    $pdo = null;
                }

                // INSERT NEW REGISTERED MEETINGS TO THE DATABASE
                if (in_array($dcmKey, $wantedMeetings))
                {                    
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("INSERT INTO circle_participants (StudentId,MeetingId) VALUES (?,?)");
                    $stmt->execute([$studentId, $dcmValue['MeetingId']]); 
                    $pdo = null;
                }                
            }

            echo 'ההרשמה בוצעה בהצלחה';
            exit;                 
        }        
    }

    // UPDATE THE TOTAL BUDGET
    if (isset($_POST['updateBudget']))
    {
        if (!is_numeric($_POST['updateBudget']))
        {
            echo 'נא להזין תקציב תקין';
            exit;
        }

        if (floatval($_POST['updateBudget']) < 0)
        {
            echo 'לא ניתן לעדכן לתקציב שלילי';
            exit;
        }

        $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
        $stmt = $pdo->prepare("UPDATE total_budget SET TotalSchoolBudget=?");
        $stmt->execute([$_POST['updateBudget']]); 
        $pdo = null; 
        
        echo 'עדכון התקציב בוצע בהצלחה';
        exit;
    }

    // PUBLISH A MESSAGE FROM THE INSTRUCTOR
    if (isset($_POST['sendMessageTo']) && isset($_POST['messageContent']))
    {
        $currentDateTime = date("Y-m-d H:i:s");
        if (isset($_SESSION['ManagerId']) && $_POST['sendMessageTo'] == 'all')
        {            
            $pdo = UTILITIES::PDO_DB_Connection('school_messages');
            $stmt = $pdo->prepare("INSERT INTO messages_table (Active,MessageText,CreationDate) VALUES (?,?,?)");
            $stmt->execute(['YES', $_POST['messageContent'], $currentDateTime]); 
            $pdo = null; 
    
            echo 'added successfully';
            exit;
        }  
        else if (isset($_SESSION['ManagerId']))
        {
            $pdo = UTILITIES::PDO_DB_Connection('school_messages');
            $stmt = $pdo->prepare("INSERT INTO messages_table (Active,MessageText,CreationDate,ToSpecificInstructor) VALUES (?,?,?,?)");
            $stmt->execute(['YES', $_POST['messageContent'], $currentDateTime, $_POST['sendMessageTo']]); 
            $pdo = null; 
    
            echo 'added successfully';
            exit;
        }  
        else if (isset($_SESSION['InstructorId']))  
        {
            $messageToStudentIds = json_decode(stripslashes($_POST['sendMessageTo'])); 
            $pdo = UTILITIES::PDO_DB_Connection('school_messages');
            foreach ($messageToStudentIds as $key => $value)
            {
                $stmt = $pdo->prepare("INSERT INTO messages_table (Active,MessageText,CreationDate,ToSpecificStudent,FromInstructor) VALUES (?,?,?,?,?)");
                $stmt->execute(['YES', $_POST['messageContent'], $currentDateTime, $value, $_SESSION['InstructorId']]); 
            }
            $pdo = null;

            echo 'added successfully';
            exit;
        }  
    }

    // REMOVE A STUDENT FROM THE SYSTEM
    if (isset($_POST['removeStudentId']))
    {
        $day = date('w');
        $weekStart = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('Y-m-d', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");

        // SELECT ALL THE FUTURE CIRCLE MEETINGS
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT MeetingId FROM circle_meetings WHERE StartDate>=?");
        $stmt->execute([$currentDateTime]); 
        $futureMeetingsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 
        
        // REMOVE RECORD OF THIS USER WITH THOSE MEETINGS FROM THE PATICIPANTS TABLE 
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        foreach ($futureMeetingsResults as $fmKey => $fmValue)
        {                       
            $stmt = $pdo->prepare("DELETE FROM circle_participants WHERE MeetingId=? AND StudentId=?");
            $stmt->execute([$fmValue['MeetingId'], $_POST['removeStudentId']]); 
        }
        $pdo = null; 

        // DELETE ALL THE MESSAGES THAT ARE TO THIS STUDENT
        $pdo = UTILITIES::PDO_DB_Connection('school_messages');
        $stmt = $pdo->prepare("DELETE FROM messages_table WHERE ToSpecificStudent=?");
        $stmt->execute([$_POST['removeStudentId']]);
        $pdo = null; 

        // REMOVE THE STUDENT
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("DELETE FROM students WHERE StudentId=?");
        $stmt->execute([$_POST['removeStudentId']]); 
        $pdo = null; 

        echo "הסרת התלמיד התבצעה בהצלחה";
        exit;
    }

    // REMOVE AN INSTRUCTOR FROM THE SYSTEM
    if (isset($_POST['removeInstructorId']))
    {
        $day = date('w');
        $weekStart = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('Y-m-d', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");
        
        // REMOVE ALL FURTURE MEETINGS OF CIRCLES WITH THIS INSTRUCTOR
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate>?");
        $stmt->execute([$_POST['removeInstructorId'], $currentDateTime]);
        $circles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($circles as $cKey => $cValue)
        {
            // SELECT FUTURE MEETINGS TO REMOVE THEIR PARTICIPANTS
            $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? AND EndDate>?");
            $stmt->execute([$cValue['CircleId'], $currentDateTime]);
            $futureMeetingsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($futureMeetingsResults as $fmKey => $fmValue)
            {
                $stmt = $pdo->prepare("DELETE FROM circle_participants WHERE BINARY MeetingId=?");
                $stmt->execute([$fmValue['MeetingId']]);
            }

            // DELETE FUTURE MEETINGS
            $stmt = $pdo->prepare("DELETE FROM circle_meetings WHERE BINARY CircleId=? AND EndDate>?");
            $stmt->execute([$cValue['CircleId'], $currentDateTime]);
            
            // SELECT FINISHED MEETINGS TO UPDATE CIRCLE END TIME
            $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY EndDate ASC");
            $stmt->execute([$cValue['CircleId']]);
            $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($meetings) > 0)
            {
                // UPDATE THE CIRCLE SET END TIME AS THE END OF THE LAST MEETING
                $endDate = $meetings[count($meetings) - 1]['EndDate'];
                $stmt = $pdo->prepare("UPDATE circles_table SET EndDate=? WHERE BINARY CircleId=?");
                $stmt->execute([$endDate, $cValue['CircleId']]);
            }
            else
            {
                // REMOVE THE CIRCLE
                $stmt = $pdo->prepare("DELETE FROM circles_table WHERE BINARY CircleId=?");
                $stmt->execute([$cValue['CircleId']]);
            }
        }
        $pdo = null;

        // DELETE ALL THE EVENTS THAT ARE THIS WEEK WITH THIS INSTRUCTOR     
        $pdo = UTILITIES::PDO_DB_Connection('school_events');
        $stmt = $pdo->prepare("UPDATE events_table SET EventInstructorId=?,NeedInstructor=? WHERE EventInstructorId=?");
        $stmt->execute([-1, 'NO', $_POST['removeInstructorId']]);
        $pdo = null; 

        // DELETE ALL THE MESSAGES THAT ARE FROM THIS INSTRUCTOR  
        $pdo = UTILITIES::PDO_DB_Connection('school_messages');
        $stmt = $pdo->prepare("DELETE FROM messages_table WHERE FromInstructor=? OR ToSpecificInstructor=?");
        $stmt->execute([$_POST['removeInstructorId'], $_POST['removeInstructorId']]);
        $pdo = null; 

        // REMOVE THE INSTRUCTOR
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("DELETE FROM instructors WHERE InstructorId=?");
        $stmt->execute([$_POST['removeInstructorId']]); 
        $pdo = null; 

        echo "הסרת המדריך התבצעה בהצלחה";
        exit;
    }

    // REMOVE OR ACTIVATE A MESSAGE OF THE MANAGER
    if (isset($_POST['actionMessageId']) && isset($_POST['action']))
    {
        if ($_POST['action'] == 'remove')
        {
            $pdo = UTILITIES::PDO_DB_Connection('school_messages');
            $stmt = $pdo->prepare("UPDATE messages_table SET Active=? WHERE MessageId=?");
            $stmt->execute(['NO', $_POST['actionMessageId']]);
            $pdo = null; 
        }
        else if ($_POST['action'] == 'activate')
        {
            $pdo = UTILITIES::PDO_DB_Connection('school_messages');
            $stmt = $pdo->prepare("UPDATE messages_table SET Active=? WHERE MessageId=?");
            $stmt->execute(['YES', $_POST['actionMessageId']]);
            $pdo = null; 
        }
        echo "הפעולה התבצעה בהצלחה";
        exit;
    }

    // ADD A GENERAL EXPENSE
    if (isset($_POST['generalExpenseName']) && isset($_POST['generalExpenseDescription']) && isset($_POST['generalExpenseAmount']))
    {
        $day = date('w');
        $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");
        $currentDateUnix = date("Y-m-d");
        $currentDate = date('d/m/Y');
        $monthStart = date('Y-m-01').' 00:00:00'; 
        $monthEnd = date("Y-m-t").' 23:59:59';

        // VALIDATE EXPENSE AMOUNT
        if (!is_numeric($_POST['generalExpenseAmount']) || floatval($_POST['generalExpenseAmount']) < 0) {
            echo 'נא להזין סכום הוצאה תקין';
            exit;
        }

        // INSERT THE NEW EXPENSE TO THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
        $stmt = $pdo->prepare("INSERT INTO general_monthly_budget (ExpenseName,ExpenseDescription,ExpenseAmount,ExpenseDate) VALUES (?,?,?,?)");
        $stmt->execute([$_POST['generalExpenseName'], $_POST['generalExpenseDescription'], $_POST['generalExpenseAmount'], $currentDateUnix]);
        
        // UPDATE THE GENERAL BUDGET
        $stmt = $pdo->prepare("SELECT TotalSchoolBudget FROM total_budget");
        $stmt->execute(); 
        $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalBudget = floatval($totalBudgetResult[0]['TotalSchoolBudget']);
        $totalBudget -= floatval($_POST['generalExpenseAmount']);

        $stmt = $pdo->prepare("UPDATE total_budget SET TotalSchoolBudget=?");
        $stmt->execute([$totalBudget]);
        $pdo = null;

        echo "added successfully";
        exit;
    }

    // ADD AN EXPENSE TO A SPECIFIC CIRCLE
    if (isset($_POST['addExpenseToCircle']) && isset($_POST['expenseName']) && isset($_POST['expenseDescription']) && isset($_POST['expenseAmount']))
    {
        $day = date('w');
        $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");
        $currentDate = date('Y-m-d');
        $monthStart = date('Y-m-01').' 00:00:00'; 
        $monthEnd = date("Y-m-t").' 23:59:59';

        // VALIDATE EXPENSE AMOUNT
        if (!is_numeric($_POST['expenseAmount']) || floatval($_POST['expenseAmount']) < 0) {
            echo 'נא להזין סכום הוצאה תקין';
            exit;
        }

        // CHECK IF THIS CIRCLE EXISTS
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT PermanentCircleName FROM permanent_circles WHERE BINARY PermanentCircleName=?");
        $stmt->execute([$_POST['addExpenseToCircle']]); 
        $circleResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        if (!$circleResult || count($circleResult) == 0)
        {
            echo 'החוג לא קיים במערכת';
            exit;
        }

        // INSERT THE NEW EXPENSE TO THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
        $stmt = $pdo->prepare("INSERT INTO circles_budget (ExpenseName,ExpenseDescription,ExpenseAmount,CircleName,ExpenseDate) VALUES (?,?,?,?,?)");
        $stmt->execute([$_POST['expenseName'], $_POST['expenseDescription'], $_POST['expenseAmount'], $_POST['addExpenseToCircle'], $currentDate]);
        
        // UPDATE THE TOTAL BUDGET OF THE SYSTEM
        $stmt = $pdo->prepare("SELECT TotalSchoolBudget FROM total_budget");
        $stmt->execute(); 
        $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalBudget = $totalBudgetResult[0]['TotalSchoolBudget'];
        $updatedBudget = floatval($totalBudget) - floatval($_POST['expenseAmount']);

        $stmt = $pdo->prepare("UPDATE total_budget SET TotalSchoolBudget=?");
        $stmt->execute([$updatedBudget]); 
        $pdo = null;

        echo 'SUCCESS';
        exit;
    }

    // ADD AN EXPENSE TO A SPECIFIC EVENT    
    if (isset($_POST['addExpenseToEvent']) && isset($_POST['expenseName']) && isset($_POST['expenseDescription']) && isset($_POST['expenseAmount']))
    {
        $day = date('w');
        $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s");
        $currentDate = date('Y-m-d');
        $monthStart = date('Y-m-01').' 00:00:00'; 
        $monthEnd = date("Y-m-t").' 23:59:59';

        // VALIDATE EXPENSE AMOUNT
        if (!is_numeric($_POST['expenseAmount']) || floatval($_POST['expenseAmount']) < 0) {
            echo 'נא להזין סכום הוצאה תקין';
            exit;
        }

        // CHECK IF THIS EVENT EXISTS
        $pdo = UTILITIES::PDO_DB_Connection('school_events');
        $stmt = $pdo->prepare("SELECT EventName FROM events_table WHERE BINARY EventId=?");
        $stmt->execute([$_POST['addExpenseToEvent']]); 
        $eventResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        if (!$eventResult || count($eventResult) == 0)
        {
            echo 'האירוע לא קיים במערכת או שהסתיים בחודש שעבר';
            exit;
        }

        // INSERT THE NEW EXPENSE TO THE DATABASE
        $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
        $stmt = $pdo->prepare("INSERT INTO events_budget (ExpenseName,ExpenseDescription,ExpenseAmount,EventId,ExpenseDate) VALUES (?,?,?,?,?)");
        $stmt->execute([$_POST['expenseName'], $_POST['expenseDescription'], $_POST['expenseAmount'], $_POST['addExpenseToEvent'], $currentDate]);
        
        // UPDATE THE TOTAL BUDGET OF THE SYSTEM
        $stmt = $pdo->prepare("SELECT TotalSchoolBudget FROM total_budget");
        $stmt->execute(); 
        $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalBudget = $totalBudgetResult[0]['TotalSchoolBudget'];
        $updatedBudget = floatval($totalBudget) - floatval($_POST['expenseAmount']);

        $stmt = $pdo->prepare("UPDATE total_budget SET TotalSchoolBudget=?");
        $stmt->execute([$updatedBudget]); 
        $pdo = null;

        echo 'SUCCESS';
        exit;
    }

    // PUBLISH A COMMENT FOR A CIRCLE FROM THE STUDENT
    if (isset($_POST['circleStudentAddComment']) && isset($_POST['circleId']) && isset($_POST['rate']) && isset($_SESSION['StudentId']))
    {
        $dayStart = date('Y-m-d').' 00:00:00'; 
        $dayEnd = date('Y-m-d').' 23:59:59';
        $currentDateTime = date("Y-m-d H:i:s");

        // CHECK IF THIS CIRCLE IS STILL ELIGIBLE FOR COMMENTS
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY CircleId=?");                         
        $stmt->execute([$_POST['circleId']]); 
        $circleResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        if (!$circleResult || count($circleResult) == 0)
        {
            echo 'החוג כבר לא קיים במערכת';
            exit;
        }

        if (!(strtotime($circleResult[0]['EndDate']) > strtotime($dayStart) && strtotime($currentDateTime) > strtotime($circleResult[0]['EndDate'])))
        {
            echo 'זמן שליחת משובים לחוג זה עבר כבר';
            exit;
        }

        // CHECK IF THIS STUDENT SENT ALREADY A COMMENT FOR THIS CIRCLE
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT * FROM circle_comments_table WHERE BINARY FromStudentId=? AND CircleId=?");
        $stmt->execute([$_SESSION['StudentId'], $_POST['circleId']]); 
        $commentResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        
        if (count($commentResult) > 0) 
        {
            echo 'לא ניתן לשלוח משוב על אותו חוג יותר מפעם אחת';
            exit;
        } 

        if (strtotime($circleResult[0]['EndDate']) >= strtotime($dayStart) && strtotime($circleResult[0]['EndDate']) <= strtotime($currentDateTime)
            && strtotime($currentDateTime) <= strtotime($dayEnd))
        {
            // INSERT THE COMMENT TO THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("INSERT INTO circle_comments_table (Rate,Comment,FromStudentId,CircleId) VALUES (?,?,?,?)");                         
            $stmt->execute([$_POST['rate'], $_POST['circleStudentAddComment'], $_SESSION['StudentId'], $_POST['circleId']]);
            $pdo = null; 

            echo "המשוב נשלח בהצלחה";
            exit;
        }
        else
        {
            echo 'עבר הזמן להשארת משוב עבור חוג זה';
            exit;
        }
    }

    // PUBLISH A COMMENT FOR A CIRCLE FROM THE INSTRUCTOR
    if (isset($_POST['circleInstructorAddComment']) && isset($_POST['circleId']) && isset($_POST['rate']) && isset($_POST['onStudentId']) && isset($_SESSION['InstructorId']))
    {
        $dayStart = date('Y-m-d').' 00:00:00'; 
        $dayEnd = date('Y-m-d').' 23:59:59';
        $currentDateTime = date("Y-m-d H:i:s");

        // CHECK IF THIS CIRCLE IS STILL ELIGIBLE FOR COMMENTS
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY CircleId=?");                         
        $stmt->execute([$_POST['circleId']]); 
        $circleResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        if (!$circleResult || count($circleResult) == 0)
        {
            echo 'החוג כבר לא קיים במערכת';
            exit;
        }

        if (strtotime($circleResult[0]['EndDate']) >= strtotime($dayStart) && strtotime($circleResult[0]['EndDate']) <= strtotime($currentDateTime)
            && strtotime($currentDateTime) <= strtotime($dayEnd))
        {
            // INSERT THE COMMENT TO THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("INSERT INTO circle_comments_table (Rate,Comment,FromInstructorId,CircleId,OnStudentId) VALUES (?,?,?,?,?)");                         
            $stmt->execute([$_POST['rate'], $_POST['circleInstructorAddComment'], $_SESSION['InstructorId'], $_POST['circleId'], $_POST['onStudentId']]);
            $pdo = null; 

            echo "המשוב נשלח בהצלחה";
            exit;
        }
        else
        {
            echo 'עבר הזמן להשארת משוב עבור חוג זה';
            exit;
        }
    }

    // EDIT EVENT
    if (isset($_POST['editEventId']) && isset($_POST['type']))
    {
        // SELECT THE EVENT
        $dayStart = date('Y-m-d').' 00:00:00'; 
        $dayEnd = date('Y-m-d').' 23:59:59';
        $currentDateTime = date("Y-m-d H:i:s");

        // SELECT THIS EVENT 
        $pdo = UTILITIES::PDO_DB_Connection('school_events');
        $stmt = $pdo->prepare("SELECT * FROM events_table WHERE BINARY EventId=?");                         
        $stmt->execute([$_POST['editEventId']]); 
        $eventResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        if (!$eventResult || count($eventResult) == 0)
        {
            echo 'SUCCESS
            <div class="alt-text">האירוע המבוקש לא קיים במערכת</div>';
            exit;
        }

        // CHECK IF THIS EVENT IS ALREADY STARTED
        if (strtotime($currentDateTime) >= strtotime($eventResult[0]['StartDate']))
        {
            echo 'SUCCESS
            <div class="alt-text">לא ניתן לבצע שינויים עבור אירוע זה מפני שהוא התחיל כבר</div>';
            exit;
        }

        // SELECT INSTRUCTOR DETIALS
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM instructors WHERE BINARY InstructorId=?");                         
        $stmt->execute([$eventResult[0]['EventInstructorId']]); 
        $instructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        $instructor = '';
        if (!$instructorResult || count($instructorResult) == 0) {
            $instructor = 'ללא מדריך';
        }
        else {
            $instructor = $instructorResult[0]['FirstName'].' '.$instructorResult[0]['LastName'].' - '.$instructorResult[0]['IdNumber']; 
        }

        // SELECT ALL INSTRUCTORS
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM instructors WHERE BINARY InstructorId!=?");                         
        $stmt->execute([$eventResult[0]['EventInstructorId']]); 
        $instructorsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        // RETURN THE DETAILS OF THE EVENT TO EDIT
        if ($_POST['type'] == 'eventInfo')
        {          
            $startDate = explode(' ', $eventResult[0]['StartDate'])[0];
            $startHour = explode(' ', $eventResult[0]['StartDate'])[1];
            $endDate = explode(' ', $eventResult[0]['EndDate'])[0];
            $endHour = explode(' ', $eventResult[0]['EndDate'])[1];

            $data =
            'SUCCESS<div class="ssc-field" style="margin-top: 0;">
                <div class="ssc-field-name">שם&nbsp;האירוע</div>
                <input type="text" id="eventName" class="ssc-field-value" value="'.$eventResult[0]['EventName'].'"/>
            </div>
            <div class="ssc-field-name" style="display: block; margin-top: 20px; width: 100%; height: 20px; line-height: 20px; direction: rtl; text-align: right;">תיאור&nbsp;האירוע</div>
            <textarea id="eventDescription" class="field-textarea">'.$eventResult[0]['EventDescription'].'</textarea>                
            <div class="ssc-field">
                <div class="ssc-field-name">מדריך</div>
                <select id="selectInstructor" class="ssc-field-value">
                    <option>'.$instructor.'</option>';
                    foreach ($instructorsResult as $instKey => $instValue) {
                        $data .= '<option>'.$instValue['FirstName'].' '.$instValue['LastName'].' - '.$instValue['IdNumber'].'</option>'; 
                    }
                    if ($instructor != 'ללא מדריך') {
                        $data .= '<option>ללא מדריך</option>';
                    }
                    $data .=
                '</select>
            </div>
            <div class="ssc-field">
                <div class="ssc-field-name">תאריך&nbsp;התחלה</div>
                <input type="date" id="eventStartDate" class="ssc-field-value" value="'.$startDate.'"/>
            </div>
            <div class="ssc-field">
                <div class="ssc-field-name">שעת&nbsp;התחלה</div>
                <input type="text" id="eventStartHour" class="ssc-field-value" value="'.$startHour.'" placeholder="שעה (בפורמט: 00:00:00)"/>
            </div>
            <div class="ssc-field">
                <div class="ssc-field-name">תאריך&nbsp;סיום</div>
                <input type="date" id="eventEndDate" class="ssc-field-value" value="'.$endDate.'"/>
            </div>
            <div class="ssc-field">
                <div class="ssc-field-name">שעת&nbsp;סיום</div>
                <input type="text" id="eventEndHour" class="ssc-field-value" value="'.$endHour.'" placeholder="שעה (בפורמט: 00:00:00)"/>
            </div>';

            echo $data;
            exit;
        }
        // SAVE THE CHANGES TO THE SPECIFIC EVENT
        else if ($_POST['type'] == 'saveEdits')
        {
            // CHECK FOR EMPTY FIELDS
            if (empty($_POST['eventName']) || empty($_POST['eventDescription']) || empty($_POST['eventStartDate']) || empty($_POST['eventStartHour'])
                || empty($_POST['eventEndDate']) || empty($_POST['eventEndHour']))
            {
                echo 'השלם/י את השדות החסרים';
                exit;
            }

            $day = date('w');
            $weekStart = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
            $weekEnd = date('Y-m-d', strtotime('+'.(7-$day).' days')).' 00:00:00';
            $currentDateTime = date("Y-m-d H:i:s");
    
            $startDate = str_replace('-', '/', $_POST['eventStartDate']);
            $startDate = date('Y-m-d', strtotime($startDate)).' '.$_POST['eventStartHour'];
            
            $endDate = str_replace('-', '/', $_POST['eventEndDate']);
            $endDate = date('Y-m-d', strtotime($endDate)).' '.$_POST['eventEndHour'];
    
            // VALIDATE START DATE
            if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($startDate)) 
            {
                echo 'שגיאה: זמן שגוי עבור תחילת האירוע';
                exit;
            }
    
            // VALIDATE START DATE
            if (strtotime($startDate) >= strtotime($endDate)) 
            {
                echo 'שגיאה: זמן שגוי עבור סיום האירוע';
                exit;
            }
            
            $needInstructor = 'NO';
            $instructorId = -1;
    
            // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS EVENT
            if ($_POST['eventInstructor'] != 'ללא מדריך')
            {
                // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS EVENT, (AGAINST OTHER CIRCLES)
                $instructorIdNumber = explode(' - ', $_POST['eventInstructor'])[1];
                $needInstructor = 'YES';
    
                // SELECT THE ID OF THE INSTRUCTOR
                $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                $stmt = $pdo->prepare("SELECT InstructorId FROM instructors WHERE BINARY IdNumber=?");
                $stmt->execute([$instructorIdNumber]); 
                $instructorIdResults = $stmt->fetchAll(PDO::FETCH_ASSOC);      
                
                if (!$instructorIdResults || count($instructorIdResults) == 0) {
                    echo 'המדריך המבוקש לאירוע זה לא קיים במערכת';
                    exit;
                }

                foreach ($instructorIdResults as $row) {
                    $instructorId = $row['InstructorId'];
                }
                $pdo = null; 
    
                // CHECK IF THIS INSTRUCTOR IS BUSY WITH CIRCLES IN THE TIME OF THE EVENT
                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate>=?");
                $stmt->execute([$instructorId, $currentDateTime]); 
                $currentInstructorCircles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $pdo = null; 
    
                foreach ($currentInstructorCircles as $row) 
                {
                    if (strtotime($startDate) <= strtotime($row['EndDate']) && strtotime($startDate) >= strtotime($row['StartDate']))
                    {
                        echo 'המדריך שנבחר עבור אירוע זה מעביר חוג אחר בזמנים שנבחרו עבור אירוע זה';
                        exit;
                    }
    
                    if (strtotime($endDate) <= strtotime($row['EndDate']) && strtotime($endDate) >= strtotime($row['StartDate']))
                    {
                        echo 'המדריך שנבחר עבור אירוע זה מעביר חוג אחר בזמנים שנבחרו עבור אירוע זה';
                        exit;
                    }
    
                    if (strtotime($row['StartDate']) >= strtotime($startDate) && strtotime($endDate) >= strtotime($row['StartDate']))
                    {
                        echo 'המדריך שנבחר עבור אירוע זה מעביר חוג אחר בזמנים שנבחרו עבור אירוע זה';
                        exit;
                    }
                }    
                
                // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS EVENT, (AGAINST OTHER EVENTS)
                $pdo = UTILITIES::PDO_DB_Connection('school_events');
                $stmt = $pdo->prepare("SELECT EventId,StartDate,EndDate FROM events_table WHERE BINARY EventId!=? AND NeedInstructor=? AND EventInstructorId=? AND EndDate>=?");
                $stmt->execute([$_POST['editEventId'], 'YES', $instructorId, $currentDateTime]); 
                $currentInstructorEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $pdo = null;
    
                foreach ($currentInstructorEvents as $row) 
                {                    
                    if (strtotime($startDate) <= strtotime($row['EndDate']) && strtotime($startDate) >= strtotime($row['StartDate']))
                    {
                        echo 'המדריך שנבחר עבור אירוע זה מעביר אירוע בזמנים שנבחרו עבור אירוע זה';
                        exit;
                    }
    
                    if (strtotime($endDate) <= strtotime($row['EndDate']) && strtotime($endDate) >= strtotime($row['StartDate']))
                    {
                        echo 'המדריך שנבחר עבור אירוע זה מעביר אירוע בזמנים שנבחרו עבור אירוע זה';
                        exit;
                    }
    
                    if (strtotime($row['StartDate']) >= strtotime($startDate) && strtotime($endDate) >= strtotime($row['StartDate']))
                    {
                        echo 'המדריך שנבחר עבור אירוע זה מעביר אירוע בזמנים שנבחרו עבור אירוע זה';
                        exit;
                    }              
                }
            }
    
            // UPDATE THE CIRCLE IN THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('school_events');
            $columns = 'EventName=?,EndDate=?,StartDate=?,EventInstructorId=?,EventDescription=?,NeedInstructor=?';
            $values = [$_POST['eventName'], $endDate, $startDate, $instructorId, $_POST['eventDescription'], $needInstructor, $_POST['editEventId']];
            $stmt = $pdo->prepare('UPDATE events_table SET '.$columns.' WHERE BINARY EventId=?');
            $stmt->execute($values); 
            $pdo = null;
    
            echo 'השינויים נשמרו בהצלחה';
            exit;
        }
    }

    // REMOVE EVENT
    if (isset($_POST['removeEventId']) && isset($_SESSION['ManagerId']))
    {
        $dayStart = date('Y-m-d').' 00:00:00'; 
        $dayEnd = date('Y-m-d').' 23:59:59';
        $currentDateTime = date("Y-m-d H:i:s");

        // SELECT THIS EVENT
        $pdo = UTILITIES::PDO_DB_Connection('school_events');
        $stmt = $pdo->prepare("SELECT * FROM events_table WHERE BINARY EventId=?");                         
        $stmt->execute([$_POST['removeEventId']]); 
        $eventResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        if (!$eventResult || count($eventResult) == 0)
        {
            echo 'האירוע המבוקש לא קיים במערכת';
            exit;
        }

        // CHECK IF THE EVENT IS ALREADY STARTED
        if (strtotime($currentDateTime) > strtotime($eventResult[0]['StartDate']))
        {
            echo 'האירוע המבוקש כבר התחיל, לא ניתן לבטלו כעת';
            exit;
        }

        // CHECK IF THIS EVENT HAS EXPENSES
        $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
        $stmt = $pdo->prepare("SELECT * FROM events_budget WHERE BINARY EventId=?");                         
        $stmt->execute([$_POST['removeEventId']]); 
        $eventExpensesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        if (count($eventExpensesResult) > 0)
        {
            echo 'נרשמו הוצאות עבור האירוע המבוקש, לא ניתן לבטלו';
            exit;
        }

        $pdo = UTILITIES::PDO_DB_Connection('school_events');
        $stmt = $pdo->prepare("DELETE FROM events_table WHERE BINARY EventId=?");                         
        $stmt->execute([$_POST['removeEventId']]); 
        $pdo = null; 

        echo "האירוע הוסר בהצלחה";
        exit;
    }

    // REMOVE CIRCLE
    if (isset($_POST['removeCircleId']) && isset($_SESSION['ManagerId']))
    {
        // SELECT THE EVENT
        $dayStart = date('Y-m-d').' 00:00:00'; 
        $dayEnd = date('Y-m-d').' 23:59:59';
        $currentDateTime = date("Y-m-d H:i:s");

        // SELECT THIS CIRCLE
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY CircleId=?");                         
        $stmt->execute([$_POST['removeCircleId']]); 
        $circleResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        if (!$circleResult || count($circleResult) == 0)
        {
            echo 'החוג המבוקש לא קיים במערכת';
            exit;
        }

        // CHECK IF THE CIRCLE IS ALREADY STARTED
        if (strtotime($currentDateTime) > strtotime($circleResult[0]['StartDate']))
        {
            echo 'החוג המבוקש כבר התחיל, לא ניתן לבטלו כעת';
            exit;
        }

        // SELECT THE CIRCLE MEETINGS TO REMOVE
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT MeetingId FROM circle_meetings WHERE BINARY CircleId=?");                         
        $stmt->execute([$_POST['removeCircleId']]); 
        $circleMeetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null; 

        // REMOVE MEETING PARTICIPANTS
        $participantsIds = [];
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        foreach ($circleMeetings as $cmKey => $cmValue)
        {            
            $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");                         
            $stmt->execute([$cmValue['MeetingId']]); 
            $meetingParticipants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // SAVE MEETING PARTICIPANTS
            foreach ($meetingParticipants as $mpKey => $mpValue)
            {
                if (!in_array($mpValue['StudentId'], $participantsIds)) {
                    array_push($participantsIds, $mpValue['StudentId']);
                }
            }

            // REMOVE PARTICIPANTS OF THIS MEETING
            $stmt = $pdo->prepare("DELETE FROM circle_participants WHERE BINARY MeetingId=?");                         
            $stmt->execute([$cmValue['MeetingId']]); 
        }

        // REMOVE CIRCLE MEETINGS
        $stmt = $pdo->prepare("DELETE FROM circle_meetings WHERE BINARY CircleId=?");                         
        $stmt->execute([$_POST['removeCircleId']]); 

        // REMOVE THE CIRCLE ITSELF
        $stmt = $pdo->prepare("DELETE FROM circles_table WHERE BINARY CircleId=?");                         
        $stmt->execute([$_POST['removeCircleId']]); 
        $pdo = null; 

        // SEND MESSAGE TO ALL PARTICIPANTS        
        $message = 'חוג '.$circleResult[0]['CircleName'].' שנרשמת אליו לשבוע זה בוטל';
        $pdo = UTILITIES::PDO_DB_Connection('school_messages');
        foreach ($participantsIds as $pKey => $pValue)
        {
            $stmt = $pdo->prepare("INSERT INTO messages_table (Active,MessageText,CreationDate,ToSpecificStudent) VALUES (?,?,?,?)");
            $stmt->execute(['YES', $message, $currentDateTime, $pValue]); 
        }
        $pdo = null; 

        echo "החוג הוסר בהצלחה";
        exit;
    }

    // UPDATE PROFILE
    if (isset($_POST['firstName']) && isset($_POST['lastName']) && isset($_POST['phone']) && isset($_POST['email']))
    {
        if (empty($_POST['firstName']) || empty($_POST['lastName']) || empty($_POST['phone']) || empty($_POST['email'])) 
        {
            echo 'נא למלא את השדות החסרים';
            exit;
        }

        $table = 'students';
        if (isset($_SESSION['InstructorId'])) { $table = 'instructors'; }
        if (isset($_SESSION['ManagerId'])) { $table = 'managers'; }
        
        $row = 'StudentId';
        if (isset($_SESSION['InstructorId'])) { $row = 'InstructorId'; }
        if (isset($_SESSION['ManagerId'])) { $row = 'ManagerId'; }

        // SELECT THIS PROFILE
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("UPDATE $table SET FirstName=?,LastName=?,Phone=?,Email=? WHERE $row=?");                         
        $stmt->execute([$_POST['firstName'], $_POST['lastName'], $_POST['phone'], $_POST['email'], $_SESSION[$row]]); 
        $pdo = null; 

        $_SESSION['FirstName'] = $_POST['firstName'];
        $_SESSION['LastName'] = $_POST['lastName'];
        $_SESSION['Phone'] = $_POST['phone'];
        $_SESSION['Email'] = $_POST['email'];

        echo 'השינויים נשמרו בהצלחה';
        exit;
    }

    // SAVE CIRCLE SCHEDULE UPDATE
    if (isset($_POST['circleInstructorName']) && isset($_POST['editCircleId']) && isset($_POST['circleSchedule']) 
        && isset($_POST['chMeetingIds']) && isset($_POST['rmMeetingIds']) && isset($_POST['finMeetings']))
    {
        // CHECK IF THIS CIRCLE EXISTS
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY CircleId=?");
        $stmt->execute([$_POST['editCircleId']]); 
        $circleResult = $stmt->fetchAll(PDO::FETCH_ASSOC);     
        $pdo = null;

        if (!$circleResult || count($circleResult) == 0)
        {
            echo 'החוג לא קיים במערכת';
            exit;
        }

        $circleMeetings = json_decode(stripslashes($_POST['circleSchedule']));
        $changedMeetingsIds = json_decode(stripslashes($_POST['chMeetingIds']));
        $removedMeetingsIds = json_decode(stripslashes($_POST['rmMeetingIds']));
        $finishedMeetings = json_decode(stripslashes($_POST['finMeetings']));

        // CHECK IF THERE ARE MEETINGS FOR THIS CIRCLE        
        $full = false;
        for ($i = 0; $i < count($circleMeetings); $i++)
        {
            if (count($circleMeetings[$i]) > 0) {
                $full = true;
                break;
            }
        }
        if ($full == false) {
            echo 'הוסף/י מפגשים לחוג';
            exit;
        }

        $day = date('w');
        $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
        $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
        $currentDateTime = date("Y-m-d H:i:s"); 
        
        $startDate = ''; $endDate = '';
        
        // SET THE START DATE AS THE START OF THE FIRST MEETING
        for ($i = 0; $i < count($circleMeetings); $i++)
        {
            if (count($circleMeetings[$i]) > 0 && $startDate == '')
            {
                $startTime = $circleMeetings[$i][0][0];
                $startDate = date('Y-m-d', strtotime('+'.($i - $day).' days')).' '.$startTime;
                break;
            }
        }    
        
        // SET THE END DATE AS THE END OF THE LAST MEETING
        for ($i = count($circleMeetings) - 1; $i >= 0; $i--)
        {
            if (count($circleMeetings[$i]) > 0 && $endDate == '')
            {
                $endTime = $circleMeetings[$i][count($circleMeetings[$i]) - 1][1];
                $endDate = date('Y-m-d', strtotime('+'.($i - $day).' days')).' '.$endTime;
                break;
            }
        }

        $finishedMeetingsCount = 0;
        foreach ($finishedMeetings as $fmKey => $fmValue)
        {
            if (count($fmValue) > 0) {
                $finishedMeetingsCount += count($fmValue);
            }
        }

        // VALIDATE START DATE
        if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($startDate) && $finishedMeetingsCount == 0) 
        {
            echo 'שגיאה: זמן שגוי עבור המפגש הראשון של החוג';
            exit;
        }

        if ($finishedMeetingsCount > 0 && $startDate != $circleResult[0]['StartDate'])
        {
            echo 'שגיאה: זמן שגוי עבור המפגש הראשון של החוג';
            exit;
        }

        // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS CIRCLE, (AGAINST OTHER CIRCLES)
        $instructorIdNumber = explode(' - ', $_POST['circleInstructorName'])[1];
        $instructorId = 0;

        // SELECT THE ID OF THE INSTRUCTOR
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("SELECT InstructorId FROM instructors WHERE BINARY IdNumber=?");
        $stmt->execute([$instructorIdNumber]); 
        $instructorIdResults = $stmt->fetchAll(PDO::FETCH_ASSOC);      
        $pdo = null;

        if (!$instructorIdResults || count($instructorIdResults) == 0) {
            echo 'המדריך לא קיים במערכת';
            exit;
        }
        
        foreach ($instructorIdResults as $row) {
            $instructorId = $row['InstructorId'];
        }

        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate>=? AND CircleId!=?");
        $stmt->execute([$instructorId, $currentDateTime, $_POST['editCircleId']]); 
        $currentInstructorCircles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        
        foreach ($currentInstructorCircles as $row) 
        {
            if (strtotime($startDate) <= strtotime($row['EndDate']) && strtotime($startDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר חוג אחר בזמנים שנבחרו עבור חוג זה';
                exit;
            }

            if (strtotime($endDate) <= strtotime($row['EndDate']) && strtotime($endDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר חוג אחר בזמנים שנבחרו עבור חוג זה';
                exit;
            }

            if (strtotime($row['StartDate']) >= strtotime($startDate) && strtotime($endDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר חוג אחר בזמנים שנבחרו עבור חוג זה';
                exit;
            }
        }   

        // CHECK INSTRUCTOR'S AVAILABILITY FOR THIS CIRCLE, (AGAINST OTHER EVENTS)
        $pdo = UTILITIES::PDO_DB_Connection('school_events');
        $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM events_table WHERE BINARY NeedInstructor=? AND EventInstructorId=? AND EndDate>=?");
        $stmt->execute(['YES', $instructorId, $currentDateTime]); 
        $currentInstructorEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        foreach ($currentInstructorEvents as $row) 
        {
            if (strtotime($startDate) <= strtotime($row['EndDate']) && strtotime($startDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר אירוע בזמנים שנבחרו עבור חוג זה';
                exit;
            }

            if (strtotime($endDate) <= strtotime($row['EndDate']) && strtotime($endDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר אירוע בזמנים שנבחרו עבור חוג זה';
                exit;
            }

            if (strtotime($row['StartDate']) >= strtotime($startDate) && strtotime($endDate) >= strtotime($row['StartDate']))
            {
                echo 'המדריך שנבחר עבור חוג זה מעביר אירוע בזמנים שנבחרו עבור חוג זה';
                exit;
            }
        }

        $changedMeetingsParticipants = []; /* [MeetingId, Start, End, UpdatedStart, UpdatedEnd, [ParticipantsId]] */
        $removedMeetingsParticipants = []; /* [StartDate, EndDate, [StudentId]] */

        // FOREACH REMOVED MEETING CHECK IF MEETING IS NOT STARTED ALREADY, IF NOT - GET ALL THE PARTICIPANTS OF THIS MEETING
        $error = '';
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');        
        foreach ($removedMeetingsIds as $rmKey => $rmValue)
        {
            $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM circle_meetings WHERE BINARY MeetingId=?");
            $stmt->execute([$rmValue]); 
            $removedMeetingResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($removedMeetingResult) > 0)
            {
                if (strtotime($currentDateTime) > strtotime($removedMeetingResult[0]['StartDate'])) 
                {
                    $error .= '/nלא ניתן להסיר את המפגש המתחיל ב: '.$removedMeetingResult[0]['StartDate'].' ומסתיים ב '.$removedMeetingResult[0]['EndDate'].'';
                }
                else 
                {
                    $participants = [];
                    $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");
                    $stmt->execute([$rmValue]);
                    $rmParticipantsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rmParticipantsResult as $rpKey => $rpValue) {
                        array_push($participants, $rpValue['StudentId']);
                    } 
                    if (count($participants) > 0) {
                        array_push($removedMeetingsParticipants, [$removedMeetingResult[0]['StartDate'], $removedMeetingResult[0]['EndDate'], $participants]);
                    }
                }
            }
        }

        // FOREACH CHANGED MEETING CHECK IF MEETING IS NOT STARTED ALREADY, IF NOT - GET ALL THE PARTICIPANTS OF THIS MEETING
        foreach ($changedMeetingsIds as $chKey => $chValue)
        {
            $stmt = $pdo->prepare("SELECT StartDate,EndDate FROM circle_meetings WHERE BINARY MeetingId=?");
            $stmt->execute([$chValue]); 
            $changedMeetingResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($changedMeetingResult) > 0)
            {
                if (strtotime($currentDateTime) > strtotime($changedMeetingResult[0]['StartDate'])) 
                {
                    $error .= '/nלא ניתן לשנות את המפגש המתחיל ב: '.$changedMeetingResult[0]['StartDate'].' ומסתיים ב '.$changedMeetingResult[0]['EndDate'].'';
                }
                else 
                {
                    // GET THE NEW TIME FOR THE UPDATED MEETING
                    $meetingDay = explode(' ', $changedMeetingResult[0]['StartDate'])[0];
                    $start = $meetingDay.' ';
                    $end = $meetingDay.' ';
                    for ($i = 0; $i < 7; $i++)
                    {
                        foreach ($circleMeetings[$i] as $cmKey => $cmValue)
                        {
                            if (count($cmValue) == 3 && $cmValue[2] == $chValue) {
                                $start .= $cmValue[0];
                                $end .= $cmValue[1];
                            }
                        }
                    }

                    // GET THE PARTICIPANTS OF THIS CHANGED MEETING
                    $participants = [];
                    $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");
                    $stmt->execute([$chValue]);
                    $chParticipantsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($chParticipantsResult as $cpKey => $cpValue) {
                        array_push($participants, $cpValue['StudentId']);
                    } 
                    if (count($participants) > 0) {
                        array_push($changedMeetingsParticipants, [$start, $end, $participants]);
                    }
                }
            }
        }
        $pdo = null;

        if ($error != '')
        {
            echo $error;
            exit;
        }

        // CHECK FOR COLLISION OF CHANGED MEETINGS
        if (count($changedMeetingsParticipants) > 0)
        {
            // SELECT ALL FUTURE MEETINGS TO CHECK FOR COLLISION
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');  
            $stmt = $pdo->prepare("SELECT MeetingId,StartDate,EndDate FROM circle_meetings WHERE BINARY EndDate>=? AND CircleId!=?");
            $stmt->execute([$currentDateTime, $_POST['editCircleId']]); 
            $otherMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            $pdo = null;
            
            // GET ALL STUDENTS THAT ARE REGISTERED TO THOSE MEETINGS
            $otherMeetingsStudents = [];
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');  
            foreach ($otherMeetingsResult as $omKey => $omValue)
            {                
                $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");
                $stmt->execute([$omValue['MeetingId']]); 
                $studentsResult = $stmt->fetchAll(PDO::FETCH_ASSOC); 

                $students = [];
                foreach ($studentsResult as $sKey => $sValue) {
                    array_push($students, $sValue['StudentId']);
                }
                array_push($otherMeetingsStudents, $students);
            }
            $pdo = null;

            // ITERATE OVER EACH CHANGED MEETING
            foreach ($changedMeetingsParticipants as $cmpKey => $cmpValue)
            {
                // ITERATE OVER EACH OTHER MEETING
                foreach ($otherMeetingsResult as $omKey => $omValue)
                {
                    // CHECK IF THIS MEETING IS COLIIDING WITH THE CHANGED MEETING
                    $changedStart = $cmpValue[0];
                    $changedEnd = $cmpValue[1];                    
                    $otherStart = $omValue['StartDate'];
                    $otherEnd = $omValue['EndDate'];

                    // CHECK IF A STUDENT FROM THE CHANGED MEETING IS PARTICIPATING IN THE OTHER MEETING
                    $commonStudent = false;
                    foreach ($cmpValue[2] as $pKey => $pValue)
                    {
                        if (in_array($pValue, $otherMeetingsStudents[$omKey])) {
                            $commonStudent = true; 
                            break;
                        }
                    }                 
                    
                    if ($commonStudent !== false)
                    { 
                        if (strtotime($otherStart) <= strtotime($changedEnd) && strtotime($otherStart) >= strtotime($changedStart))
                        {
                            echo 'לא ניתן לשנות מפגש לתאריך: '.$changedStart.' עד: '.$changedEnd.': מפני שהוא מתנגש עם מפגשים אחרים של תלמידים שנרשמו אליו.';
                            exit;
                        }

                        if (strtotime($otherEnd) <= strtotime($changedEnd) && strtotime($otherEnd) >= strtotime($changedStart))
                        {
                            echo 'לא ניתן לשנות מפגש לתאריך: '.$changedStart.' עד: '.$changedEnd.': מפני שהוא מתנגש עם מפגשים אחרים של תלמידים שנרשמו אליו.';
                            exit;
                        }

                        if (strtotime($changedStart) >= strtotime($otherStart) && strtotime($otherEnd) >= strtotime($changedStart))
                        {
                            echo 'לא ניתן לשנות מפגש לתאריך: '.$changedStart.' עד: '.$changedEnd.': מפני שהוא מתנגש עם מפגשים אחרים של תלמידים שנרשמו אליו.';
                            exit;
                        }
                    }                
                }
            }
        }

        if (!isset($_POST['confirmed']))
        {
            // ECHO A CONFIRMATION SCREEN TO ASK THE MANAGER TO CONFIRM THE CHANGES
            $confirmUpdates = 
            'CONFIRM<div class="secondary-screen-w noselect">
                <div class="secondary-screen" style="width: 460px;">
                    <div class="secondary-sc-title-w">
                        <div class="secondary-sc-title">עדכון תלמידים</div>
                        <img class="close-secondary-sc" src="http://'.$GLOBALS['SERVER_ADDRESS'].'/images/close.png"/>
                    </div>
                    <div class="ssc-body-w">
                        <div class="student-list-items-w">';
            $needToConfirm = false;

            if (count($changedMeetingsParticipants) > 0)
            {
                // OUTPUT A LIST OF THE STUDENTS THAT  ARE REGISTERED TO UPDATED MEETINGS
                $pdo = UTILITIES::PDO_DB_Connection('school_entities');  
                foreach ($changedMeetingsParticipants as $cmKey => $cmValue)
                {
                    foreach ($cmValue[2] as $key => $value)
                    {
                        $needToConfirm = true;
                        // SELECT THE STUDENT NAME                    
                        $stmt = $pdo->prepare("SELECT FirstName,LastName FROM students WHERE BINARY StudentId=?");
                        $stmt->execute([$value]); 
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 

                        if (count($result) > 0)
                        {
                            $meetingDate = explode(' ', $cmValue[0])[0];
                            $meetingDate = str_replace('-', '/', $meetingDate);
                            $meetingDate = date('d/m/Y', strtotime($meetingDate));

                            $startHour = explode(' ', $cmValue[0])[1];
                            $endHour = explode(' ', $cmValue[1])[1];

                            $top = ($cmKey == 0 && $key == 0) ? ' margin-top: 0;' : '';
                            $confirmUpdates .= 
                            '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'">
                                <div class="student-name-li-w">'.$result[0]['FirstName'].' '.$result[0]['LastName'].'</div>
                                <div class="student-circles-li-w" style="color: #454545;">משתתף במפגש ב '.$meetingDate.'</div>
                                <div class="student-circles-li-w">מ: '.$startHour.' עד: '.$endHour.'</div>        
                            </div>';
                        }                        
                    }                
                }
                $pdo = null;
            }

            if (count($removedMeetingsParticipants) > 0)
            {
                // OUTPUT A LIST OF THE STUDENTS THAT ARE REGISTERED TO REMOVED MEETINGS
                $pdo = UTILITIES::PDO_DB_Connection('school_entities');  
                foreach ($removedMeetingsParticipants as $rmKey => $rmValue)
                {
                    foreach ($rmValue[2] as $key => $value)
                    {
                        $needToConfirm = true;
                        // SELECT THE STUDENT NAME                    
                        $stmt = $pdo->prepare("SELECT FirstName,LastName FROM students WHERE BINARY StudentId=?");
                        $stmt->execute([$value]); 
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 

                        $top = ($cmKey == 0 && $key == 0) ? ' margin-top: 0;' : '';
                        $confirmUpdates .= 
                        '<div class="circle-students-list-item" style="direction: rtl; text-align: right;'.$top.'">
                            <div class="student-name-li-w">'.$result[0]['FirstName'].' '.$result[0]['LastName'].'</div>
                            <div class="student-circles-li-w">משתתף במפגש מ: '.$rmValue[0].' עד: '.$rmValue[1].'</div>        
                        </div>';
                    }           
                }
                $pdo = null;
            }

            if ($needToConfirm !== false)
            {
                $confirmUpdates .= '</div>
                        </div>
                        <div class="ssc-footer-w">
                            <div id="updateCircleMeetings" class="ssc-action-btn animated-transition noselect"><a>אשר עדכון לתלמידים</a></div>
                        </div>
                    </div>
                </div>';
                echo $confirmUpdates;
                exit;
            }
        }        

        // REMOVE MEETINGS TO REMOVE
        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
        foreach ($removedMeetingsIds as $rmKey => $rmValue)
        {            
            $stmt = $pdo->prepare("DELETE FROM circle_meetings WHERE BINARY MeetingId=?");
            $stmt->execute([$rmValue]); 
        }      
        
        // UPDATE MEETINGS AND INSERT NEW MEETINGS
        foreach ($circleMeetings as $cmKey => $cmValue) // 7 DAYS
        {
            if (count($cmValue) > 0)
            {
                foreach ($cmValue as $key => $value) // $value = [start, end, meetingId?]
                {                
                    $offset = $cmKey - $day;
                    $iThDate = date('d/m/Y', strtotime('+'.$offset.' days'));
                    if ($offset < 0) { $iThDate = date('d/m/Y', strtotime($offset.' days')); }
                    $unixIthDate = str_replace('/', '-', $iThDate);
                    $unixIthDate = date('Y-m-d', strtotime($unixIthDate));

                    $start = $unixIthDate.' '.$value[0];
                    $end = $unixIthDate.' '.$value[1];
                    
                    if (strtotime($start) > strtotime($currentDateTime))
                    {
                        if (count($value) == 3) 
                        {
                            $stmt = $pdo->prepare("UPDATE circle_meetings SET StartDate=?,EndDate=? WHERE BINARY MeetingId=?");
                            $stmt->execute([$start, $end, $value[2]]);                         
                        }             
                        else 
                        {
                            $stmt = $pdo->prepare("INSERT INTO circle_meetings (StartDate,EndDate,CircleId,WeekDay) VALUES (?,?,?,?)");
                            $stmt->execute([$start, $end, $_POST['editCircleId'], $cmKey]); 
                        }                    
                    }                
                }
            }            
        }

        // UPDATE CIRCLE START DATE AND END DATE
        $stmt = $pdo->prepare("UPDATE circles_table SET StartDate=?,EndDate=?,CircleInstructorId=? WHERE BINARY CircleId=?");
        $stmt->execute([$startDate, $endDate, $instructorId, $_POST['editCircleId']]); 
        $pdo = null;

        // SEND MESSAGE TO REGISTERED STUDENTS
        $changedSentStudentIds = [];
        $removedSentStudentIds = [];
        $pdo = UTILITIES::PDO_DB_Connection('school_messages');
        if (isset($_POST['confirmed']))
        {
            foreach ($changedMeetingsParticipants as $cmpKey => $cmpValue)
            {
                foreach ($cmpValue[2] as $pKey => $pValue)
                {
                    if (!in_array($pValue, $changedSentStudentIds))
                    {
                        $stmt = $pdo->prepare("INSERT INTO messages_table (Active,ToSpecificStudent,FromInstructor,MessageText,CreationDate) VALUES (?,?,?,?,?)");
                        $stmt->execute(['YES', $pValue, $instructorId, 'שים לב: שונו מפגשים שנרשמת אליהם בחוג '.$circleResult[0]['CircleName'], $currentDateTime]); 
                        array_push($changedSentStudentIds, $pValue);
                    }                    
                }
            }

            foreach ($removedMeetingsParticipants as $cmpKey => $cmpValue)
            {
                foreach ($cmpValue[2] as $pKey => $pValue)
                {
                    if (!in_array($pValue, $removedSentStudentIds))
                    {
                        $stmt = $pdo->prepare("INSERT INTO messages_table (Active,ToSpecificStudent,FromInstructor,MessageText,CreationDate) VALUES (?,?,?,?,?)");
                        $stmt->execute(['YES', $pValue, $instructorId, 'שים לב: בוטלו מפגשים שנרשמת אליהם בחוג '.$circleResult[0]['CircleName'], $currentDateTime]);
                        array_push($removedSentStudentIds, $pValue);
                    }                     
                }
            }
        }
        $pdo = null;

        echo 'החוג עודכן בהצלחה';
        exit;
    }

    // ADD A SALARY FOR AN INSTRUCTOR
    if (isset($_POST['selectedInstructorSalary']) && isset($_POST['HourSalary']) && isset($_POST['workHours']) && isset($_POST['salaryId'])
        && isset($_POST['instructorId']))
    {
        $currentDateTime = date("Y-m-d H:i:s");
        $monthEndDate = date("Y-m-t");

        // CHECK IF THIS INSTRUCTOR EXISTS IN THE SYSTEM
        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
        $stmt = $pdo->prepare("SELECT * FROM instructors WHERE BINARY InstructorId=?");
        $stmt->execute([$_POST['instructorId']]); 
        $instructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);     
        $pdo = null;

        if (count($instructorResult) == 0)
        {
            echo 'המדריך לא קיים במערכת';
            exit;
        }

        if (!is_numeric($_POST['workHours']) || !is_numeric($_POST['HourSalary']))
        {
            echo 'נא להזין ערכים תקינים עבור משכורת';
            exit;
        }

        $salary = floatval($_POST['HourSalary']) * floatval($_POST['workHours']);
        if (strlen($salary) > 4) {
            $salary = substr($salary, 0, 4);
        }

        // SELECT THE GENERAL BUDGET
        $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
        $stmt = $pdo->prepare("SELECT TotalSchoolBudget FROM total_budget");
        $stmt->execute(); 
        $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalBudget = floatval($totalBudgetResult[0]['TotalSchoolBudget']);

        // ADD OR UPDATE SALARY
        if ($_POST['salaryId'] == '-1')
        {
            // ADD SALARY
            $stmt = $pdo->prepare("INSERT INTO instructors_salaries (InstructorId,Salary,Date) VALUES (?,?,?)");
            $stmt->execute([$_POST['instructorId'], $salary, $monthEndDate]);
            $totalBudget -= floatval($salary);
        }
        else 
        {
            // SELECT CURRENT SALARY
            $stmt = $pdo->prepare("SELECT Salary FROM instructors_salaries WHERE BINARY InstructorId=? AND Date=?");
            $stmt->execute([$_POST['instructorId'], $monthEndDate]);
            $currentSalaryResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $currentSalary = floatval($currentSalaryResult[0]['Salary']);

            // UPDATE SALARY            
            $stmt = $pdo->prepare("UPDATE instructors_salaries SET Salary=? WHERE BINARY InstructorId=? AND Date=?");
            $stmt->execute([$salary, $_POST['instructorId'], $monthEndDate]);

            // SET THE TOTAL BUDGET
            $totalBudget = $totalBudget - (floatval($salary) - $currentSalary);
        }

        // UPDATE THE TOTAL BUDGET
        $stmt = $pdo->prepare("UPDATE total_budget SET TotalSchoolBudget=?");
        $stmt->execute([$totalBudget]);
        $pdo = null;

        echo 'המשכורת נוספה בהצלחה';
        exit;   
    }

?>