<?php

    global $SERVER_ADDRESS; $SERVER_ADDRESS = 'localhost/NewSite';

    class PAGES
    {
        // RETURNS THE PROFILE SCREEN JS
        public static function ProfileScreenJs() : string
        {
            $screen =
            '<script type="text/javascript">
                var firstName = "'.$_SESSION['FirstName'].'";
                var lastName = "'.$_SESSION['LastName'].'";
                var phone = "'.$_SESSION['Phone'].'";
                var email = "'.$_SESSION['Email'].'";
                function getProfileScreen()
                {
                    var profileScreen = 
                    `<div class="secondary-screen-w noselect">
                        <div class="secondary-screen" style="width: 460px;">
                            <div class="secondary-sc-title-w">
                                <div class="secondary-sc-title">פרופיל משתמש</div>
                                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
                            </div>
                            <div class="ssc-body-w">
                                <div class="ssc-field" style="margin-top: 0;">
                                    <div class="ssc-field-name">שם פרטי</div>
                                    <input type="text" id="firstName" class="ssc-field-value" value="'.$_SESSION['FirstName'].'"/>
                                </div>
                                <div class="ssc-field">
                                    <div class="ssc-field-name">שם משפחה</div>
                                    <input type="text" id="lastName" class="ssc-field-value" value="'.$_SESSION['LastName'].'"/>
                                </div>
                                <div class="ssc-field">
                                    <div class="ssc-field-name">פלאפון</div>
                                    <input type="text" id="phone" class="ssc-field-value" value="'.$_SESSION['Phone'].'"/>
                                </div>
                                <div class="ssc-field">
                                    <div class="ssc-field-name">מייל</div>
                                    <input type="text" id="email" class="ssc-field-value" value="'.$_SESSION['Email'].'"/>
                                </div>
                            </div>
                            <div class="ssc-footer-w">
                                <div id="updateProfile" class="ssc-disabled-action-btn noselect"><a>שמור/י</a></div>
                            </div>
                        </div>
                    </div>`;
                    return profileScreen;
                }
            </script>';
            return $screen;
        }

        // RETURNS THE SIGNUP HTML PAGE
        public static function LogIn(string $error = '') : string
        {
            $page =
            '<div class="header">
                <div class="logo"><a class="animated-transition" href="index.php">פנימיית כפר הנוער</a></div>
            </div>
            <div class="body-wrapper">
                <div class="title">כניסה</div>
                <div class="login-w">
                    <div class="login-cont">
                        <input id="username" name="username" type="text" class="input-field" style="margin-top: 0;" placeholder="שם משתמש"/>
                        <input id="password" name="password" type="text" class="input-field" placeholder="סיסמא (תאריך לידה, דוגמא: 1952-07-21)"/>';
                        if ($error != '')
                        {
                            $page .=
                            '<div class="error">'.$error.'</div>';
                        }
                        $page .=
                        '<div class="type-wrapper">
                            <label class="entity-type noselect" style="margin-right: 0;"><input type="checkbox" id="manager" name="manager"/>מנהל</label>
                            <label class="entity-type noselect"><input type="checkbox" id="instructor" name="instructor"/>מדריך</label>
                            <label class="entity-type noselect"><input type="checkbox" id="student" name="student"/>תלמיד</label>
                        </div>
                        <button id="login" name="login" class="button animated-transition">כניסה</button>
                    </div>
                </div>
            </div>';

            // THE USER IS ALREADY LOGGED IN
            if (isset($_SESSION['IdNumber']))
            {
                $page =
                '<div class="header">
                    <div class="logo"><a class="animated-transition" href="index.php">פנימיית כפר הנוער</a></div>
                </div>
                <div class="body-wrapper">
                    <div class="login-redirect">
                        <div class="login-r-txt">הנך כבר מחובר/ת למערכת</div>
                        <a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/index.php" class="link animated-transition">לדף הבית</a>
                    </div>
                </div>';
            }

            return $page;
        }

        // RETURNS THE LOGGED IN HOME PAGE
        public static function LoggedInHomePage() : string 
        {
            $page = '';
            $jsMessagesIds =
            '<script type="text/javascript">
                var messagesIds = [';

            $day = date('w');
            $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
            $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
            $currentDateTime = date("Y-m-d H:i:s");
            $startDay = date("Y-m-d").' 00:00:00';            

            // SELECT ALL THE CIRCLES THAT ARE THIS WEEK
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY EndDate>=? ORDER BY StartDate ASC");
            $stmt->execute([$currentDateTime]); 
            $circlesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            // SELECT CIRCLE INSTRUCTORS NAME
            $circleInstructors = [];                
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            foreach ($circlesResult as $key => $value)
            {
                $stmt = $pdo->prepare("SELECT FirstName,LastName FROM instructors WHERE BINARY InstructorId=?");                    
                $stmt->execute([$value['CircleInstructorId']]);
                $circleInstructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!$circleInstructorResult || count($circleInstructorResult) == 0) {
                    array_push($circleInstructors, 'המדריך לא רשום במערכת');
                }
                else {
                    array_push($circleInstructors, $circleInstructorResult[0]['FirstName'].' '.$circleInstructorResult[0]['LastName']);
                }
            }
            $pdo = null;

            // SELECT ALL THE EVENTS THAT ARE NOT ENDED
            $pdo = UTILITIES::PDO_DB_Connection('school_events');
            $stmt = $pdo->prepare("SELECT * FROM events_table WHERE BINARY EndDate>=? ORDER BY StartDate ASC");                    
            $stmt->execute([$currentDateTime]); 
            $eventsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            // SELECT EVENT INSTRUCTORS NAME
            $eventInstructors = [];                
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            foreach ($eventsResult as $key => $value)
            {
                if ($value['NeedInstructor'] == 'YES')
                {
                    $stmt = $pdo->prepare("SELECT FirstName,LastName FROM instructors WHERE BINARY InstructorId=?");                    
                    $stmt->execute([$value['EventInstructorId']]);
                    $eventInstructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!$eventInstructorResult || count($eventInstructorResult) == 0) {
                        array_push($eventInstructors, 'המדריך לא רשום במערכת');
                    }
                    else {
                        array_push($eventInstructors, $eventInstructorResult[0]['FirstName'].' '.$eventInstructorResult[0]['LastName']);
                    }
                }                    
                else {
                    array_push($eventInstructors, 'ללא מדריך');
                }
                
            }
            $pdo = null;

            if ($_SESSION['UserType'] == 'Student')
            {
                // SELECT ALL THE MESSAGES
                $pdo = UTILITIES::PDO_DB_Connection('school_messages');
                $stmt = $pdo->prepare("SELECT * FROM messages_table WHERE BINARY Active=? AND ToSpecificInstructor=? AND (ToSpecificStudent=? OR ToSpecificStudent=?) ORDER BY CreationDate DESC");                    
                $stmt->execute(['YES', -1, -1, $_SESSION['StudentId']]); 
                $messagesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $pdo = null; 

                $page =
                '<div class="header">
                    <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                    <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
                </div>
                <div>
                    <div class="user-profile-menu-w">  
                        <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                        <div id="studentCircleComments" class="user-profile-menu-item"><a>שלח משוב לחוגים</a></div>
                        <div id="studentReadCircleComments" class="user-profile-menu-item"><a>קריאת משובים</a></div>
                        <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                    </div>
                </div>
                <div class="body-wrapper">
                    <div class="title">פנימיית כפר הנוער - דף הבית</div> 
                    <div class="operations-w">
                        <div id="myStudentCircles" class="operation-btn animated-transition noselect" style="margin-right: 70px;"><a>החוגים שלי</a></div>
                    </div>
                    <div class="messages-title">הודעות</div>
                    <div class="messages-w">
                        <div class="messages-cont">';
                        $allMsgsRead = true;   
                        $comma = '';
                        foreach ($messagesResult as $index => $row)
                        {
                            if (empty($row['ReadByStudents']) || strpos($row['ReadByStudents'], '|'.$_SESSION['StudentId'].'|') === false)
                            {       
                                // SELECT THE DETAIL OF THE INSTRUCTOR WHO SENT THIS MESSAGE
                                $instructorName = '';
                                if ($row['FromInstructor'] != -1)
                                {
                                    $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                                    $stmt = $pdo->prepare("SELECT FirstName,LastName FROM instructors WHERE BINARY InstructorId=?");                    
                                    $stmt->execute([$row['FromInstructor']]); 
                                    $instructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    $pdo = null; 

                                    if (count($instructorResult) > 0) {
                                        $instructorName = 'נשלח מ: '.$instructorResult[0]['FirstName'].' '.$instructorResult[0]['LastName'];
                                    }
                                    else {
                                        $instructorName = 'המדריך/ה ששלח הודעה זו לא קיים המערכת';
                                    }
                                }
                                
                                $creationDate1 = substr($row['CreationDate'], 0, strpos($row['CreationDate'], ' '));
                                $creationDate1 = str_replace('-', '/', $creationDate1);
                                $creationDate1 = date('d/m/Y', strtotime($creationDate1));
                                $creationDate2 = substr($row['CreationDate'], strpos($row['CreationDate'], ' ') + 1);

                                $top = $allMsgsRead === true ? ' style="margin-top: 0;"' : '';
                                $page .=
                                '<div msgid="'.$row['MessageId'].'" class="message-w"'.$top.'>
                                    <div class="message-text">'.htmlentities($row['MessageText']).'</div>
                                    <div class="message-date">'.$creationDate1.'&nbsp;'.$creationDate2.'</div>';
                                    if ($instructorName != '') {
                                        $page .= '<div class="message-date" style="color: #079372;">'.$instructorName.'</div>';
                                    }
                                    $page .=
                                    '<div class="read-msg-w noselect animated-transition">סמן הודעה זו כנקראה</div>
                                </div>';
                                $jsMessagesIds .= $comma.'"'.$row['MessageId'].'"';
                                $comma = ',';
                                $allMsgsRead = false;
                            }                               
                        }                            
                        if ($allMsgsRead === true) 
                        {
                            $page .= 
                            '<div class="message-w" style="margin-top: 0;">
                                <div class="message-text">הנך מעודכן/ת, לא נמצאו עבורך הודעות חדשות כרגע</div>
                            </div>';
                        } 
                        $page .=
                        '</div>
                    </div>';  

                    $jsMessagesIds .= 
                    '];
                    var userId = '.$_SESSION['StudentId'].';
                    var userType = "Student";
                    var circlesEligibleForComments = [';

                    // SELECT ALL THE CIRCLES THAT ARE ELIGIBLE FOR COMMENT
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY EndDate>=? AND EndDate<=?");  
                    $dayStart = date('Y-m-d').' 00:00:00';                  
                    $stmt->execute([$dayStart, $currentDateTime]); 
                    $eligibleCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null; 

                    $circleAdded = false;
                    foreach ($eligibleCirclesResult as $ecKey => $ecValue)
                    {
                        // SELECT THE CIRCLE INSTRUCTOR
                        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                        $stmt = $pdo->prepare("SELECT * FROM instructors WHERE BINARY InstructorId=?");  
                        $stmt->execute([$ecValue['CircleInstructorId']]); 
                        $instructorIdResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $pdo = null; 

                        // SELECT ALL THE MEETINGS OF THIS CIRCLE TO SEE IF THE STUDENT IS PARTICIPATING IN THIS CIRCLE
                        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                        $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=?");  
                        $stmt->execute([$ecValue['CircleId']]); 
                        $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $pdo = null; 

                        $isParticipating = false;
                        foreach ($circleMeetingsResult as $cmKey => $cmValue)
                        {
                            // SELECT ALL THE PARTICIPANTS OF THIS MEETING
                            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                            $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY MeetingId=?");  
                            $stmt->execute([$cmValue['MeetingId']]); 
                            $meetingParticipantsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null; 

                            foreach ($meetingParticipantsResult as $mpKey => $mpValue)
                            {
                                if ($mpValue['StudentId'] == $_SESSION['StudentId']) {
                                    $isParticipating = true;
                                    break;
                                }
                            }

                            if ($isParticipating == true) {
                                break;
                            }
                        }

                        // CHECK IF THIS STUDENT SENT ALREADY A COMMENT FOR THIS CIRCLE
                        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                        $stmt = $pdo->prepare("SELECT * FROM circle_comments_table WHERE BINARY FromStudentId=? AND CircleId=?");  
                        $stmt->execute([$_SESSION['StudentId'], $ecValue['CircleId']]); 
                        $commentResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $pdo = null;
                        
                        if (count($commentResult) > 0) {
                            continue;
                        }
                        
                        $instructorName = count($instructorIdResult) > 0 ? $instructorIdResult[0]['FirstName'].' '.$instructorIdResult[0]['LastName'] : 'המדריך לא קיים במערכת';

                        if ($isParticipating == true)
                        {
                            if ($circleAdded == true) {
                                $jsMessagesIds .= ',';
                            } 
                            $jsMessagesIds .= '["'.$ecValue['CircleName'].'", "'.$ecValue['CircleId'].'", "'.$instructorName.'"]';                            
                            $circleAdded = true;  
                        }                                        
                    }

                    $jsMessagesIds .= '];
                    var circles = [';

                    $page .=
                    '<div class="events-container">
                        <div class="events-w">
                            <div class="event-title">חוגים</div>
                            <div class="events-data-w">';
                            for ($i = 0; $i < count($circlesResult); $i++)
                            {
                                // SELECT CIRCLE MEETINGS
                                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                                $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
                                $stmt->execute([$circlesResult[$i]['CircleId']]); 
                                $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $pdo = null; 

                                $lastRegistrationDate1 = substr($circlesResult[$i]['StartDate'], 0, strpos($circlesResult[$i]['StartDate'], ' '));
                                $lastRegistrationDate1 = str_replace('-', '/', $lastRegistrationDate1);
                                $lastRegistrationDate1 = date('d/m/Y', strtotime($lastRegistrationDate1));
                                $lastRegistrationDate2 = substr($circlesResult[$i]['StartDate'], strpos($circlesResult[$i]['StartDate'], ' ') + 1);

                                $page .=
                                '<div class="outer-event-w">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">'.htmlentities($circlesResult[$i]['CircleName']).'</div>
                                        <div class="event-data-txt" style="margin-top: 15px;"><span style="color: #000;">רקע</span>:&nbsp;'.htmlentities($circlesResult[$i]['CircleDescription']).'</div>
                                        <div class="event-data-txt"><span style="color: #000;">מדריך</span>:&nbsp;'.htmlentities($circleInstructors[$i]).'</div>';
                                        if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($circlesResult[$i]['StartDate'])) {
                                            $page .= '<div class="event-data-txt" style="color: #c41818;"><span style="color: #000;">תאריך אחרון להרשמה</span>:&nbsp;'.$lastRegistrationDate1.'&nbsp;'.$lastRegistrationDate2.'</div>';
                                        }
                                        else { 
                                            $page .= '<div class="event-data-txt"><span style="color: #000;">תאריך אחרון להרשמה</span>:&nbsp;'.$lastRegistrationDate1.'&nbsp;'.$lastRegistrationDate2.'</div>';
                                        }

                                        $circleStartDate = explode(' ', $circlesResult[$i]['StartDate'])[0];
                                        $circleStartDate = str_replace('-', '/', $circleStartDate);
                                        $circleStartDate = date('d/m/Y', strtotime($circleStartDate));

                                        $circleEndDate = explode(' ', $circlesResult[$i]['EndDate'])[0];
                                        $circleEndDate = str_replace('-', '/', $circleEndDate);
                                        $circleEndDate = date('d/m/Y', strtotime($circleEndDate));

                                        $page .=
                                        '<div class="event-data-txt"><span style="color: #000;">תאריך התחלה</span>:&nbsp;'.$circleStartDate.'</div>
                                        <div class="event-data-txt"><span style="color: #000;">תאריך סיום</span>:&nbsp;'.$circleEndDate.'</div>';
                                                                                
                                        $registered = false;
                                        $currentDay = -1;
                                        foreach ($circleMeetingsResult as $cmKey => $cmValue)
                                        {
                                            $hDay = 'יום ראשון';
                                            if ($cmValue['WeekDay'] == 1) { $hDay = 'יום שני'; }
                                            if ($cmValue['WeekDay'] == 2) { $hDay = 'יום שלישי'; }
                                            if ($cmValue['WeekDay'] == 3) { $hDay = 'יום רביעי'; }
                                            if ($cmValue['WeekDay'] == 4) { $hDay = 'יום חמישי'; }
                                            if ($cmValue['WeekDay'] == 5) { $hDay = 'יום שישי'; }
                                            if ($cmValue['WeekDay'] == 6) { $hDay = 'יום שבת'; }

                                            $meetingDate = explode(' ', $cmValue['StartDate'])[0];
                                            $meetingDate = str_replace('-', '/', $meetingDate);
                                            $meetingDate = date('d/m/Y', strtotime($meetingDate));

                                            if ($currentDay != $cmValue['WeekDay'])
                                            {
                                                $page .= '<div class="event-data-txt" style="text-align: center;"><span style="color: #000;">'.$hDay.' - '.$meetingDate.'</span></div>';
                                                $currentDay = $cmValue['WeekDay'];
                                            }
                                            
                                            $startHour = explode(' ', $cmValue['StartDate'])[1];
                                            $endHour = explode(' ', $cmValue['EndDate'])[1];

                                            $page .= '<div class="event-data-txt" style="text-align: center;">מ: '.$startHour.' עד: '.$endHour.'</div>';

                                            if ($registered == false)
                                            {
                                                // CHECK IF THIS STUDENT IS REGISTERED TO AT LEAST ONE MEETING OF THIS CIRCLE
                                                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                                                $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY MeetingId=? AND StudentId=?");
                                                $stmt->execute([$cmValue['MeetingId'], $_SESSION['StudentId']]); 
                                                $circleparticipantResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                $pdo = null; 

                                                if (count($circleparticipantResult) > 0) {
                                                    $registered = true;
                                                }
                                            }
                                        }

                                        $action = 'change';
                                        if ($registered !== false)
                                        {
                                            $page .=
                                            '<div class="subscribe-w animated-transition noselect">
                                                <a>שנה/י הרשמה</a>
                                            </div>';
                                            $action = 'change';
                                        }
                                        else
                                        {
                                            if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($circlesResult[$i]['StartDate']))
                                            {
                                                $page .=
                                                '<div class="disabled-subscribe-w noselect">
                                                    <a>בקש/י הרשמה מהמדריך</a>
                                                </div>';
                                                $action = 'ask';
                                            }
                                            else
                                            {
                                                $page .=
                                                '<div circleId="'.$circlesResult[$i]['CircleId'].'" class="subscribe-w animated-transition noselect">
                                                    <a>הרשמ/י לחוג</a>
                                                </div>';
                                                $action = 'register';
                                            }    
                                        }

                                        $jsMessagesIds .= 
                                        '["'.$action.'", "'.$circlesResult[$i]['CircleId'].'"],';

                                        $page .=                                        
                                    '</div>
                                </div>';                                
                            }
                            
                            if (count($circlesResult) > 0) {
                                $jsMessagesIds = substr($jsMessagesIds, 0, strlen($jsMessagesIds) - 1);
                            }
                            $jsMessagesIds .= '];
                            var weeklyFinishedCircles = [';

                            // SELECT ALL THE CIRCLES THAT CAN SHOW COMMENTS FOR THEM
                            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                            $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY EndDate<? AND EndDate>?");  
                            $startWeek = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
                            $stmt->execute([$currentDateTime, $startWeek]); 
                            $finishedCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null; 
        
                            $circleAdded = false;
                            foreach ($finishedCirclesResult as $fcKey => $fcValue)
                            {
                                // SELECT THE CIRCLE INSTRUCTOR
                                $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                                $stmt = $pdo->prepare("SELECT * FROM instructors WHERE BINARY InstructorId=?");  
                                $stmt->execute([$fcValue['CircleInstructorId']]); 
                                $instructorIdResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $pdo = null; 
        
                                // SELECT ALL THE MEETINGS OF THIS CIRCLE TO SEE IF THE STUDENT IS PARTICIPATING IN THIS CIRCLE
                                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                                $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=?");  
                                $stmt->execute([$fcValue['CircleId']]); 
                                $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $pdo = null; 
        
                                $isParticipating = false;
                                foreach ($circleMeetingsResult as $cmKey => $cmValue)
                                {
                                    // SELECT ALL THE PARTICIPANTS OF THIS MEETING
                                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                                    $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY MeetingId=?");  
                                    $stmt->execute([$cmValue['MeetingId']]); 
                                    $meetingParticipantsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    $pdo = null; 
        
                                    foreach ($meetingParticipantsResult as $mpKey => $mpValue)
                                    {
                                        if ($mpValue['StudentId'] == $_SESSION['StudentId']) {
                                            $isParticipating = true;
                                            break;
                                        }
                                    }
        
                                    if ($isParticipating == true) {
                                        break;
                                    }
                                }
                                
                                $instructorName = count($instructorIdResult) > 0 ? $instructorIdResult[0]['FirstName'].' '.$instructorIdResult[0]['LastName'] : 'המדריך לא קיים במערכת';
        
                                if ($isParticipating == true)
                                {
                                    if ($circleAdded == true) {
                                        $jsMessagesIds .= ',';
                                    } 
                                    $jsMessagesIds .= 
                                    '["'.$fcValue['CircleId'].'", "'.$fcValue['CircleName'].'"]';                            
                                    $circleAdded = true;  
                                }                                        
                            }

                            $jsMessagesIds .=
                            '];
                            </script>';

                            $page .=
                            '</div>
                        </div>
                        <div class="events-w">
                            <div class="event-title">אירועים</div>
                            <div class="events-data-w">';
                            for ($i = 0; $i < count($eventsResult); $i++)
                            {
                                $startDate = explode(' ', $eventsResult[$i]['StartDate'])[0];
                                $unixTimestamp = strtotime($startDate);
                                $startDayWeek = date("l", $unixTimestamp);

                                $endDate = explode(' ', $eventsResult[$i]['EndDate'])[0];
                                $unixTimestamp = strtotime($endDate);
                                $endDayWeek = date("l", $unixTimestamp);

                                $startDay = UTILITIES::GetHebrewDay($startDayWeek);
                                $endDay = UTILITIES::GetHebrewDay($endDayWeek); 
                                
                                $startDate = explode(' ', $eventsResult[$i]['StartDate'])[0];
                                $startDate = str_replace('-', '/', $startDate);
                                $startDate = date('d/m/Y', strtotime($startDate));

                                $endDate = explode(' ', $eventsResult[$i]['EndDate'])[0];
                                $endDate = str_replace('-', '/', $endDate);
                                $endDate = date('d/m/Y', strtotime($endDate)); 

                                $page .=
                                '<div class="outer-event-w">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">'.htmlentities($eventsResult[$i]['EventName']).'</div>
                                        <div class="event-data-txt" style="margin-top: 15px;"><span style="color: #000;">רקע</span>:&nbsp;'.htmlentities($eventsResult[$i]['EventDescription']).'</div>
                                        <div class="event-data-txt"><span style="color: #000;">מדריך</span>:&nbsp;'.htmlentities($eventInstructors[$i]).'</div>
                                        <div class="event-data-txt" style="text-align: center;"><span style="color: #000;">התחלה</span></div>
                                        <div class="event-data-txt" style="text-align: center;">'.$startDay.' - '.$startDate.' - מ: '.explode(' ', $eventsResult[$i]['StartDate'])[1].'</div>
                                        <div class="event-data-txt" style="text-align: center;"><span style="color: #000;">סיום</span></div>
                                        <div class="event-data-txt" style="text-align: center;">'.$endDay.' - '.$endDate.' - עד: '.explode(' ', $eventsResult[$i]['EndDate'])[1].'</div>
                                    </div>
                                </div>';
                            }
                            $page .=
                            '</div>
                        </div>';
                        /*<div class="events-w">
                            <div class="event-title">אודותינו</div>
                            <div class="events-data-w">
                                <div class="outer-event-w" style="border-bottom: 0;">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">קיץ 2021</div>
                                        <img class="event-image" src="images/1.jpg"/>
                                        <img class="event-image" src="images/2.jpg"/>
                                        <img class="event-image" src="images/3.jpg"/>
                                    </div>
                                </div>
                            </div>
                        </div>*/
                        $page .=
                    '</div>                
                </div>';
            }
            else if ($_SESSION['UserType'] == 'Instructor')
            {
                // SELECT ALL THE MESSAGES
                $pdo = UTILITIES::PDO_DB_Connection('school_messages');
                $stmt = $pdo->prepare("SELECT * FROM messages_table WHERE BINARY Active=? AND (ToSpecificInstructor=? OR ToSpecificInstructor=?) AND ToSpecificStudent=? AND FromInstructor=? ORDER BY CreationDate DESC");                    
                $stmt->execute(['YES', -1, $_SESSION['InstructorId'], -1, -1]); 
                $messagesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $pdo = null;  
                
                $page =
                '<div class="header">
                    <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                    <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
                </div>
                <div>
                    <div class="user-profile-menu-w">  
                        <div id="manageInstructorMessages" class="user-profile-menu-item"><a>ניהול הודעות לתלמידים</a></div>
                        <div id="instructorReadComments" class="user-profile-menu-item"><a>קריאת משובים</a></div>
                        <div id="instructorCircleComments" class="user-profile-menu-item"><a>פרסם/י משוב לחוגים</a></div>
                        <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                        <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                    </div>
                </div>
                <div class="body-wrapper">
                    <div class="title">פנימיית כפר הנוער - דף הבית</div> 
                    <div class="operations-w">
                        <div id="myStudents" class="operation-btn animated-transition noselect" style="margin-right: 70px;"><a>התלמידים שלי</a></div>  
                        <div id="addStudentToCircle" class="operation-btn animated-transition noselect"><a>רשום/י תלמיד לחוג</a></div>                        
                        <div id="myInstructorCircles" class="operation-btn animated-transition noselect"><a>החוגים שלי</a></div>
                        <div id="myInstructorEvents" class="operation-btn animated-transition noselect"><a>האירועים שלי</a></div>                        
                        <div id="sendInstructorMessage" class="operation-btn animated-transition noselect"><a>שלח הודעה לתלמיד/ים</a></div>
                    </div>
                    <div class="messages-title">הודעות</div>
                    <div class="messages-w">
                        <div class="messages-cont">';
                        $allMsgsRead = true;   
                        $comma = '';
                        foreach ($messagesResult as $index => $row)
                        {
                            if (empty($row['ReadByInstructors']) || strpos($row['ReadByInstructors'], '|'.$_SESSION['InstructorId'].'|') === false)
                            {                                    
                                $creationDate1 = substr($row['CreationDate'], 0, strpos($row['CreationDate'], ' '));
                                $creationDate1 = str_replace('-', '/', $creationDate1);
                                $creationDate1 = date('d/m/Y', strtotime($creationDate1));
                                $creationDate2 = substr($row['CreationDate'], strpos($row['CreationDate'], ' ') + 1);

                                $top = $allMsgsRead === true ? ' style="margin-top: 0;"' : '';
                                $page .=
                                '<div msgid="'.$row['MessageId'].'" class="message-w"'.$top.'>
                                    <div class="message-text">'.htmlentities($row['MessageText']).'</div>
                                    <div class="message-date">'.$creationDate1.'&nbsp;'.$creationDate2.'</div>
                                    <div class="read-msg-w noselect animated-transition">סמן הודעה זו כנקראה</div>
                                </div>';
                                $jsMessagesIds .= $comma.'"'.$row['MessageId'].'"';
                                $allMsgsRead = false;
                                $comma = ',';
                            }                               
                        }                            
                        if ($allMsgsRead === true) 
                        {
                            $page .= 
                            '<div class="message-w" style="margin-top: 0;">
                                <div class="message-text">הנך מעודכן/ת, לא נמצאו עבורך הודעות חדשות כרגע</div>
                            </div>';
                        } 
                        $page .=
                        '</div>
                    </div>';      

                    $jsMessagesIds .= 
                    '];
                    var myCircles = [';

                    // GET ALL CIRCLES FOR THIS INSTRUCTOR
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT CircleId,CircleName,StartDate,EndDate FROM circles_table WHERE BINARY EndDate>=? AND CircleInstructorId=?");
                    $stmt->execute([$currentDateTime, $_SESSION['InstructorId']]); 
                    $myCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null; 

                    foreach ($myCirclesResult as $key => $value)
                    {
                        $jsMessagesIds .=
                        '["'.$value['CircleName'].'", "'.$value['CircleId'].'", "'.$value['StartDate'].'", "'.$value['EndDate'].'"]';
                        if ($key < count($myCirclesResult) - 1) {
                            $jsMessagesIds .= ',';
                        }
                    }

                    $jsMessagesIds .=
                    '];
                    var students = [';

                    // GET ALL CIRCLES FOR THIS INSTRUCTOR
                    $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                    $stmt = $pdo->prepare("SELECT StudentId,IdNumber,FirstName,LastName FROM students");
                    $stmt->execute(); 
                    $studentsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null; 

                    foreach ($studentsResult as $key => $value)
                    {
                        $jsMessagesIds .=
                        '["'.$value['StudentId'].'", "'.$value['FirstName'].'", "'.$value['LastName'].'", "'.$value['IdNumber'].'"]';
                        if ($key < count($studentsResult) - 1) {
                            $jsMessagesIds .= ',';
                        }
                    }

                    $jsMessagesIds .=
                    '];
                    var circlesEligibleForComments = [';

                    // SELECT ALL THE CIRCLES THAT ARE ELIGIBLE FOR COMMENT
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate>=? AND EndDate<=?");  
                    $dayStart = date('Y-m-d').' 00:00:00';                  
                    $stmt->execute([$_SESSION['InstructorId'], $dayStart, $currentDateTime]); 
                    $eligibleCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null;

                    foreach ($eligibleCirclesResult as $ecKey => $ecValue)
                    {
                        $jsMessagesIds .= '["'.$ecValue['CircleName'].'", "'.$ecValue['CircleId'].'"]';
                        if ($ecKey < count($eligibleCirclesResult) - 1) {
                            $jsMessagesIds .= ',';
                        }
                    }

                    $jsMessagesIds .=
                    '];
                    var studentsOfCirclesEligibleForComments = [';

                    foreach ($eligibleCirclesResult as $ecKey => $ecValue)
                    {
                        // SELECT MEETINGS OF THIS CIRCLE 
                        $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                        $stmt = $pdo->prepare("SELECT MeetingId FROM circle_meetings WHERE BINARY CircleId=?");
                        $stmt->execute([$ecValue['CircleId']]); 
                        $meetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $pdo = null;

                        $comma = $ecKey > 0 ? ',' : '';
                        $jsMessagesIds .= $comma.'[';
                        $insertedParticipants = [];
                        foreach ($meetingsResult as $mKey => $mValue)
                        {
                            // SELECT MEETING PARTICIPANT IDS
                            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                            $stmt = $pdo->prepare("SELECT StudentId FROM circle_participants WHERE BINARY MeetingId=?");
                            $stmt->execute([$mValue['MeetingId']]); 
                            $participantsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null;

                            foreach ($participantsResult as $pKey => $pValue)
                            {
                                if (in_array($pValue['StudentId'], $insertedParticipants)) { continue; }

                                // SELECT THE DETAILS OF EACH PARTICIPANT
                                $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                                $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM students WHERE BINARY StudentId=?");
                                $stmt->execute([$pValue['StudentId']]); 
                                $studentsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $pdo = null;

                                if (count($studentsResult) > 0) {
                                    $studentDetails = $studentsResult[0]['FirstName'].' '.$studentsResult[0]['LastName'].' - '.$studentsResult[0]['IdNumber'];
                                }
                                $delimeter = count($insertedParticipants) > 0 ? ',' : '';
                                $jsMessagesIds .= $delimeter.'["'.$pValue['StudentId'].'", "'.$studentDetails.'"]';
                                array_push($insertedParticipants, $pValue['StudentId']);
                            }
                        }
                        $jsMessagesIds .= ']';
                    }

                    $jsMessagesIds .=
                    '];
                    var weeklyFinishedCircles = [';

                    // SELECT ALL THE CIRCLES THAT CAN SHOW COMMENTS FOR THEM
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT CircleId,CircleName FROM circles_table WHERE BINARY CircleInstructorId=? AND EndDate<? AND EndDate>?");  
                    $startWeek = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
                    $stmt->execute([$_SESSION['InstructorId'], $currentDateTime, $startWeek]); 
                    $finishedCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null;

                    foreach ($finishedCirclesResult as $fcKey => $fcValue)
                    {
                        $jsMessagesIds .=
                        '["'.$fcValue['CircleId'].'", "'.$fcValue['CircleName'].'"]';
                        if ($fcKey < count($finishedCirclesResult) - 1) {
                            $jsMessagesIds .= ',';
                        }
                    }

                    $jsMessagesIds .=
                    '];
                    var userId = '.$_SESSION['InstructorId'].';
                    var userType = "Instructor";
                    </script>';

                    $page .=
                    '<div class="events-container">
                        <div class="events-w">
                            <div class="event-title">חוגים</div>
                            <div class="events-data-w">';
                            for ($i = 0; $i < count($circlesResult); $i++)
                            {
                                // SELECT CIRCLE MEETINGS
                                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                                $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
                                $stmt->execute([$circlesResult[$i]['CircleId']]); 
                                $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $pdo = null; 

                                $lastRegistrationDate1 = substr($circlesResult[$i]['StartDate'], 0, strpos($circlesResult[$i]['StartDate'], ' '));
                                $lastRegistrationDate1 = str_replace('-', '/', $lastRegistrationDate1);
                                $lastRegistrationDate1 = date('d/m/Y', strtotime($lastRegistrationDate1));
                                $lastRegistrationDate2 = substr($circlesResult[$i]['StartDate'], strpos($circlesResult[$i]['StartDate'], ' ') + 1);

                                $page .=
                                '<div class="outer-event-w">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">'.htmlentities($circlesResult[$i]['CircleName']).'</div>
                                        <div class="event-data-txt" style="margin-top: 15px;"><span style="color: #000;">רקע</span>:&nbsp;'.htmlentities($circlesResult[$i]['CircleDescription']).'</div>
                                        <div class="event-data-txt"><span style="color: #000;">מדריך</span>:&nbsp;'.htmlentities($circleInstructors[$i]).'</div>';
                                        if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($circlesResult[$i]['StartDate'])) {
                                            $page .= '<div class="event-data-txt" style="color: #c41818;"><span style="color: #000;">תאריך אחרון להרשמה</span>:&nbsp;'.$lastRegistrationDate1.'&nbsp;'.$lastRegistrationDate2.'</div>';
                                        }
                                        else { 
                                            $page .= '<div class="event-data-txt"><span style="color: #000;">תאריך אחרון להרשמה</span>:&nbsp;'.$lastRegistrationDate1.'&nbsp;'.$lastRegistrationDate2.'</div>';
                                        }
                                        
                                        $circleStartDate = explode(' ', $circlesResult[$i]['StartDate'])[0];
                                        $circleStartDate = str_replace('-', '/', $circleStartDate);
                                        $circleStartDate = date('d/m/Y', strtotime($circleStartDate));

                                        $circleEndDate = explode(' ', $circlesResult[$i]['EndDate'])[0];
                                        $circleEndDate = str_replace('-', '/', $circleEndDate);
                                        $circleEndDate = date('d/m/Y', strtotime($circleEndDate));

                                        $page .=
                                        '<div class="event-data-txt"><span style="color: #000;">תאריך התחלה</span>:&nbsp;'.$circleStartDate.'</div>
                                        <div class="event-data-txt"><span style="color: #000;">תאריך סיום</span>:&nbsp;'.$circleEndDate.'</div>';
                                        
                                        $registered = false;
                                        $currentDay = -1;
                                        foreach ($circleMeetingsResult as $cmKey => $cmValue)
                                        {
                                            $hDay = 'יום ראשון';
                                            if ($cmValue['WeekDay'] == 1) { $hDay = 'יום שני'; }
                                            if ($cmValue['WeekDay'] == 2) { $hDay = 'יום שלישי'; }
                                            if ($cmValue['WeekDay'] == 3) { $hDay = 'יום רביעי'; }
                                            if ($cmValue['WeekDay'] == 4) { $hDay = 'יום חמישי'; }
                                            if ($cmValue['WeekDay'] == 5) { $hDay = 'יום שישי'; }
                                            if ($cmValue['WeekDay'] == 6) { $hDay = 'יום שבת'; }

                                            $meetingDate = explode(' ', $cmValue['StartDate'])[0];
                                            $meetingDate = str_replace('-', '/', $meetingDate);
                                            $meetingDate = date('d/m/Y', strtotime($meetingDate));

                                            if ($currentDay != $cmValue['WeekDay'])
                                            {
                                                $page .= '<div class="event-data-txt" style="text-align: center;"><span style="color: #000;">'.$hDay.' - '.$meetingDate.'</span></div>';
                                                $currentDay = $cmValue['WeekDay'];
                                            }
                                            
                                            $startHour = explode(' ', $cmValue['StartDate'])[1];
                                            $endHour = explode(' ', $cmValue['EndDate'])[1];

                                            $page .= '<div class="event-data-txt" style="text-align: center;">מ: '.$startHour.' עד: '.$endHour.'</div>';
                                        }
                                        $page .=                                        
                                    '</div>
                                </div>';                                
                            }
                            $page .=
                            '</div>
                        </div>
                        <div class="events-w">
                            <div class="event-title">אירועים</div>
                            <div class="events-data-w">';
                            for ($i = 0; $i < count($eventsResult); $i++)
                            {
                                $startDate = explode(' ', $eventsResult[$i]['StartDate'])[0];
                                $unixTimestamp = strtotime($startDate);
                                $startDayWeek = date("l", $unixTimestamp);

                                $endDate = explode(' ', $eventsResult[$i]['EndDate'])[0];
                                $unixTimestamp = strtotime($endDate);
                                $endDayWeek = date("l", $unixTimestamp);

                                $startDay = UTILITIES::GetHebrewDay($startDayWeek);
                                $endDay = UTILITIES::GetHebrewDay($endDayWeek); 
                                
                                $startDate = explode(' ', $eventsResult[$i]['StartDate'])[0];
                                $startDate = str_replace('-', '/', $startDate);
                                $startDate = date('d/m/Y', strtotime($startDate));

                                $endDate = explode(' ', $eventsResult[$i]['EndDate'])[0];
                                $endDate = str_replace('-', '/', $endDate);
                                $endDate = date('d/m/Y', strtotime($endDate));

                                $page .=
                                '<div class="outer-event-w">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">'.htmlentities($eventsResult[$i]['EventName']).'</div>
                                        <div class="event-data-txt" style="margin-top: 15px;"><span style="color: #000;">רקע</span>:&nbsp;'.htmlentities($eventsResult[$i]['EventDescription']).'</div>
                                        <div class="event-data-txt"><span style="color: #000;">מדריך</span>:&nbsp;'.htmlentities($eventInstructors[$i]).'</div>
                                        <div class="event-data-txt" style="text-align: center;"><span style="color: #000;">התחלה</span></div>
                                        <div class="event-data-txt" style="text-align: center;">'.$startDay.' - '.$startDate.' - מ: '.explode(' ', $eventsResult[$i]['StartDate'])[1].'</div>
                                        <div class="event-data-txt" style="text-align: center;"><span style="color: #000;">סיום</span></div>
                                        <div class="event-data-txt" style="text-align: center;">'.$endDay.' - '.$endDate.' - עד: '.explode(' ', $eventsResult[$i]['EndDate'])[1].'</div>
                                    </div>
                                </div>';
                            }
                            $page .=
                            '</div>
                        </div>';
                        /*<div class="events-w">
                            <div class="event-title">אודותינו</div>
                            <div class="events-data-w">
                                <div class="outer-event-w" style="border-bottom: 0;">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">קיץ 2021</div>
                                        <img class="event-image" src="images/1.jpg"/>
                                        <img class="event-image" src="images/2.jpg"/>
                                        <img class="event-image" src="images/3.jpg"/>
                                    </div>
                                </div>
                            </div>
                        </div>*/
                        $page .=
                    '</div>                
                </div>';
            }
            else if ($_SESSION['UserType'] == 'Manager')
            {
                // SELECT ALL THE MESSAGES
                $pdo = UTILITIES::PDO_DB_Connection('school_messages');
                $stmt = $pdo->prepare("SELECT * FROM messages_table WHERE BINARY Active=? AND ToSpecificInstructor=? AND ToSpecificStudent=? AND FromInstructor=? ORDER BY CreationDate DESC");                    
                $stmt->execute(['YES', -1, -1, -1]); 
                $messagesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $pdo = null; 

                $page =
                '<div class="header">
                    <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                    <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
                </div>
                <div>
                    <div class="user-profile-menu-w">                        
                        <div id="managerStudentsList" class="user-profile-menu-item"><a>רשימת תלמידים</a></div>
                        <div id="managerInstructorsList" class="user-profile-menu-item"><a>רשימת מדריכים</a></div>
                        <div class="user-profile-menu-item"><a href="circle.php">ניהול חוגים</a></div>
                        <div class="user-profile-menu-item"><a href="attendance.php">נוכחות חוגים</a></div>
                        <div class="user-profile-menu-item"><a href="event.php">ניהול אירועים</a></div>
                        <div id="manageManagerMessages" class="user-profile-menu-item"><a>ניהול הודעות</a></div>
                        <div class="user-profile-menu-item"><a href="budget.php">ניהול תקציב / דוחות</a></div>
                        <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                        <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                    </div>
                </div>
                <div class="body-wrapper">
                    <div class="title">פנימיית כפר הנוער - דף הבית</div>
                    <div class="operations-w">
                        <div id="addStudent" class="operation-btn animated-transition noselect" style="margin-right: 70px;"><a>הוסף/י תלמיד</a></div>
                        <div id="addInstructor" class="operation-btn animated-transition noselect"><a>הוסף/י מדריך</a></div>
                        <div id="createEvent" class="operation-btn animated-transition noselect"><a>צור/י אירוע</a></div>
                        <div id="createCircle" class="operation-btn animated-transition noselect"><a>צור/י חוג</a></div>
                        <div class="operation-btn animated-transition noselect"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/createCircle.php">שבץ/י חוג</a></div>
                        <div id="sendManagerMessage" class="operation-btn animated-transition noselect"><a>צור/י הודעה</a></div>
                    </div>
                    <div class="messages-title">הודעות</div>
                    <div class="messages-w">
                        <div class="messages-cont">';
                        $allMsgsRead = true;   
                        $comma = '';
                        foreach ($messagesResult as $index => $row)
                        {
                            if (empty($row['ReadByManagers']) || strpos($row['ReadByManagers'], '|'.$_SESSION['ManagerId'].'|') === false)
                            {                                    
                                $creationDate1 = substr($row['CreationDate'], 0, strpos($row['CreationDate'], ' '));
                                $creationDate1 = str_replace('-', '/', $creationDate1);
                                $creationDate1 = date('d/m/Y', strtotime($creationDate1));
                                $creationDate2 = substr($row['CreationDate'], strpos($row['CreationDate'], ' ') + 1);

                                $top = $allMsgsRead === true ? ' style="margin-top: 0;"' : '';
                                $page .=
                                '<div msgid="'.$row['MessageId'].'" class="message-w"'.$top.'>
                                    <div class="message-text">'.htmlentities($row['MessageText']).'</div>
                                    <div class="message-date">'.$creationDate1.'&nbsp;'.$creationDate2.'</div>
                                    <div class="read-msg-w noselect animated-transition">סמן הודעה זו כנקראה</div>
                                </div>';
                                $jsMessagesIds .= $comma.'"'.$row['MessageId'].'"';
                                $allMsgsRead = false;
                                $comma = ',';
                            }                               
                        }
                        if ($allMsgsRead === true) 
                        {
                            $page .= 
                            '<div class="message-w" style="margin-top: 0;">
                                <div class="message-text">הנך מעודכן/ת, לא נמצאו עבורך הודעות חדשות כרגע</div>
                            </div>';
                        }
                        $page .=
                        '</div>
                    </div>';

                    $jsMessagesIds .= 
                    '];
                    var userId = '.$_SESSION['ManagerId'].';
                    var userType = "Manager";
                    var instructors = [';

                    // SELECT ALL THE INSTRUCTORS
                    $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                    $stmt = $pdo->prepare("SELECT InstructorId,FirstName,LastName,IdNumber FROM instructors");                    
                    $stmt->execute(); 
                    $instructorsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null; 
                    
                    foreach ($instructorsResult as $key => $row)
                    {
                        $jsMessagesIds .= '["'.$row['FirstName'].' '.$row['LastName'].' - '.$row['IdNumber'].'", "'.$row['InstructorId'].'"]';
                        if ($key < count($instructorsResult) - 1) {
                            $jsMessagesIds .= ',';
                        }
                    }

                    $jsMessagesIds .= '];
                    </script>';

                    $page .=
                    '<div class="events-container">
                        <div class="events-w">
                            <div class="event-title">חוגים</div>
                            <div class="events-data-w">';
                            for ($i = 0; $i < count($circlesResult); $i++)
                            {
                                // SELECT CIRCLE MEETINGS
                                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                                $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
                                $stmt->execute([$circlesResult[$i]['CircleId']]); 
                                $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $pdo = null; 

                                $lastRegistrationDate1 = substr($circlesResult[$i]['StartDate'], 0, strpos($circlesResult[$i]['StartDate'], ' '));
                                $lastRegistrationDate1 = str_replace('-', '/', $lastRegistrationDate1);
                                $lastRegistrationDate1 = date('d/m/Y', strtotime($lastRegistrationDate1));
                                $lastRegistrationDate2 = substr($circlesResult[$i]['StartDate'], strpos($circlesResult[$i]['StartDate'], ' ') + 1);
                                $page .=
                                '<div class="outer-event-w">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">'.htmlentities($circlesResult[$i]['CircleName']).'</div>
                                        <div class="event-data-txt" style="margin-top: 15px;"><span style="color: #000;">רקע</span>:&nbsp;'.htmlentities($circlesResult[$i]['CircleDescription']).'</div>
                                        <div class="event-data-txt"><span style="color: #000;">מדריך</span>:&nbsp;'.htmlentities($circleInstructors[$i]).'</div>';
                                        if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($circlesResult[$i]['StartDate'])) {
                                            $page .= '<div class="event-data-txt" style="color: #c41818;"><span style="color: #000;">תאריך אחרון להרשמה</span>:&nbsp;'.$lastRegistrationDate1.'&nbsp;'.$lastRegistrationDate2.'</div>';
                                        }
                                        else {
                                            $page .= '<div class="event-data-txt"><span style="color: #000;">תאריך אחרון להרשמה</span>:&nbsp;'.$lastRegistrationDate1.'&nbsp;'.$lastRegistrationDate2.'</div>';
                                        }                                        
                                        
                                        $circleStartDate = explode(' ', $circlesResult[$i]['StartDate'])[0];
                                        $circleStartDate = str_replace('-', '/', $circleStartDate);
                                        $circleStartDate = date('d/m/Y', strtotime($circleStartDate));

                                        $circleEndDate = explode(' ', $circlesResult[$i]['EndDate'])[0];
                                        $circleEndDate = str_replace('-', '/', $circleEndDate);
                                        $circleEndDate = date('d/m/Y', strtotime($circleEndDate));

                                        $page .=
                                        '<div class="event-data-txt"><span style="color: #000;">תאריך התחלה</span>:&nbsp;'.$circleStartDate.'</div>
                                        <div class="event-data-txt"><span style="color: #000;">תאריך סיום</span>:&nbsp;'.$circleEndDate.'</div>';

                                        $currentDay = -1;
                                        foreach ($circleMeetingsResult as $cmKey => $cmValue)
                                        {
                                            $hDay = 'יום ראשון';
                                            if ($cmValue['WeekDay'] == 1) { $hDay = 'יום שני'; }
                                            if ($cmValue['WeekDay'] == 2) { $hDay = 'יום שלישי'; }
                                            if ($cmValue['WeekDay'] == 3) { $hDay = 'יום רביעי'; }
                                            if ($cmValue['WeekDay'] == 4) { $hDay = 'יום חמישי'; }
                                            if ($cmValue['WeekDay'] == 5) { $hDay = 'יום שישי'; }
                                            if ($cmValue['WeekDay'] == 6) { $hDay = 'יום שבת'; }

                                            $meetingDate = explode(' ', $cmValue['StartDate'])[0];
                                            $meetingDate = str_replace('-', '/', $meetingDate);
                                            $meetingDate = date('d/m/Y', strtotime($meetingDate));

                                            if ($currentDay != $cmValue['WeekDay'])
                                            {
                                                $page .= '<div class="event-data-txt" style="text-align: center;"><span style="color: #000;">'.$hDay.' - '.$meetingDate.'</span></div>';
                                                $currentDay = $cmValue['WeekDay'];
                                            }
                                            
                                            $startHour = explode(' ', $cmValue['StartDate'])[1];
                                            $endHour = explode(' ', $cmValue['EndDate'])[1];

                                            $page .= '<div class="event-data-txt" style="text-align: center;">מ: '.$startHour.' עד: '.$endHour.'</div>';
                                        }                                        
                                        $page .= 
                                        '<div class="change-circle-schedule animated-transition noselect">
                                            <a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/editCircle.php?id='.$circlesResult[$i]['CircleId'].'">שנה/י מערכת</a>
                                        </div>';
                                        if (strtotime($currentDateTime) < strtotime($circlesResult[$i]['StartDate']))
                                        {
                                            $page .=
                                            '<div removeCircle="'.$circlesResult[$i]['CircleId'].'" class="change-circle-schedule animated-transition noselect" style="margin-left: 15px;">
                                                <a>הסר/י חוג</a>
                                            </div>';
                                        }  
                                        $page .=                                      
                                    '</div>
                                </div>';
                            }
                            $page .=
                            '</div>
                        </div>
                        <div class="events-w">
                            <div class="event-title">אירועים</div>
                            <div class="events-data-w">';
                            for ($i = 0; $i < count($eventsResult); $i++)
                            {
                                $startDate = explode(' ', $eventsResult[$i]['StartDate'])[0];
                                $unixTimestamp = strtotime($startDate);
                                $startDayWeek = date("l", $unixTimestamp);

                                $endDate = explode(' ', $eventsResult[$i]['EndDate'])[0];
                                $unixTimestamp = strtotime($endDate);
                                $endDayWeek = date("l", $unixTimestamp);

                                $startDay = UTILITIES::GetHebrewDay($startDayWeek);
                                $endDay = UTILITIES::GetHebrewDay($endDayWeek); 
                                
                                $startDate = explode(' ', $eventsResult[$i]['StartDate'])[0];
                                $startDate = str_replace('-', '/', $startDate);
                                $startDate = date('d/m/Y', strtotime($startDate));

                                $endDate = explode(' ', $eventsResult[$i]['EndDate'])[0];
                                $endDate = str_replace('-', '/', $endDate);
                                $endDate = date('d/m/Y', strtotime($endDate));
                                
                                $page .=
                                '<div class="outer-event-w">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">'.htmlentities($eventsResult[$i]['EventName']).'</div>
                                        <div class="event-data-txt" style="margin-top: 15px;"><span style="color: #000;">רקע</span>:&nbsp;'.htmlentities($eventsResult[$i]['EventDescription']).'</div>
                                        <div class="event-data-txt"><span style="color: #000;">מדריך</span>:&nbsp;'.htmlentities($eventInstructors[$i]).'</div>
                                        <div class="event-data-txt" style="text-align: center;"><span style="color: #000;">התחלה</span></div>
                                        <div class="event-data-txt" style="text-align: center;">'.$startDay.' - '.$startDate.' - מ: '.explode(' ', $eventsResult[$i]['StartDate'])[1].'</div>
                                        <div class="event-data-txt" style="text-align: center;"><span style="color: #000;">סיום</span></div>
                                        <div class="event-data-txt" style="text-align: center;">'.$endDay.' - '.$endDate.' - עד: '.explode(' ', $eventsResult[$i]['EndDate'])[1].'</div>';
                                        if (strtotime($currentDateTime) < strtotime($eventsResult[$i]['StartDate']))
                                        {
                                            $page .=
                                            '<div editEvent="'.$eventsResult[$i]['EventId'].'" class="change-circle-schedule animated-transition noselect">
                                                <a>ערוך/י אירוע</a>
                                            </div>
                                            <div removeEvent="'.$eventsResult[$i]['EventId'].'" class="change-circle-schedule animated-transition noselect" style="margin-left: 15px;">
                                                <a>הסר/י אירוע</a>
                                            </div>';
                                        }                                        
                                        $page .=
                                    '</div>
                                </div>';
                            }
                            $page .=
                            '</div>
                        </div>';
                        /*<div class="events-w">
                            <div class="event-title">אודותינו</div>
                            <div class="events-data-w">
                                <div class="outer-event-w" style="border-bottom: 0;">
                                    <div class="inner-event-w">
                                        <div class="spec-event-title">קיץ 2021</div>
                                        <img class="event-image" src="images/1.jpg"/>
                                        <img class="event-image" src="images/2.jpg"/>
                                        <img class="event-image" src="images/3.jpg"/>
                                    </div>
                                </div>
                            </div>
                        </div>*/
                        $page .=
                    '</div>                
                </div>';
            }
            $page .=
            '<div class="footer">
                
            </div>'
            .$jsMessagesIds.
            self::ProfileScreenJs();
            return $page;
        }

        // RETURNS THE HOME PAGE FOR NOT LOGGED IN USER
        public static function NotLoggedInHomePage() : string 
        {     
            $day = date('w');
            $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
            $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
            $currentDateTime = date("Y-m-d H:i:s");
            $startDay = date("Y-m-d").' 00:00:00';

            // SELECT ALL THE CIRCLES THAT ARE THIS WEEK
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY EndDate>=? ORDER BY StartDate ASC");
            $stmt->execute([$currentDateTime]); 
            $circlesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            // SELECT CIRCLE INSTRUCTORS NAME
            $circleInstructors = [];                
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            foreach ($circlesResult as $key => $value)
            {
                $stmt = $pdo->prepare("SELECT FirstName,LastName FROM instructors WHERE BINARY InstructorId=?");                    
                $stmt->execute([$value['CircleInstructorId']]);
                $circleInstructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!$circleInstructorResult || count($circleInstructorResult) == 0) {
                    array_push($circleInstructors, 'המדריך לא רשום במערכת');
                }
                else {
                    array_push($circleInstructors, $circleInstructorResult[0]['FirstName'].' '.$circleInstructorResult[0]['LastName']);
                }
            }
            $pdo = null;

            // SELECT ALL THE EVENTS THAT ARE THIS WEEK
            $pdo = UTILITIES::PDO_DB_Connection('school_events');
            $stmt = $pdo->prepare("SELECT * FROM events_table WHERE BINARY EndDate>=? ORDER BY StartDate ASC");                    
            $stmt->execute([$currentDateTime]); 
            $eventsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            // SELECT EVENT INSTRUCTORS NAME
            $eventInstructors = [];                
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            foreach ($eventsResult as $key => $value)
            {
                if ($value['NeedInstructor'] == 'YES')
                {
                    $stmt = $pdo->prepare("SELECT FirstName,LastName FROM instructors WHERE BINARY InstructorId=?");                    
                    $stmt->execute([$value['EventInstructorId']]);
                    $eventInstructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!$eventInstructorResult || count($eventInstructorResult) == 0) {
                        array_push($eventInstructors, 'המדריך לא רשום במערכת');
                    }
                    else {
                        array_push($eventInstructors, $eventInstructorResult[0]['FirstName'].' '.$eventInstructorResult[0]['LastName']);
                    }
                }                    
                else {
                    array_push($eventInstructors, 'ללא מדריך');
                }
                
            }
            $pdo = null;
            
            $page =
            '<div class="header">
                <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                <div class="header-login noselect animated-transition"><a class="animated-transition" href="login.php">כניסה</a></div>                
            </div>                
            <div class="body-wrapper">
                <div class="title">פנימיית כפר הנוער</div>
                <div class="events-container">
                    <div class="events-w">
                        <div class="event-title">חוגים</div>
                        <div class="events-data-w">';
                        for ($i = 0; $i < count($circlesResult); $i++)
                        {
                            // SELECT CIRCLE MEETINGS
                            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                            $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
                            $stmt->execute([$circlesResult[$i]['CircleId']]); 
                            $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null; 

                            $lastRegistrationDate1 = substr($circlesResult[$i]['StartDate'], 0, strpos($circlesResult[$i]['StartDate'], ' '));
                            $lastRegistrationDate1 = str_replace('-', '/', $lastRegistrationDate1);
                            $lastRegistrationDate1 = date('d/m/Y', strtotime($lastRegistrationDate1));
                            $lastRegistrationDate2 = substr($circlesResult[$i]['StartDate'], strpos($circlesResult[$i]['StartDate'], ' ') + 1);

                            $page .=
                            '<div class="outer-event-w">
                                <div class="inner-event-w">
                                    <div class="spec-event-title">'.htmlentities($circlesResult[$i]['CircleName']).'</div>
                                    <div class="event-data-txt" style="margin-top: 15px;"><span style="color: #000;">רקע</span>:&nbsp;'.htmlentities($circlesResult[$i]['CircleDescription']).'</div>
                                    <div class="event-data-txt"><span style="color: #000;">מדריך</span>:&nbsp;'.htmlentities($circleInstructors[$i]).'</div>';
                                    if (strtotime((new DateTime())->format("Y-m-d H:i:s")) > strtotime($circlesResult[$i]['StartDate'])) {
                                        $page .= '<div class="event-data-txt" style="color: #c41818;"><span style="color: #000;">תאריך אחרון להרשמה</span>:&nbsp;'.$lastRegistrationDate1.'&nbsp;'.$lastRegistrationDate2.'</div>';
                                    }
                                    else { 
                                        $page .= '<div class="event-data-txt"><span style="color: #000;">תאריך אחרון להרשמה</span>:&nbsp;'.$lastRegistrationDate1.'&nbsp;'.$lastRegistrationDate2.'</div>';
                                    }

                                    $circleStartDate = explode(' ', $circlesResult[$i]['StartDate'])[0];
                                    $circleStartDate = str_replace('-', '/', $circleStartDate);
                                    $circleStartDate = date('d/m/Y', strtotime($circleStartDate));

                                    $circleEndDate = explode(' ', $circlesResult[$i]['EndDate'])[0];
                                    $circleEndDate = str_replace('-', '/', $circleEndDate);
                                    $circleEndDate = date('d/m/Y', strtotime($circleEndDate));

                                    $page .=
                                    '<div class="event-data-txt"><span style="color: #000;">תאריך התחלה</span>:&nbsp;'.$circleStartDate.'</div>
                                    <div class="event-data-txt"><span style="color: #000;">תאריך סיום</span>:&nbsp;'.$circleEndDate.'</div>';                      
                                    
                                    $registered = false;
                                    $currentDay = -1;
                                    foreach ($circleMeetingsResult as $cmKey => $cmValue)
                                    {
                                        $hDay = 'יום ראשון';
                                        if ($cmValue['WeekDay'] == 1) { $hDay = 'יום שני'; }
                                        if ($cmValue['WeekDay'] == 2) { $hDay = 'יום שלישי'; }
                                        if ($cmValue['WeekDay'] == 3) { $hDay = 'יום רביעי'; }
                                        if ($cmValue['WeekDay'] == 4) { $hDay = 'יום חמישי'; }
                                        if ($cmValue['WeekDay'] == 5) { $hDay = 'יום שישי'; }
                                        if ($cmValue['WeekDay'] == 6) { $hDay = 'יום שבת'; }

                                        $meetingDate = explode(' ', $cmValue['StartDate'])[0];
                                        $meetingDate = str_replace('-', '/', $meetingDate);
                                        $meetingDate = date('d/m/Y', strtotime($meetingDate));

                                        if ($currentDay != $cmValue['WeekDay'])
                                        {
                                            $page .= '<div class="event-data-txt" style="text-align: center;"><span style="color: #000;">'.$hDay.' - '.$meetingDate.'</span></div>';
                                            $currentDay = $cmValue['WeekDay'];
                                        }
                                        
                                        $startHour = explode(' ', $cmValue['StartDate'])[1];
                                        $endHour = explode(' ', $cmValue['EndDate'])[1];

                                        $page .= '<div class="event-data-txt" style="text-align: center;">מ: '.$startHour.' עד: '.$endHour.'</div>';
                                    }
                                    $page .=                                        
                                '</div>
                            </div>';                            
                        }
                        $page .=
                        '</div>
                    </div>
                    <div class="events-w">
                        <div class="event-title">אירועים</div>
                        <div class="events-data-w">';
                        for ($i = 0; $i < count($eventsResult); $i++)
                        { 
                            $startDate = explode(' ', $eventsResult[$i]['StartDate'])[0];
                            $unixTimestamp = strtotime($startDate);
                            $startDayWeek = date("l", $unixTimestamp);

                            $endDate = explode(' ', $eventsResult[$i]['EndDate'])[0];
                            $unixTimestamp = strtotime($endDate);
                            $endDayWeek = date("l", $unixTimestamp);

                            $startDay = UTILITIES::GetHebrewDay($startDayWeek);
                            $endDay = UTILITIES::GetHebrewDay($endDayWeek); 
                            
                            $startDate = explode(' ', $eventsResult[$i]['StartDate'])[0];
                            $startDate = str_replace('-', '/', $startDate);
                            $startDate = date('d/m/Y', strtotime($startDate));

                            $endDate = explode(' ', $eventsResult[$i]['EndDate'])[0];
                            $endDate = str_replace('-', '/', $endDate);
                            $endDate = date('d/m/Y', strtotime($endDate));

                            $page .=
                            '<div class="outer-event-w">
                                <div class="inner-event-w">
                                    <div class="spec-event-title">'.htmlentities($eventsResult[$i]['EventName']).'</div>
                                    <div class="event-data-txt" style="margin-top: 15px;"><span style="color: #000;">רקע</span>:&nbsp;'.htmlentities($eventsResult[$i]['EventDescription']).'</div>
                                    <div class="event-data-txt"><span style="color: #000;">מדריך</span>:&nbsp;'.htmlentities($eventInstructors[$i]).'</div>
                                    <div class="event-data-txt" style="text-align: center;"><span style="color: #000;">התחלה</span></div>
                                    <div class="event-data-txt" style="text-align: center;">'.$startDay.' - '.$startDate.' - מ: '.explode(' ', $eventsResult[$i]['StartDate'])[1].'</div>
                                    <div class="event-data-txt" style="text-align: center;"><span style="color: #000;">סיום</span></div>
                                    <div class="event-data-txt" style="text-align: center;">'.$endDay.' - '.$endDate.' - עד: '.explode(' ', $eventsResult[$i]['EndDate'])[1].'</div>
                                </div>
                            </div>';
                        }
                        $page .=
                        '</div>
                    </div>';
                    /*<div class="events-w">
                        <div class="event-title">אודותינו</div>
                        <div class="events-data-w">
                            <div class="outer-event-w" style="border-bottom: 0;">
                                <div class="inner-event-w">
                                    <div class="spec-event-title">קיץ 2021</div>
                                    <img class="event-image" src="images/1.jpg"/>
                                    <img class="event-image" src="images/2.jpg"/>
                                    <img class="event-image" src="images/3.jpg"/>
                                </div>
                            </div>
                        </div>
                    </div>*/
                    $page .=
                '</div>                
            </div>
            <div class="footer">
            
            </div>';
            return $page;
        }

        // RETURNS THE HTML DOCUMENT FOR CREATING A NEW CIRCLE FOR THE FOLLOWING WEEK
        public static function CreateCirclePage() : string 
        {
            $day = date('w');
            $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
            $unixWeekStart = date('Y-m-d', strtotime('-'.$day.' days')).' 00:00:00';
            $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
            $currentDateTime = date("d/m/Y H:i:s");

            // SELECT THE AVAILABLE CIRCLES FROM THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM permanent_circles");
            $stmt->execute(); 
            $permanentCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            if (count($permanentCirclesResult) == 0)
            {
                echo '<script type="text/javascript">
                    alert("לא קיימת עדיין רשימת חוגים במערכת");
                    location.href = "http://'.$GLOBALS['SERVER_ADDRESS'].'/index";
                </script>';
                exit;
            }

            // SELECT THE AVAILABLE INSTRUCTORS FOR THIS CIRCLE FROM THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM instructors");
            $stmt->execute(); 
            $instructorsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            if (count($instructorsResult) == 0)
            {
                echo '<script type="text/javascript">
                    alert("לא קיימים מדריכים במערכת");
                    location.href = "http://'.$GLOBALS['SERVER_ADDRESS'].'/index";
                </script>';
                exit;
            }

            $page =
            '<script>
                var circleDescriptions = ['; 
                foreach ($permanentCirclesResult as $row) 
                {
                    // CHECK IF THIS CIRCLE IS ALREADY SCHEDULED IN THIS WEEK
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT CircleId FROM circles_table WHERE BINARY CircleName=? AND StartDate>=?");
                    $stmt->execute([$row['PermanentCircleName'], $unixWeekStart]); 
                    $pcResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null; 

                    if ($pcResult || count($pcResult) > 0) {
                        continue;
                    }

                    $page .= '"'.htmlentities($row['PermanentCircleDescription']).'",';
                }
                $page = substr($page, 0, strlen($page) - 1);
                $page .= '];
            </script>';

            $page .=
            '<div class="header">
                <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
            </div>
            <div>
                <div class="user-profile-menu-w">                        
                    <div id="managerStudentsList" class="user-profile-menu-item"><a>רשימת תלמידים</a></div>
                    <div id="managerInstructorsList" class="user-profile-menu-item"><a>רשימת מדריכים</a></div>
                    <div class="user-profile-menu-item"><a href="circle.php">ניהול חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="attendance.php">נוכחות חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="event.php">ניהול אירועים</a></div>
                    <div id="manageManagerMessages" class="user-profile-menu-item"><a>ניהול הודעות</a></div>
                    <div class="user-profile-menu-item"><a href="budget.php">ניהול תקציב / דוחות</a></div>
                    <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                    <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                </div>
            </div>
            <div class="body-wrapper">
                <div class="title">שיבוץ חוג לשבוע הקרוב</div>
                <div class="circle-fields-w">
                    <div class="inner-circle-fields-w">
                        <div class="circle-field-w" style="margin-top: 0;">
                            <div class="circle-field-txt">שם החוג</div>
                            <select id="circleName" class="circle-field-value">';
                            $circles = 0;
                            foreach ($permanentCirclesResult as $row) 
                            {
                                // CHECK IF THIS CIRCLE IS ALREADY SCHEDULED IN THIS WEEK
                                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                                $stmt = $pdo->prepare("SELECT CircleId FROM circles_table WHERE BINARY CircleName=? AND StartDate>=?");
                                $stmt->execute([$row['PermanentCircleName'], $unixWeekStart]); 
                                $pcResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $pdo = null; 

                                if ($pcResult || count($pcResult) > 0) {
                                    continue;
                                }                                
                                $page .= '<option value="'.htmlentities($row['PermanentCircleName']).'">'.htmlentities($row['PermanentCircleName']).'</option>';
                                $circles++;
                            }

                            if ($circles == 0)
                            {
                                echo '<script type="text/javascript">
                                    alert("לא ניתן לשבץ חוגים נוספים לשבוע זה");
                                    location.href = "http://'.$GLOBALS['SERVER_ADDRESS'].'/index";
                                </script>';
                                exit;
                            }

                            $page .=
                            '</select>
                        </div>
                        <div class="circle-field-w">
                            <div class="circle-field-txt">שם המדריך</div>
                            <select id="circleInstructorName" class="circle-field-value">';
                            foreach ($instructorsResult as $row) {
                                $page .= '<option value="'.htmlentities($row['FirstName'].' '.$row['LastName'].' - '.$row['IdNumber']).'">'.htmlentities($row['FirstName'].' '.$row['LastName'].' - '.$row['IdNumber']).'</option>';
                            }
                            $page .=
                            '</select>
                        </div>
                        <div class="circle-field-name-w">רקע ותיאור של החוג</div>
                        <textarea id="circleDescription" readonly></textarea>
                    </div>
                </div>
                <div class="circle-field-name-w" style="width: 720px; margin-top: 0;">קביעת מפגשים לחוג</div>
                <div class="outer-circle-schedule-w">
                    <div class="schedule-item">
                        <div class="inner-schedule-item" style="width: 199px;"></div>
                        <div class="inner-schedule-item" style="width: 299px;"><div class="sch-title">מפגשים</div></div>
                        <div class="inner-schedule-item" style="border-right: none; width: 220px;""><div class="sch-title">ימים</div></div>
                    </div>';                     
                    for ($i = 0; $i < 7; $i++)
                    {
                        $offset = $i - $day;
                        $iThDate = date('d/m/Y', strtotime('+'.$offset.' days'));
                        if ($offset < 0) { $iThDate = date('d/m/Y', strtotime($offset.' days')); }

                        $border = $i == 6 ? ' style="border-bottom: none;"' : '';

                        $hDay = 'יום ראשון';
                        if ($i == 1) { $hDay = 'יום שני'; }
                        if ($i == 2) { $hDay = 'יום שלישי'; }
                        if ($i == 3) { $hDay = 'יום רביעי'; }
                        if ($i == 4) { $hDay = 'יום חמישי'; }
                        if ($i == 5) { $hDay = 'יום שישי'; }
                        if ($i == 6) { $hDay = 'יום שבת'; }

                        $page .=
                        '<div day="'.$i.'" class="schedule-item"'.$border.'>
                            <div class="inner-schedule-item" style="width: 199px;">';
                            if ($i < $day) {
                                $page .= '<div class="disabled-sch-button noselect"><a>הוספת&nbsp;מפגש</a></div>';
                            } 
                            else {
                                $page .= '<div class="animated-transition sch-button noselect"><a>הוספת&nbsp;מפגש</a></div>';
                            }
                            $page .=
                            '</div>
                            <div class="inner-schedule-item" style="width: 299px;">
                                <div class="inner-meeting-w">';
                                if ($i < $day) {
                                    $page .= '<div class="inner-meeting-text">לא ניתן לשבץ מפשגים ליום זה</div>';
                                }
                                else {
                                    $page .= '<div class="inner-meeting-text">מפגשים שישובצו ליום זה יופיעו כאן</div>';
                                }
                                $page .=
                                '</div>
                            </div>
                            <div class="inner-schedule-item" style="border-right: none; width: 220px;">
                                <div class="inner-meeting-w">
                                    <div class="inner-meeting-text">'.$hDay.'&nbsp;-&nbsp;'.$iThDate.'</div>
                                </div>
                            </div>
                        </div>';
                    } 
                    $page .=
                '</div>
                <div class="selected-sch-options-w">
                    <div class="selected-opt-txt">פעולות עבור מפגשים שנבחרו</div>
                    <div class="selected-ops-w noselect">
                        <div id="deleteMeetings" class="selected-opt animated-transition" style="margin-right: 0;"><a>מחק/י</a></div>
                        <div id="changeMeeting" class="selected-opt animated-transition"><a>שנה/י</a></div>
                    </div>
                </div>';
                /*<div class="circle-fields-w">
                    <div class="inner-circle-fields-w" style="padding-bottom: 0;">
                        <div class="circle-field-name-w">תאריך&nbsp;אחרון&nbsp;להרשמה&nbsp;לחוג</div>
                        <div class="circle-field-w">
                            <input type="date" id="lastRegistrationDate" class="date-input" style="margin-right: 0; width: 355px;"/>
                            <input type="text" id="lastRegistrationTime" class="date-input" style="width: 355px;" placeholder="שעה&nbsp;(בפורמט: 00:00:00)"/>
                        </div>
                    </div>
                </div>*/
                $page .=
                '<div id="publishCircle" class="noselect animated-transition"><a>פרסמ/י חוג</a></div>
            </div>
            <div class="footer">
                
            </div>'.
            self::ProfileScreenJs();
            return $page;
        }

        // RETURNS THE HTML DOCUMENT FOR EDITING AN EXISTING CIRCLE
        public static function EditCirclePage($circleId) : string 
        {
            if (!is_numeric($circleId) || intval($circleId) < 0)
            {
                header("Location: http://$GLOBALS[SERVER_ADDRESS]/index");
                exit;
            }

            $day = date('w');
            $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
            $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
            $currentDateTime = date("d/m/Y H:i:s");
            $currentUnixDateTime = date("Y-m-d H:i:s");

            // SELECT THE AVAILABLE INSTRUCTORS FOR THIS CIRCLE FROM THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM instructors");
            $stmt->execute(); 
            $instructorsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            // SELECT THE SPECIFIC CIRCLE TO EDIT
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY CircleId=?");
            $stmt->execute([$circleId]); 
            $circleResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            if (!$circleResult || count($circleResult) == 0)
            {
                echo '<script tyippe="text/javascript">
                    alert("החוג המבוקש כבר לא קיים במערכת.");
                    location.href = "http://'.$GLOBALS['SERVER_ADDRESS'].'/index";
                </script>';
                exit;
            }

            // CHECK IF THIS CIRCLE IS ENDED ALREADY
            if (strtotime(date('Y-m-d H:i:s')) > strtotime($circleResult[0]['EndDate']))
            {
                echo '<script tyippe="text/javascript">
                    alert("מערכת החוג עבור שבוע זה הסתיימה, נא לחכות עד לשבוע הבא כדי לשבץ את החוג מחדש.");
                    location.href = "http://'.$GLOBALS['SERVER_ADDRESS'].'/index";
                </script>';
                exit;
            }

            // SELECT ALL THE MEETING OF THIS CIRCLE
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
            $stmt->execute([$circleId]); 
            $circleMeetingResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            // SELECT THE NAME OF THE CURRENT INSTRUCTOR OF THIS CIRCLE
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM instructors WHERE BINARY InstructorId=?");
            $stmt->execute([$circleResult[0]['CircleInstructorId']]); 
            $instructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            $page =
            '<div class="header">
                <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
            </div>
            <div>
                <div class="user-profile-menu-w">                        
                    <div id="managerStudentsList" class="user-profile-menu-item"><a>רשימת תלמידים</a></div>
                    <div id="managerInstructorsList" class="user-profile-menu-item"><a>רשימת מדריכים</a></div>
                    <div class="user-profile-menu-item"><a href="circle.php">ניהול חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="attendance.php">נוכחות חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="event.php">ניהול אירועים</a></div>
                    <div id="manageManagerMessages" class="user-profile-menu-item"><a>ניהול הודעות</a></div>
                    <div class="user-profile-menu-item"><a href="budget.php">ניהול תקציב / דוחות</a></div>
                    <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                    <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                </div>
            </div>
            <div class="body-wrapper">
                <div class="title">שינוי מערכת עבור החוג: '.$circleResult[0]['CircleName'].'</div>
                <div class="circle-fields-w">
                    <div class="inner-circle-fields-w">
                        <div class="circle-field-w" style="margin-top: 0;">
                            <div class="circle-field-txt">שם המדריך</div>
                            <select id="circleInstructorName" class="circle-field-value">
                                <option value="'.htmlentities($instructorResult[0]['FirstName'].' '.$instructorResult[0]['LastName'].' - '.$instructorResult[0]['IdNumber']).'">'.htmlentities($instructorResult[0]['FirstName'].' '.$instructorResult[0]['LastName'].' - '.$instructorResult[0]['IdNumber']).'</option>';
                                foreach ($instructorsResult as $row) 
                                {
                                    if ($row['IdNumber'] == $instructorResult[0]['IdNumber']) {
                                        continue;
                                    }
                                    $page .= '<option value="'.htmlentities($row['FirstName'].' '.$row['LastName'].' - '.$row['IdNumber']).'">'.htmlentities($row['FirstName'].' '.$row['LastName'].' - '.$row['IdNumber']).'</option>';
                                }
                                $page .=
                            '</select>
                        </div>
                    </div>
                </div>
                <div class="circle-field-name-w" style="width: 720px; margin-top: 0;">מפגשים לחוג</div>
                <div class="outer-circle-schedule-w">
                    <div class="schedule-item">
                        <div class="inner-schedule-item" style="width: 199px;"></div>
                        <div class="inner-schedule-item" style="width: 299px;"><div class="sch-title">מפגשים</div></div>
                        <div class="inner-schedule-item" style="border-right: none; width: 220px;""><div class="sch-title">ימים</div></div>
                    </div>'; 

                    // ARRANGE THE MEETINGS IN A WEEKLY DAYS ORDER
                    $meetingsByDays = [[],[],[],[],[],[],[]];
                    foreach ($circleMeetingResult as $cmKey => $cmValue) {
                        array_push($meetingsByDays[$cmValue['WeekDay']], [$cmValue['StartDate'], $cmValue['EndDate'], $cmValue['MeetingId']]);
                    }

                    for ($i = 0; $i < 7; $i++)
                    {
                        $hDay = 'יום ראשון';
                        if ($i == 1) { $hDay = 'יום שני'; }
                        if ($i == 2) { $hDay = 'יום שלישי'; }
                        if ($i == 3) { $hDay = 'יום רביעי'; }
                        if ($i == 4) { $hDay = 'יום חמישי'; }
                        if ($i == 5) { $hDay = 'יום שישי'; }
                        if ($i == 6) { $hDay = 'יום שבת'; }

                        $offset = $i - $day;
                        $iThDate = date('d/m/Y', strtotime('+'.$offset.' days'));
                        if ($offset < 0) { $iThDate = date('d/m/Y', strtotime($offset.' days')); }
                        $unixIthDate = str_replace('/', '-', $iThDate);
                        $unixIthDate = date('Y-m-d', strtotime($unixIthDate)); 

                        $border = $i == 6 ? ' style="border-bottom: none;"' : '';

                        $page .=
                        '<div day="'.$i.'" class="schedule-item"'.$border.'>
                            <div class="inner-schedule-item" style="width: 199px;">';
                            if ($i < $day) {
                                $page .= '<div class="disabled-sch-button noselect"><a>הוספת&nbsp;מפגש</a></div>';
                            } 
                            else {
                                $page .= '<div class="animated-transition sch-button noselect"><a>הוספת&nbsp;מפגש</a></div>';
                            }
                            $page .=
                            '</div>
                            <div class="inner-schedule-item" style="width: 299px;">
                                <div class="inner-meeting-w">';
                                if (count($meetingsByDays[$i]) == 0)
                                {
                                    if ($i < $day) {
                                        $page .=
                                        '<div class="inner-meeting-text">לא ניתן לשבץ מפשגים ליום זה</div>';
                                    }
                                    else {
                                        $page .=
                                        '<div class="inner-meeting-text">מפגשים שישובצו ליום זה יופעו כאן</div>';
                                    }
                                }
                                foreach ($meetingsByDays[$i] as $mKey => $mValue)
                                {
                                    $meetingPassed = false;
                                    $meetingStartHour = explode(' ', $mValue[0])[1];
                                    $meetingEndHour = explode(' ', $mValue[1])[1];
                                    if (strtotime($currentUnixDateTime) > strtotime($unixIthDate.' '.$meetingStartHour)) {
                                        $meetingPassed = true;
                                    }
                                    $top = $mKey == 0 ? ' style="margin-top: 0;"' : '';
                                    if ($i < $day || ($i == $day && $meetingPassed == true)) {
                                        $page .= '<label class="meeting-w noselect"'.$top.'>
                                        <input type="checkbox" disabled="disabled"/>מ: '.$meetingStartHour.' עד: '.$meetingEndHour.'</label>';
                                    }
                                    else {
                                        $page .= '<label class="meeting-w noselect"'.$top.'>
                                        <input meetingId="'.$mValue[2].'" type="checkbox"/>מ: '.$meetingStartHour.' עד: '.$meetingEndHour.'</label>';
                                    } 
                                }
                                $page .=
                                '</div>
                            </div>
                            <div class="inner-schedule-item" style="border-right: none; width: 220px;">
                                <div class="inner-meeting-w">
                                    <div class="inner-meeting-text">'.$hDay.'&nbsp;-&nbsp;'.$iThDate.'</div>
                                </div>
                            </div>
                        </div>';
                    } 
                $page .=
                '</div>
                <div class="selected-sch-options-w">
                    <div class="selected-opt-txt">פעולות עבור מפגשים שנבחרו</div>
                    <div class="selected-ops-w noselect">
                        <div id="deleteMeetings" class="selected-opt animated-transition" style="margin-right: 0;"><a>מחק/י</a></div>
                        <div id="changeMeeting" class="selected-opt animated-transition"><a>שנה/י</a></div>
                    </div>
                </div>';

                /*if (strtotime(date('Y-m-d H:i:s')) < strtotime($circleResult[0]['StartDate']))
                {
                    $lastRegistrationDate = explode(' ', $circleResult[0]['LastRegistrationDate'])[0];
                    $lastRegistrationHour = explode(' ', $circleResult[0]['LastRegistrationDate'])[1];
                    $page .=
                    '<div class="circle-fields-w">
                        <div class="inner-circle-fields-w" style="padding-bottom: 0;">
                            <div class="circle-field-name-w">תאריך&nbsp;אחרון&nbsp;להרשמה&nbsp;לחוג</div>
                            <div class="circle-field-w">
                                <input type="date" id="lastRegistrationDate" class="date-input" style="margin-right: 0; width: 355px;" value="'.$lastRegistrationDate.'"/>
                                <input type="text" id="lastRegistrationTime" class="date-input" style="width: 355px;" placeholder="שעה&nbsp;(בפורמט: 00:00:00)" value="'.$lastRegistrationHour.'"/>
                            </div>
                        </div>
                    </div>';
                }*/               

                $page .=
                '<div id="updateCircle" class="noselect animated-transition"><a>שמור/י שינויים</a></div>
            </div>
            <div class="footer">                
            </div>';

            // PREPARE THE JAVASCRIPT ARRAYS OF CURRENT / FINISHED MEETINGS OF THIS CIRCLE
            $page .=
            '<script type="text/javascript">
                var edCircleId = '.$circleId.';

                var finishedMeetings = [';
                $meetingCounter = 0; 
                for ($i = 0; $i < 7; $i++)
                {
                    $page .= ($i == 0) ? '[' : ', [';
                    $hadMeetings = false;
                    while ($meetingCounter < count($circleMeetingResult) && intval($circleMeetingResult[$meetingCounter]['WeekDay']) == $i)
                    {
                        if (strtotime($currentUnixDateTime) > strtotime($circleMeetingResult[$meetingCounter]['StartDate']))
                        {
                            $comma = $hadMeetings == true ? ',' : '';
                            $startHour = explode(' ', $circleMeetingResult[$meetingCounter]['StartDate'])[1];
                            $endHour = explode(' ', $circleMeetingResult[$meetingCounter]['EndDate'])[1];
                            $meetingId = $circleMeetingResult[$meetingCounter]['MeetingId'];
                            $page .= $comma.'["'.$startHour.'","'.$endHour.'","'.$meetingId.'"]';                            
                            $hadMeetings = true;
                        }                  
                        $meetingCounter++;      
                    }
                    $page .= ']';
                }
                $page .=
                '];

                var circleMeetings = [';
                $meetingCounter = 0;
                for ($i = 0; $i < 7; $i++)
                {
                    $page .= ($i == 0) ? '[' : ', [';
                    $hadMeetings = false;
                    while ($meetingCounter < count($circleMeetingResult) && intval($circleMeetingResult[$meetingCounter]['WeekDay']) == $i)
                    {                        
                        $comma = $hadMeetings == true ? ',' : '';
                        $startHour = explode(' ', $circleMeetingResult[$meetingCounter]['StartDate'])[1];
                        $endHour = explode(' ', $circleMeetingResult[$meetingCounter]['EndDate'])[1];
                        $meetingId = $circleMeetingResult[$meetingCounter]['MeetingId'];
                        $page .= $comma.'["'.$startHour.'","'.$endHour.'","'.$meetingId.'"]';
                        $meetingCounter++;
                        $hadMeetings = true;
                    }
                    $page .= ']';
                }
                $page .=
                '];
            </script>'.
            self::ProfileScreenJs();

            return $page;
        }

        // NOT COMPLETED - REUTRNS THE BUDGET MANAGEMENT PAGE
        public static function BudgetPage() : string
        {
            $day = date('w');
            $weekStart = date('d/m/Y', strtotime('-'.$day.' days')).' 00:00:00';
            $weekEnd = date('d/m/Y', strtotime('+'.(7-$day).' days')).' 00:00:00';
            $currentDateTime = date("d/m/Y H:i:s");
            $currentDate = date('d/m/Y');
            $monthStart = date('Y-m-01').' 00:00:00'; 
            $monthEndUnix = date('Y-m-t').' 23:59:59'; 
            $monthStartDate = date('Y-m-01'); 
            $monthEnd = date("t/m/Y");
            $month = date('m'); 
            $year = date("Y"); 
            $monthEndDate = date("Y-m-t");

            $fromDate = isset($_POST['updateDatesRange']) ? $_POST['fromDate'] : date('01/m/Y');
            $toDate = isset($_POST['updateDatesRange']) ? $_POST['toDate'] : date('d/m/Y');

            $fromDateUnix = str_replace('/', '-', $fromDate);
            $fromDateUnix = date('Y-m-d', strtotime($fromDateUnix));
            $toDateUnix = str_replace('/', '-', $toDate);
            $toDateUnix = date('Y-m-d', strtotime($toDateUnix));

            // SELECT ALL THE INSTRUCTORS IN THE SYSTEM AND HOW MUCH HOURS THEY DID IN THIS MONTH
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            $stmt = $pdo->prepare("SELECT InstructorId,FirstName,LastName,IdNumber FROM instructors");
            $stmt->execute(); 
            $instructorsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            // SELECT THE MONTHLY SALARIES
            $salariesEndDate = strtotime($toDateUnix.' 23:59:59') >= strtotime(date('Y-m-d H:i:s')) ? date("Y-m-t") : $toDateUnix;
            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
            $stmt = $pdo->prepare("SELECT * FROM instructors_salaries WHERE BINARY Date>=? AND Date<=? ORDER BY Date ASC");
            $stmt->execute([$fromDateUnix, $salariesEndDate]); 
            $salariesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // GET THE CURRENT BUDGET FROM THE DATABASE
            $stmt = $pdo->prepare("SELECT * FROM total_budget");
            $stmt->execute(); 
            $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalBudget = floatval($totalBudgetResult[0]['TotalSchoolBudget']);

            // SELECT THE GENERAL EXPENSES
            $stmt = $pdo->prepare("SELECT * FROM general_monthly_budget WHERE BINARY ExpenseDate>=? AND ExpenseDate<=? ORDER BY ExpenseDate ASC");
            $stmt->execute([$fromDateUnix, $toDateUnix]);
            $monthlyExpensesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            // SELECT THE PERMANENT CIRCLE NAMES
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM permanent_circles");
            $stmt->execute();
            $permanentCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            // SELECT THE TOTAL BUDGET OF THE SYSTEM
            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
            $stmt = $pdo->prepare("SELECT TotalSchoolBudget FROM total_budget");
            $stmt->execute();
            $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // SELECT ALL EVENT EXPENSES FOR THIS DATE RANGE
            $stmt = $pdo->prepare("SELECT * FROM events_budget WHERE BINARY ExpenseDate>=? ORDER BY ExpenseDate ASC");
            $stmt->execute([$monthStart]);
            $eventExpensesResults = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            $pdo = null;
            
            $sumInstructors = 0;
            $monthlySumInstructors = 0;

            $page =
            '<div class="header">
                <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
            </div>
            <div>
                <div class="user-profile-menu-w">                        
                    <div id="managerStudentsList" class="user-profile-menu-item"><a>רשימת תלמידים</a></div>
                    <div id="managerInstructorsList" class="user-profile-menu-item"><a>רשימת מדריכים</a></div>
                    <div class="user-profile-menu-item"><a href="circle.php">ניהול חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="attendance.php">נוכחות חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="event.php">ניהול אירועים</a></div>
                    <div id="manageManagerMessages" class="user-profile-menu-item"><a>ניהול הודעות</a></div>
                    <div class="user-profile-menu-item"><a href="budget.php">ניהול תקציב / דוחות</a></div>
                    <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                    <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                </div>
            </div>
            <div class="body-wrapper">
                <div class="title">ניהול תקציב</div>
                <div class="inner-body-w">

                    <div class="field-w" style="margin-top: 0;">
                        <div class="field-name">תקציב&nbsp;המערכת&nbsp;הנוכחי:</div>
                        <input type="text" readonly="true" id="currentBudget" class="field-value" value="'.$totalBudget.'"/>
                        <div class="button noselect animated-transition" id="modifyBudget" style="display: flex;"><a>שינוי&nbsp;תקציב</a></div>
                    </div>
                    
                    <form method="post">
                        <div id="datesRange" class="field-w">
                            <div class="field-name">מ:</div>
                            <input type="text" id="fromDate" name="fromDate" class="field-value" value="'.$fromDate.'"/>
                            <div class="field-name" style="margin-right: 10px;">עד:</div>
                            <input type="text" id="toDate" name="toDate" class="field-value" value="'.$toDate.'"/>
                            <button class="button noselect animated-transition" style="border: none; width: 50px; font-family: Arial; font-size: 19px;" id="updateDatesRange" name="updateDatesRange">עדכן</button>
                        </div>
                    </form>';

                    $page .=
                    '<div class="field-name" style="height: 20px; line-height: 20px; margin-top: 20px; font-weight: bold;">דו"ח הוצאות עבור הוצאות כלליות:</div>
                    
                    <div class="expenses-report-w">
                        <div class="expenses-report-row"> 
                            <div class="expenses-report-cell" style="width: 20%; border-left: none;">
                                <div class="expenses-report-inner-w" style="color: #000; font-size: 19px; font-weight: bold; text-align: center;">תאריך</div>
                            </div>
                            <div class="expenses-report-cell" style="width: 15%;">
                                <div class="expenses-report-inner-w" style="color: #000; font-size: 19px; font-weight: bold; text-align: center;">עלות</div>
                            </div>
                            <div class="expenses-report-cell" style="width: 40%;">
                                <div class="expenses-report-inner-w" style="color: #000; font-size: 19px; font-weight: bold; text-align: center;">תיאור</div>
                            </div>
                            <div class="expenses-report-cell" style="width: 25%;">
                                <div class="expenses-report-inner-w" style="color: #000; font-size: 19px; font-weight: bold; text-align: center;">הוצאה</div>
                            </div>
                        </div>';
                        
                        $sumGeneral = 0;
                        $sumGeneralMonthly = 0;
                        if (count($monthlyExpensesResult) > 0)
                        {
                            foreach ($monthlyExpensesResult as $merKey => $merValue)
                            {
                                $expenseDate = str_replace('-', '/', $merValue['ExpenseDate']);
                                $expenseDate = date('d/m/Y', strtotime($expenseDate));  

                                $page .=
                                '<div class="expenses-report-row">
                                    <div class="expenses-report-cell" style="width: 20%; border-left: none;">
                                        <div class="expenses-report-inner-w">'.$expenseDate.'</div>
                                    </div>
                                    <div class="expenses-report-cell" style="width: 15%;">
                                        <div class="expenses-report-inner-w">'.$merValue['ExpenseAmount'].'</div>
                                    </div>
                                    <div class="expenses-report-cell" style="width: 40%;">
                                        <div class="expenses-report-inner-w">'.$merValue['ExpenseDescription'].'</div>
                                    </div>
                                    <div class="expenses-report-cell" style="width: 25%;">
                                        <div class="expenses-report-inner-w">'.$merValue['ExpenseName'].'</div>
                                    </div>
                                </div>';
                                $sumGeneral += floatval($merValue['ExpenseAmount']);
                                if (strtotime($merValue['ExpenseDate'].' 00:00:00') >= strtotime($monthStart)) {
                                    $sumGeneralMonthly += floatval($merValue['ExpenseAmount']);
                                }
                            }
                            $page .=
                            '<div class="expenses-report-row" style="border-bottom: none;">
                                <div class="expenses-report-cell" style="width: 100%; border-left: none;">
                                    <div class="expenses-report-inner-w" style="text-align: center; color: #000; font-weight: bold;">סה"כ הוצאות כלליות: '.$sumGeneral.' שקלים</div>
                                </div>
                            </div>';
                        }
                        else 
                        {
                            $page .=
                            '<div class="expenses-report-row" style="border-bottom: none;">
                                <div class="expenses-report-cell" style="width: 100%; border-left: none;">
                                    <div class="expenses-report-inner-w" style="text-align: center; color: #000; font-weight: bold;">לא נמצאו הוצאות כלליות</div>
                                </div>
                            </div>';
                        }
                        $page .=
                        '<div class="comments-button-w">
                            <div id="addExpense" class="report-button animated-transition noselect"><a>הוספת הוצאה</a></div>
                        </div>
                    </div>

                    <div class="field-name" style="height: 20px; line-height: 20px; margin-top: 20px; font-weight: bold;">דו"ח הוצאות עבור מדריכים:</div>

                    <div class="expenses-report-w">
                        <div class="expenses-report-row"> 
                            <div class="expenses-report-cell" style="width: 20%; border-left: none;">
                                <div class="expenses-report-inner-w" style="color: #000; font-size: 19px; font-weight: bold; text-align: center;">תאריך</div>
                            </div>
                            <div class="expenses-report-cell" style="width: 15%;">
                                <div class="expenses-report-inner-w" style="color: #000; font-size: 19px; font-weight: bold; text-align: center;">עלות</div>
                            </div>
                            <div class="expenses-report-cell" style="width: 40%;">
                                <div class="expenses-report-inner-w" style="color: #000; font-size: 19px; font-weight: bold; text-align: center;">תיאור</div>
                            </div>
                            <div class="expenses-report-cell" style="width: 25%;">
                                <div class="expenses-report-inner-w" style="color: #000; font-size: 19px; font-weight: bold; text-align: center;">הוצאה</div>
                            </div>
                        </div>';

                        if (count($salariesResult) > 0)
                        {
                            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                            foreach ($salariesResult as $sKey => $sValue)
                            {
                                // SELECT THE DETAILS OF THE INSTRUCTOR
                                $stmt = $pdo->prepare("SELECT InstructorId,FirstName,LastName,IdNumber FROM instructors WHERE BINARY InstructorId=?");
                                $stmt->execute([$sValue['InstructorId']]); 
                                $instructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);     
                                
                                $instructorDetails = 'לא קיים במערכת';
                                if (count($instructorResult) > 0) {
                                    $instructorDetails = $instructorResult[0]['FirstName'].' '.$instructorResult[0]['LastName'].' - '.$instructorResult[0]['IdNumber'];
                                }

                                $expenseDate = str_replace('-', '/', $sValue['Date']);
                                $expenseDate = date('d/m/Y', strtotime($expenseDate));
                                
                                $page .=
                                '<div class="expenses-report-row"> 
                                    <div class="expenses-report-cell" style="width: 20%; border-left: none;">
                                        <div class="expenses-report-inner-w">'.$expenseDate.'</div>
                                    </div>
                                    <div class="expenses-report-cell" style="width: 15%;">
                                        <div class="expenses-report-inner-w">'.$sValue['Salary'].'</div>
                                    </div>
                                    <div class="expenses-report-cell" style="width: 40%;">
                                        <div class="expenses-report-inner-w">משכורת חודשית עבור המדריך: <span style="color: #000;">'.$instructorDetails.'</span></div>
                                    </div>
                                    <div class="expenses-report-cell" style="width: 25%;">
                                        <div class="expenses-report-inner-w">משכורת מדריך לפי שעות</div>
                                    </div>
                                </div>';
                                $sumInstructors += floatval($sValue['Salary']);
                                if ($sValue['Date'] == date('Y-m-t')) {
                                    $monthlySumInstructors += floatval($sValue['Salary']);
                                }
                            }
                            $pdo = null;
                            $page .=
                            '<div class="expenses-report-row" style="border-bottom: none;">
                                <div class="expenses-report-cell" style="width: 100%; border-left: none;">
                                    <div class="expenses-report-inner-w" style="text-align: center; color: #000; font-weight: bold;">סה"כ משכורות מדריכים: '.$sumInstructors.' שקלים</div>
                                </div>
                            </div>';
                        } 
                        else
                        {
                            $page .=
                            '<div class="expenses-report-row" style="border-bottom: none;">
                                <div class="expenses-report-cell" style="width: 100%; border-left: none;">
                                    <div class="expenses-report-inner-w" style="text-align: center; color: #000; font-weight: bold;">עדיין לא הוזנו משכורות עבור מדריכים במערכת</div>
                                </div>
                            </div>';
                        }
                        $page .=
                        '<div class="comments-button-w">
                            <div id="addInstructorExpense" class="report-button animated-transition noselect"><a>הוספת הוצאה</a></div>
                        </div>';                       

                    $page .=
                    '</div>';

                    $totalCirclesExpenses = 0;
                    if (count($permanentCirclesResult) > 0)
                    {
                        foreach ($permanentCirclesResult as $pcKey => $pcValue)
                        {
                            // SELECY ALL THE MONTHLY EXPENSES FOR THIS CIRCLE NAME
                            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
                            $stmt = $pdo->prepare("SELECT * FROM circles_budget WHERE BINARY CircleName=? AND ExpenseDate>=? ORDER BY ExpenseDate ASC");
                            $stmt->execute([$pcValue['PermanentCircleName'], $monthStart]); 
                            $circlesBudgetsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null; 

                            $circleExpensesSum = 0;                            

                            if (count($circlesBudgetsResult) > 0)
                            {
                                foreach ($circlesBudgetsResult as $cbKey => $cbValue) {                                    
                                    $circleExpensesSum += floatval($cbValue['ExpenseAmount']);
                                } 
                                $totalCirclesExpenses += $circleExpensesSum;
                            } 
                        }
                    }

                    $totalEventsExpenses = 0;                    
                    foreach ($eventExpensesResults as $exKey => $exValue) {
                        $totalEventsExpenses += floatval($exValue['ExpenseAmount']);
                    }

                    $totalExpenses = $sumGeneralMonthly + $totalCirclesExpenses + $totalEventsExpenses + $monthlySumInstructors;
                    $remainingBudget = $totalBudget - $monthlySumInstructors;
                    
                    $page .=
                    '<div class="field-name" style="height: 20px; line-height: 20px; margin-top: 20px; font-weight: bold;">סיכום ההוצאות:</div>
                    <div class="expenses-report-w">
                        <div class="expenses-report-row" style="border-bottom: none;">
                            <div class="expenses-report-cell" style="width: 100%; border-left: none;">
                                <div class="expenses-report-inner-w" style="text-align: center; color: #000; font-weight: bold;">סך כל ההוצאות עבור מדריכים והוצאות כלליות מ - '.$fromDate.' עד - '.$toDate.':<br/>'.($sumGeneral + $sumInstructors).' שקלים.</div>
                            </div>
                        </div>
                    </div>
                    <div class="expenses-report-w">
                        <div class="expenses-report-row" style="border-bottom: none;">
                            <div class="expenses-report-cell" style="width: 100%; border-left: none;">
                                <div class="expenses-report-inner-w" style="text-align: center; color: #000; font-weight: bold;">סך כל ההוצאות המתוכננות לחודש זה נכון להיום ('.$currentDate.'): '.$totalExpenses.' שקלים.</div>
                                <div class="expenses-report-inner-w" style="text-align: center; color: #000; font-weight: bold; padding-top: 0;">שארית התקציב הכללי לאחר ניכוי ההוצאות עבור חודש זה: '.$remainingBudget.' שקלים.</div>
                            </div>
                        </div>
                    </div>

                    <div class="button noselect animated-transition" style="display: inline-block; transform: translateX(-50%); left: 50%; margin-top: 20px;"><a target="_blank" href="http://'.$GLOBALS['SERVER_ADDRESS'].'/export.php?report=allExpenses&from='.$fromDateUnix.'&to='.$toDateUnix.'">excel יצוא דו"ח לקובץ</a></div>
                    
                </div>
            </div>
            <div class="footer"></div>'.
            self::ProfileScreenJs().
            '<script type="text/javascript">
                var instructorSalaries = [';
                foreach ($instructorsResult as $iKey => $iValue)
                {
                    // CHECK IF THE MONTHLY SALARY IS ALREADY SET FOR THIS INSTRUCTOR, IF SO - SELECT IT
                    $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
                    $stmt = $pdo->prepare("SELECT * FROM instructors_salaries WHERE Date=? AND InstructorId=?");
                    $stmt->execute([$monthEndDate, $iValue['InstructorId']]); 
                    $salariesResults = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                    $pdo = null;

                    $instructorSalaryId = -1;
                    if (count($salariesResults) > 0) {
                        $instructorSalaryId = $salariesResults[0]['SalaryId'];
                    }

                    $page .= '["'.$iValue['FirstName'].' '.$iValue['LastName'].' - '.$iValue['IdNumber'].'", "'.$iValue['InstructorId'].'", "'.$instructorSalaryId.'"]';
                    if ($iKey < count($instructorsResult) - 1) {
                        $page .= ',';
                    }
                }
            $page .= '];
            </script>';
            return $page;
        }

        // RETURNS THE CIRCLE ATTENDANCE PAGE
        public static function CircleAttendancePage() : string 
        {
            $currentDateTime = date("Y-m-d H:i:s");
            $monthStart = date('Y-m-01').' 00:00:00'; 
            $monthEnd = date('Y-m-t').' 23:59:59'; 
            $monthStartDay = substr(date('01-m-Y'), 0, strpos(date('01-m-Y'), '-'));
            $monthEndDay = substr(date('t-m-Y'), 0, strpos(date('t-m-Y'), '-'));

            $fromDate = isset($_POST['updateDatesRange']) ? $_POST['fromDate'] : date('01/m/Y');
            $toDate = isset($_POST['updateDatesRange']) ? $_POST['toDate'] : date('d/m/Y');

            $fromDateUnix = str_replace('/', '-', $fromDate);
            $fromDateUnix = date('Y-m-d', strtotime($fromDateUnix));
            $toDateUnix = str_replace('/', '-', $toDate);
            $toDateUnix = date('Y-m-d', strtotime($toDateUnix));

            // GET THE CIRCLES THAT ARE ALREADY FINISHED THIS MONTH
            $to = $toDateUnix.' 23:59:59';
            $from = $fromDateUnix.' 00:00:00';
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY EndDate<=? AND StartDate>=? ORDER BY StartDate ASC");
            $stmt->execute([$to, $from]); 
            $finishedCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            $pdo = null;
            
            $page =            
            '<div class="header">
                <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
            </div>
            <div>
                <div class="user-profile-menu-w">                        
                    <div id="managerStudentsList" class="user-profile-menu-item"><a>רשימת תלמידים</a></div>
                    <div id="managerInstructorsList" class="user-profile-menu-item"><a>רשימת מדריכים</a></div>
                    <div class="user-profile-menu-item"><a href="circle.php">ניהול חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="attendance.php">נוכחות חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="event.php">ניהול אירועים</a></div>
                    <div id="manageManagerMessages" class="user-profile-menu-item"><a>ניהול הודעות</a></div>
                    <div class="user-profile-menu-item"><a href="budget.php">ניהול תקציב / דוחות</a></div>
                    <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                    <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                </div>
            </div>
            <div class="body-wrapper">
                <div class="title">דו"ח נוכחות חוגים</div>
                <div class="inner-body-w">
                    <form method="post">
                        <div id="datesRange" class="field-w" style="margin-top: 0;">
                            <div class="field-name">מ:</div>
                            <input type="text" id="fromDate" name="fromDate" class="field-value" value="'.$fromDate.'"/>
                            <div class="field-name" style="margin-right: 10px;">עד:</div>
                            <input type="text" id="toDate" name="toDate" class="field-value" value="'.$toDate.'"/>
                            <button class="button noselect animated-transition" style="border: none; width: 50px; font-family: Arial; font-size: 19px;" id="updateDatesRange" name="updateDatesRange">עדכן</button>
                        </div>
                    </form>
                    <div class="itm-mgmt-title">דו"ח נוכחות חוגים עבור חוגים שהסתיימו מ: '.$fromDate.' עד: '.$toDate.'</div>';
                    if (count($finishedCirclesResult) > 0)
                    {
                        foreach ($finishedCirclesResult as $fcKey => $fcValue)
                        {
                            $page .= '<div class="itm-mgmt-attend-report-w">';

                            // SELECT THE INSTRUCTOR OF THIS CIRCLE
                            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                            $stmt = $pdo->prepare("SELECT IdNumber,FirstName,LastName FROM instructors WHERE BINARY InstructorId=?");
                            $stmt->execute([$fcValue['CircleInstructorId']]); 
                            $circleInstructorDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null;
    
                            $instructorName = 'המדריך לא קיים במערכת';
                            if (count($circleInstructorDetails) > 0)
                            {
                                $instructorName = $circleInstructorDetails[0]['FirstName'].' '.$circleInstructorDetails[0]['LastName'].' - '.
                                    $circleInstructorDetails[0]['IdNumber'];
                            }                           

                            $startDate = explode(' ', $fcValue['StartDate'])[0];
                            $startDate = str_replace('-', '/', $startDate);
                            $startDate = date('d/m/Y', strtotime($startDate));
                            $startDate = explode(' ', $fcValue['StartDate'])[1].' '.$startDate;

                            $endDate = explode(' ', $fcValue['EndDate'])[0];
                            $endDate = str_replace('-', '/', $endDate);
                            $endDate = date('d/m/Y', strtotime($endDate));
                            $endDate = explode(' ', $fcValue['EndDate'])[1].' '.$endDate;
    
                            $page .=
                            '<div class="itm-mgmt-report-row">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">שם החוג: '.$fcValue['CircleName'].', בהדרכת: '.$instructorName.'.</div>
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold; padding-top: 0;">מ: '.$startDate.' עד: '.$endDate.'.</div>
                                </div>
                            </div>
                            <div class="itm-mgmt-report-row">
                                <div class="itm-mgmt-report-cell" style="width: 50%; border-left: none;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">משתתפים</div>
                                </div>
                                <div class="itm-mgmt-report-cell" style="width: calc(50% - 1px);">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">מפגשים</div>
                                </div>
                            </div>';

                            // SELECT ALL THE MEETINGS OF THIS CIRCLE
                            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                            $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=? ORDER BY StartDate ASC");
                            $stmt->execute([$fcValue['CircleId']]); 
                            $circleMeetingsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null;
                            
                            $circleParticipantsArray = [];

                            foreach ($circleMeetingsResult as $cmKey => $cmValue)
                            {
                                // SELECT THE NUMBER OF PARTICIPANTS OF THIS MEETING
                                $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                                $stmt = $pdo->prepare("SELECT * FROM circle_participants WHERE BINARY MeetingId=?");
                                $stmt->execute([$cmValue['MeetingId']]); 
                                $meetingParticipantsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $pdo = null;

                                foreach ($meetingParticipantsResult as $mpKey => $mpValue)
                                {
                                    if (!in_array($mpValue['StudentId'], $circleParticipantsArray)) {
                                        array_push($circleParticipantsArray, $mpValue['StudentId']);
                                    }
                                }

                                $meetingDate = explode(' ', $cmValue['StartDate'])[0];
                                $meetingDate = str_replace('-', '/', $meetingDate);
                                $meetingDate = date('d/m/Y', strtotime($meetingDate));
                                $meetingStartHour = explode(' ', $cmValue['StartDate'])[1];
                                $meetingEndHour = explode(' ', $cmValue['EndDate'])[1];

                                $meetingWeekDay = 'יום ראשון';
                                if (intval($cmValue['WeekDay']) == 1) { $meetingWeekDay = 'יום שני'; }
                                if (intval($cmValue['WeekDay']) == 2) { $meetingWeekDay = 'יום שלישי'; }
                                if (intval($cmValue['WeekDay']) == 3) { $meetingWeekDay = 'יום רביעי'; }
                                if (intval($cmValue['WeekDay']) == 4) { $meetingWeekDay = 'יום חמישי'; }
                                if (intval($cmValue['WeekDay']) == 5) { $meetingWeekDay = 'יום שישי'; }
                                if (intval($cmValue['WeekDay']) == 6) { $meetingWeekDay = 'יום שבת'; }
                                    
                                $page .= 
                                '<div class="itm-mgmt-report-row">
                                    <div class="itm-mgmt-report-cell" style="width: 50%; border-left: none;">
                                        <div class="itm-mgmt-report-cell-txt">'.count($meetingParticipantsResult).' משתתפים</div>';
                                        if (count($meetingParticipantsResult) > 0) 
                                        {
                                            $page .= '<div class="report-button-cont">
                                                <div participantsCircleId="'.$fcValue['CircleId'].'" meetingId="'.$cmValue['MeetingId'].'" class="report-button animated-transition noselect"><a>הצגת משתתפים</a></div>
                                            </div>';
                                        }                                        
                                        $page .=
                                    '</div>
                                    <div class="itm-mgmt-report-cell" style="width: calc(50% - 1px);">
                                        <div class="itm-mgmt-report-cell-txt">'.$meetingWeekDay.' - '.$meetingDate.'</div>
                                        <div class="itm-mgmt-report-cell-txt" style="padding-top: 0;">מ: '.$meetingStartHour.' עד: '.$meetingEndHour.'</div>
                                    </div>
                                </div>';
                            }

                            $page .=
                            '<div class="itm-mgmt-report-row" style="border-bottom: none;">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">סך הכל '.count($circleParticipantsArray).' תלמידים נרשמו לחוג זה.</div>';
                                    if (count($circleParticipantsArray) > 0)
                                    {
                                        $page .=
                                        '<div class="comments-button-w">
                                            <div class="report-button animated-transition noselect" commentsCircleId="'.$fcValue['CircleId'].'"><a>הצגת משובים</a></div>
                                        </div>';
                                    }
                                    $page .=
                                '</div>
                            </div>';

                            $page .= 
                            '</div>';
                        }
                    }
                    else
                    {
                        $page .= 
                        '<div class="itm-mgmt-attend-report-w">
                            <div class="itm-mgmt-report-row" style="border-bottom: none;">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center;">עדיין לא ניתן להפיק דו"ח נוכחות עבור חוגים לחודש זה.</div>
                                </div>
                            </div>
                        </div>';
                    }

                    if (count($finishedCirclesResult) > 0) 
                    {
                        $page .=
                        '<div class="button noselect animated-transition" style="display: inline-block; transform: translateX(-50%); left: 50%; margin-top: 20px;"><a target="_blank" href="http://'.$GLOBALS['SERVER_ADDRESS'].'/export.php?report=attendance&from='.$fromDateUnix.'&to='.$toDateUnix.'">excel יצוא דו"ח לקובץ</a></div>';
                    }
                    $page .=
                '</div>
            </div>
            <div class="footer"></div>'
            .self::ProfileScreenJs();
            return $page;
        }

        // RETURNS THE CIRCLE MANAGEMENT PAGE
        public static function CircleManagementPage() : string 
        {
            $currentDateTime = date("Y-m-d H:i:s");
            $monthStart = date('Y-m-01').' 00:00:00'; 
            $monthEnd = date('Y-m-t').' 23:59:59'; 
            $monthStartDay = substr(date('01-m-Y'), 0, strpos(date('01-m-Y'), '-'));
            $monthEndDay = substr(date('t-m-Y'), 0, strpos(date('t-m-Y'), '-'));

            $fromDate = isset($_POST['updateDatesRange']) ? $_POST['fromDate'] : date('01/m/Y');
            $toDate = isset($_POST['updateDatesRange']) ? $_POST['toDate'] : date('d/m/Y');

            $fromDateUnix = str_replace('/', '-', $fromDate);
            $fromDateUnix = date('Y-m-d', strtotime($fromDateUnix));
            $toDateUnix = str_replace('/', '-', $toDate);
            $toDateUnix = date('Y-m-d', strtotime($toDateUnix));

            // SELECT THE PERMANENT CIRCLE NAMES
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM permanent_circles");
            $stmt->execute();
            $permanentCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            // SELECT THE TOTAL BUDGET OF THE SYSTEM
            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
            $stmt = $pdo->prepare("SELECT TotalSchoolBudget FROM total_budget");
            $stmt->execute();
            $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;
            
            $page =            
            '<div class="header">
                <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
            </div>
            <div>
                <div class="user-profile-menu-w">                        
                    <div id="managerStudentsList" class="user-profile-menu-item"><a>רשימת תלמידים</a></div>
                    <div id="managerInstructorsList" class="user-profile-menu-item"><a>רשימת מדריכים</a></div>
                    <div class="user-profile-menu-item"><a href="circle.php">ניהול חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="attendance.php">נוכחות חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="event.php">ניהול אירועים</a></div>
                    <div id="manageManagerMessages" class="user-profile-menu-item"><a>ניהול הודעות</a></div>
                    <div class="user-profile-menu-item"><a href="budget.php">ניהול תקציב / דוחות</a></div>
                    <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                    <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                </div>
            </div>
            <div class="body-wrapper">
                <div class="title">ניהול חוגים</div>
                <div class="inner-body-w">
                    <form method="post">
                        <div id="datesRange" class="field-w" style="margin-top: 0;">
                            <div class="field-name">מ:</div>
                            <input type="text" id="fromDate" name="fromDate" class="field-value" value="'.$fromDate.'"/>
                            <div class="field-name" style="margin-right: 10px;">עד:</div>
                            <input type="text" id="toDate" name="toDate" class="field-value" value="'.$toDate.'"/>
                            <button class="button noselect animated-transition" style="border: none; width: 50px; font-family: Arial; font-size: 19px;" id="updateDatesRange" name="updateDatesRange">עדכן</button>
                        </div>
                    </form>
                    <div class="itm-mgmt-title">דו"ח הוצאות עבור חוגים מ: '.$fromDate.' עד: '.$toDate.'</div>';

                    $page .= 
                    '<div class="itm-mgmt-attend-report-w">
                        <div class="itm-mgmt-report-row" style="border-bottom: none;">
                            <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">סכום התקציב הכללי של המערכת: '.$totalBudgetResult[0]['TotalSchoolBudget'].' שקלים.</div>
                            </div>
                        </div>
                    </div>';

                    $totalCirclesExpenses = 0;
                    if (count($permanentCirclesResult) > 0)
                    {
                        foreach ($permanentCirclesResult as $pcKey => $pcValue)
                        {
                            // SELECY ALL THE MONTHLY EXPENSES FOR THIS CIRCLE NAME
                            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
                            $stmt = $pdo->prepare("SELECT * FROM circles_budget WHERE BINARY CircleName=? AND ExpenseDate>=? AND ExpenseDate<=? ORDER BY ExpenseDate ASC");
                            $stmt->execute([$pcValue['PermanentCircleName'], $fromDateUnix, $toDateUnix]); 
                            $circlesBudgetsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null; 

                            $circleExpensesSum = 0;

                            $page .= '<div class="itm-mgmt-attend-report-w">';

                            $page .=
                            '<div class="itm-mgmt-report-row">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">שם החוג: '.$pcValue['PermanentCircleName'].'.</div>
                                </div>
                            </div>
                            <div class="itm-mgmt-report-row">
                                <div class="itm-mgmt-report-cell" style="width: 20%; border-left: none;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">תאריך</div>
                                </div>
                                <div class="itm-mgmt-report-cell" style="width: 15%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">עלות</div>
                                </div>
                                <div class="itm-mgmt-report-cell" style="width: 40%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">תיאור</div>
                                </div>
                                <div class="itm-mgmt-report-cell" style="width: 25%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">הוצאה</div>
                                </div>
                            </div>';

                            if (count($circlesBudgetsResult) > 0)
                            {
                                foreach ($circlesBudgetsResult as $cbKey => $cbValue)
                                {
                                    $expenseDate = str_replace('-', '/', $cbValue['ExpenseDate']);
                                    $expenseDate = date('d/m/Y', strtotime($expenseDate));

                                    $page .=
                                    '<div class="itm-mgmt-report-row">
                                        <div class="itm-mgmt-report-cell" style="width: 20%; border-left: none;">
                                            <div class="itm-mgmt-report-cell-txt">'.$expenseDate.'</div>
                                        </div>
                                        <div class="itm-mgmt-report-cell" style="width: 15%;">
                                            <div class="itm-mgmt-report-cell-txt">'.$cbValue['ExpenseAmount'].'</div>
                                        </div>
                                        <div class="itm-mgmt-report-cell" style="width: 40%;">
                                            <div class="itm-mgmt-report-cell-txt">'.$cbValue['ExpenseDescription'].'</div>
                                        </div>
                                        <div class="itm-mgmt-report-cell" style="width: 25%;">
                                            <div class="itm-mgmt-report-cell-txt">'.$cbValue['ExpenseName'].'</div>
                                        </div>
                                    </div>';
                                    $circleExpensesSum += floatval($cbValue['ExpenseAmount']);
                                } 
                                $totalCirclesExpenses += $circleExpensesSum; 
                                $page .= 
                                '<div class="itm-mgmt-report-row" style="border-bottom: none;">
                                    <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                        <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">סיכום ההוצאות עבור חוג זה לתקופה הנ"ל: '.$circleExpensesSum.' שקלים.</div>
                                    </div>
                                </div>';
                            }
                            else
                            {
                                $page .=
                                '<div class="itm-mgmt-report-row" style="border-bottom: none;">
                                    <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                        <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">לא נקבעו הוצאות עבור חוג זה לתקופה הנ"ל.</div>
                                    </div>
                                </div>';
                            }
                            $page .=
                            '<div class="comments-button-w">
                                <div class="report-button animated-transition noselect" expenseCircleName="'.$pcValue['PermanentCircleName'].'"><a>הוספת הוצאה</a></div>
                            </div>';
                            $page .= '</div>';
                        }
                        $page .= 
                        '<div class="itm-mgmt-attend-report-w">
                            <div class="itm-mgmt-report-row" style="border-bottom: none;">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">סיכום ההוצאות עבור חוגים מ '.$fromDate.' עד '.$toDate.': '.$totalCirclesExpenses.' שקלים.</div>
                                </div>
                            </div>
                        </div>';
                    }
                    else 
                    {
                        $page .= 
                        '<div class="itm-mgmt-attend-report-w">
                            <div class="itm-mgmt-report-row" style="border-bottom: none;">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center;">עדיין לא קיימים חוגים במערכת.</div>
                                </div>
                            </div>
                        </div>';
                    }
                    
                    $page .=
                    '<div class="button noselect animated-transition" style="display: inline-block; transform: translateX(-50%); left: 50%; margin-top: 20px;"><a target="_blank" href="http://'.$GLOBALS['SERVER_ADDRESS'].'/export.php?report=circleExpenses&from='.$fromDateUnix.'&to='.$toDateUnix.'">excel יצוא דו"ח לקובץ</a></div>';

                    $page .=
                '</div>
            </div>
            <div class="footer"></div>'
            .self::ProfileScreenJs();
            return $page;
        }

        // RETURNS THE EVENT MANAGEMENT PAGE
        public static function EventManagementPage() : string 
        {
            $currentDateTime = date("Y-m-d H:i:s");
            $monthStart = date('Y-m-01').' 00:00:00'; 
            $monthEnd = date('Y-m-t').' 23:59:59'; 
            $monthStartDay = substr(date('01-m-Y'), 0, strpos(date('01-m-Y'), '-'));
            $monthEndDay = substr(date('t-m-Y'), 0, strpos(date('t-m-Y'), '-'));

            $fromDate = isset($_POST['updateDatesRange']) ? $_POST['fromDate'] : date('01/m/Y');
            $toDate = isset($_POST['updateDatesRange']) ? $_POST['toDate'] : date('d/m/Y');

            $fromDateUnix = str_replace('/', '-', $fromDate);
            $fromDateUnix = date('Y-m-d', strtotime($fromDateUnix));
            $toDateUnix = str_replace('/', '-', $toDate);
            $toDateUnix = date('Y-m-d', strtotime($toDateUnix));

            // SELECT THE EVENTS 
            $to = $toDateUnix.' 23:59:59';
            $from = $fromDateUnix.' 00:00:00';
            $pdo = UTILITIES::PDO_DB_Connection('school_events');
            $stmt = $pdo->prepare("SELECT * FROM events_table WHERE BINARY StartDate>=? OR EndDate>=? AND StartDate<=? ORDER BY StartDate ASC");
            $stmt->execute([$from, $from, $to]);
            $monthlyEventsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            // SELECT THE TOTAL BUDGET OF THE SYSTEM
            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
            $stmt = $pdo->prepare("SELECT TotalSchoolBudget FROM total_budget");
            $stmt->execute();
            $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // SELECT ALL EVENT EXPENSES FOR THIS DATE RANGE
            $stmt = $pdo->prepare("SELECT * FROM events_budget WHERE BINARY ExpenseDate>=? AND ExpenseDate<=? ORDER BY ExpenseDate ASC");
            $stmt->execute([$fromDateUnix, $toDateUnix]);
            $eventExpensesResults = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            $pdo = null;

            $eventsArray = [];
            foreach ($monthlyEventsResult as $meKey => $meValue)
            {
                $eventsArray[$meValue['EventId']] = [];
            }

            foreach ($eventExpensesResults as $exKey => $exValue)
            {
                if (!isset($eventsArray[$exValue['EventId']])) {
                    $eventsArray[$exValue['EventId']] = [$exValue];
                }
                else {
                    array_push($eventsArray[$exValue['EventId']], $exValue);
                }
            }
            
            $page =            
            '<div class="header">
                <div class="logo"><a class="animated-transition noselect" href="index.php">פנימיית כפר הנוער</a></div>
                <div class="header-login noselect animated-transition"><a class="animated-transition" logged>'.htmlentities($_SESSION['FirstName'].' '.$_SESSION['LastName']).'</a></div>                
            </div>
            <div>
                <div class="user-profile-menu-w">                        
                    <div id="managerStudentsList" class="user-profile-menu-item"><a>רשימת תלמידים</a></div>
                    <div id="managerInstructorsList" class="user-profile-menu-item"><a>רשימת מדריכים</a></div>
                    <div class="user-profile-menu-item"><a href="circle.php">ניהול חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="attendance.php">נוכחות חוגים</a></div>
                    <div class="user-profile-menu-item"><a href="event.php">ניהול אירועים</a></div>
                    <div id="manageManagerMessages" class="user-profile-menu-item"><a>ניהול הודעות</a></div>
                    <div class="user-profile-menu-item"><a href="budget.php">ניהול תקציב / דוחות</a></div>
                    <div id="profile" class="user-profile-menu-item"><a>פרופיל</a></div>
                    <div class="user-profile-menu-item" style="border-bottom: none;"><a href="http://'.$GLOBALS['SERVER_ADDRESS'].'/logout.php">יציאה</a></div>
                </div>
            </div>
            <div class="body-wrapper">
                <div class="title">ניהול אירועים</div>
                <div class="inner-body-w">
                    <form method="post">
                        <div id="datesRange" class="field-w" style="margin-top: 0;">
                            <div class="field-name">מ:</div>
                            <input type="text" id="fromDate" name="fromDate" class="field-value" value="'.$fromDate.'"/>
                            <div class="field-name" style="margin-right: 10px;">עד:</div>
                            <input type="text" id="toDate" name="toDate" class="field-value" value="'.$toDate.'"/>
                            <button class="button noselect animated-transition" style="border: none; width: 50px; font-family: Arial; font-size: 19px;" id="updateDatesRange" name="updateDatesRange">עדכן</button>
                        </div>
                    </form>
                    <div class="itm-mgmt-title">דו"ח הוצאות עבור אירועים מ: '.$fromDate.' עד: '.$toDate.'</div>';

                    $page .= 
                    '<div class="itm-mgmt-attend-report-w">
                        <div class="itm-mgmt-report-row" style="border-bottom: none;">
                            <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">סכום התקציב הכללי של המערכת: '.$totalBudgetResult[0]['TotalSchoolBudget'].' שקלים.</div>
                            </div>
                        </div>
                    </div>';

                    if (count($eventsArray) > 0)
                    {
                        $totalEventsExpenses = 0;
                        foreach ($eventsArray as $eKey => $eValue)
                        {
                            // SELECT THE EVENT DETAILS
                            $pdo = UTILITIES::PDO_DB_Connection('school_events');
                            $stmt = $pdo->prepare("SELECT EventName FROM events_table WHERE BINARY EventId=?");
                            $stmt->execute([$eKey]);
                            $eventResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null;
    
                            $eventName = 'האירוע לא קיים במערכת';
                            if (count($eventResult) > 0) {
                                $eventName = $eventResult[0]['EventName'];
                            }
    
                            $page .= '<div class="itm-mgmt-attend-report-w">';
    
                            $page .=
                            '<div class="itm-mgmt-report-row">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">שם האירוע: '.$eventName.'.</div>
                                </div>
                            </div>
                            <div class="itm-mgmt-report-row">
                                <div class="itm-mgmt-report-cell" style="width: 20%; border-left: none;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">תאריך</div>
                                </div>
                                <div class="itm-mgmt-report-cell" style="width: 15%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">עלות</div>
                                </div>
                                <div class="itm-mgmt-report-cell" style="width: 40%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">תיאור</div>
                                </div>
                                <div class="itm-mgmt-report-cell" style="width: 25%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000;">הוצאה</div>
                                </div>
                            </div>';
    
                            $eventExpensesSum = 0;
                            if (count($eValue) > 0)
                            {
                                foreach ($eValue as $exKey => $exValue)
                                {
                                    $expenseDate = str_replace('-', '/', $exValue['ExpenseDate']);
                                    $expenseDate = date('d/m/Y', strtotime($expenseDate));
    
                                    $page .=
                                    '<div class="itm-mgmt-report-row">
                                        <div class="itm-mgmt-report-cell" style="width: 20%; border-left: none;">
                                            <div class="itm-mgmt-report-cell-txt">'.$expenseDate.'</div>
                                        </div>
                                        <div class="itm-mgmt-report-cell" style="width: 15%;">
                                            <div class="itm-mgmt-report-cell-txt">'.$exValue['ExpenseAmount'].'</div>
                                        </div>
                                        <div class="itm-mgmt-report-cell" style="width: 40%;">
                                            <div class="itm-mgmt-report-cell-txt">'.$exValue['ExpenseDescription'].'</div>
                                        </div>
                                        <div class="itm-mgmt-report-cell" style="width: 25%;">
                                            <div class="itm-mgmt-report-cell-txt">'.$exValue['ExpenseName'].'</div>
                                        </div>
                                    </div>';
                                    $eventExpensesSum += floatval($exValue['ExpenseAmount']);
                                } 
                                $totalEventsExpenses += $eventExpensesSum; 
                                $page .= 
                                '<div class="itm-mgmt-report-row" style="border-bottom: none;">
                                    <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                        <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">סיכום ההוצאות עבור אירוע זה לתקופה הנ"ל: '.$eventExpensesSum.' שקלים.</div>
                                    </div>
                                </div>';
                            }
                            else
                            {
                                $page .=
                                '<div class="itm-mgmt-report-row" style="border-bottom: none;">
                                    <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                        <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">לא נקבעו הוצאות עבור אירוע זה לתקופה הנ"ל.</div>
                                    </div>
                                </div>';
                            }
                            $page .=
                            '<div class="comments-button-w">
                                <div class="report-button animated-transition noselect" expenseEventId="'.$eKey.'"><a>הוספת הוצאה</a></div>
                            </div>';
                            $page .= '</div>';
                        }
                        $page .= 
                        '<div class="itm-mgmt-attend-report-w">
                            <div class="itm-mgmt-report-row" style="border-bottom: none;">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center; color: #000; font-weight: bold;">סיכום ההוצאות עבור אירועים מ '.$fromDate.' עד '.$toDate.': '.$totalEventsExpenses.' שקלים.</div>
                                </div>
                            </div>
                        </div>'; 
                    }
                    else 
                    {
                        $page .= 
                        '<div class="itm-mgmt-attend-report-w">
                            <div class="itm-mgmt-report-row" style="border-bottom: none;">
                                <div class="itm-mgmt-report-cell" style="border-left: none; width: 100%;">
                                    <div class="itm-mgmt-report-cell-txt" style="text-align: center;">לא נמצאו הוצאות עבור אירועים בתאריכים המבוקשים</div>
                                </div>
                            </div>
                        </div>';
                    }
                    
                    $page .=
                    '<div class="button noselect animated-transition" style="display: inline-block; transform: translateX(-50%); left: 50%; margin-top: 20px;"><a target="_blank" href="http://'.$GLOBALS['SERVER_ADDRESS'].'/export.php?report=eventExpenses&from='.$fromDateUnix.'&to='.$toDateUnix.'">excel יצוא דו"ח לקובץ</a></div>';

                    $page .=
                '</div>
            </div>
            <div class="footer"></div>'
            .self::ProfileScreenJs();
            return $page;
        }
    }

    class UTILITIES
    {
        // CREATES A SESSION ID
        public static function CreateSessionId() : string 
        {
            $salt = SAFE_TEXT::GetRandomString(rand(10, 18));
            $sid = bin2hex($salt.time().uniqid().$salt);
            $sid = hash('sha256', $sid);
            $sid = strlen($sid) > 27 ? substr($sid, 0, rand(19, 27)) : $sid;
            while (file_exists('C:\xampp\tmp\sess_'.$sid)) 
            {
                $salt = SAFE_TEXT::GetRandomString(rand(10, 18));
                $sid = bin2hex($salt.time().uniqid().$salt);
                $sid = hash('sha256', $sid);
                $sid = strlen($sid) > 27 ? substr($sid, 0, rand(19, 27)) : $sid;
            }
            return $sid;
        }

        // GETS AN HEBREW DAY FROM AN ENGLISH DAY
        public static function GetHebrewDay(string $engDay) : string
        {
            if ($engDay == 'Sunday') { return 'יום ראשון'; }
            if ($engDay == 'Monday') { return 'יום שני'; }
            if ($engDay == 'Tuesday') { return 'יום שלישי'; }
            if ($engDay == 'Wednesday') { return 'יום רביעי'; }
            if ($engDay == 'Thursday') { return 'יום חמישי'; }
            if ($engDay == 'Friday') { return 'יום שישי'; }
            if ($engDay == 'Saturday') { return 'יום שבת'; }
        }

        // CREATE A NEW 'PDO' DATABASE CONNECTION TO THE GIVEN DATABASE
        public static function PDO_DB_Connection(string $database, string $host = 'localhost', 
            string $user = 'root', string $password = '3f55ZuvWrrt955') : PDO 
        {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            $pdo = new PDO("mysql:charset=utf8mb4;mysql:host=$host;dbname=$database", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
            return $pdo;
        }

        // VALIDATES 10 DIGITS PHONE NUMBER WITHOUT COUNTRY CODE
        public static function ValidPhoneNumber(string $phone) : bool 
        {
            return preg_match('/^[0-9]{4,10}+$/', $phone);
        }

        // VALIDATES AN EMAIL ADDRESS
        public static function ValidateEmailAddress(string $email) : bool 
        {
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            return true;
        }
    }

    class SESSION 
    {
        // LOGS THE USER INTO THE SYSTEM
        public static function LogIn($username, $password) : string
        {
            // VALIDATE CREDENTIALS AGAIINST THE DATABASE
            $table = isset($_POST['student']) ? 'students' : (isset($_POST['instructor']) ? 'instructors' : 'managers');
            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
            $stmt = $pdo->prepare("SELECT * FROM ".$table." WHERE BINARY IdNumber=? AND BirthDate=?"); 
            $stmt->execute([$_POST['username'], $_POST['password']]); 
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null; 

            if (!$result || count($result) == 0) {
                return 'המשתמש לא קיים במערכת';
            }

            // INITIALIZE SESSION
            foreach ($result as $row)
            {
                foreach ($row as $key => $value) 
                {
                    $_SESSION[$key] = $value;
                }
            }
            $_SESSION['UserType'] = strtoupper($table[0]).substr($table, 1, strlen($table) - 2);

            // SUCCESSFUL LOG IN
            header("Location: http://$GLOBALS[SERVER_ADDRESS]/index.php");
            exit;
        }

        // LOGS A CONNECTED USER OUT OF THE SYSTEM
        public static function LogOut() : void 
        {
            $_SESSION = []; 
            header("Location: http://$GLOBALS[SERVER_ADDRESS]/index.php");
            exit;
        }
    }

    class SAFE_TEXT
    {
        // ESCAPES THE CHARS '=' ',' '{' '}' '|' IN THE PARAM VALUE TO BE READY TO PUT IN THE FILE
        public static function EscapeSessVarsToFile(string $value) : string
        {
            $hash = 'panS024sdf2r1qpmy5zk2@1!%^$3$%FdlsjGkJsf5sdf7sdgp784w862)erfkuA44^!$F&ksdfiwlgfpyb05sdf1l5w1aE(*@RTTnvaiuleqpfhbsysmkwmauxplgrbazGR(@R)hW';
            $hash = hash('sha256', $hash);
            $hash = substr($hash, 0, 10);
            $escaped = str_replace('_', $hash, $value);
            $escaped = str_replace(',', '_COMMA_', $escaped);
            $escaped = str_replace('=', '_EQUALS_', $escaped);
            $escaped = str_replace('{', '_LEFT_', $escaped);
            $escaped = str_replace('}', '_RIGHT_', $escaped);
            $escaped = str_replace('|', '_SEP_', $escaped);
            return $escaped;
        }

        // ESCAPES THE CHARS '=' AND ',' IN THE PARAM VALUE TO BE READY TO PUT IN THE FILE
        public static function ReescapeSessVarsFromFile(string $value) : string
        {
            $hash = 'panS024sdf2r1qpmy5zk2@1!%^$3$%FdlsjGkJsf5sdf7sdgp784w862)erfkuA44^!$F&ksdfiwlgfpyb05sdf1l5w1aE(*@RTTnvaiuleqpfhbsysmkwmauxplgrbazGR(@R)hW';
            $hash = hash('sha256', $hash);
            $hash = substr($hash, 0, 10);
            $escaped = str_replace($hash, '_', $value);
            $escaped = str_replace('_COMMA_', ',', $escaped);
            $escaped = str_replace('_EQUALS_', '=', $escaped);
            $escaped = str_replace('_LEFT_', '{', $escaped);
            $escaped = str_replace('_RIGHT_', '}', $escaped);
            $escaped = str_replace('_SEP_', '|', $escaped);
            return $escaped;
        }

        // RETURNS A RANDOM STRING IN A SPECIFIED LENGTH
        public static function GetRandomString(int $length, bool $letter = false) : string 
        {
            $chars = 
            '01234567dflgjfhf2d4hfg54juj4sf4ew5qg4ehw5h5s4es8g4we89ABCDEFG4535434553453HIJKLdsgdfjhk37569345kj3453khjlhgMNOPQRSDSGHdfhfdhTUVW1437952XdfhasghgljklYZ635DSfdhGFDG34abDSHcdefSDGghijklSD79845631245SDFGFHmnopq42233rstuvSDF6437gj98122Hw45454xygeskdvuz';
            $random_string = '';
            for ($i = 0; $i < $length; $i++)
            {
                $random_index = rand(0, 246);
                $random_string = $random_string.$chars[$random_index];
            }
            if ($letter === true) 
            {
                $letters = 'abjkdybnifldirubcyvusrfbJirubcyvsdDsdhgFssadfSHfvdsfgdsfgdsfHfDHdfHfhsDJuFFKuilOIDRSawdfKyJsDajGHkjWYartKrokYUuYAwegufuesugfuybnifldirubcyvusrfbJirubcyvusKHCRIirubcyvusNCRUIRV';
                for ($i = 0; $i < 7; $i++)
                {
                    $random_index = rand(0, 90);
                    $random_string = $letters[$random_index].$random_string;
                }                
            }
            return $random_string;
        }

        // ESCAPES THE SPECIAL CHARACTERS IN A STRING AND MAKE IT PREPARED FOR INSERTION TO THE DATABASE 
        public static function SqlEscapeString(string $string) : string 
        {
            if (empty($string)) return '';
            $hash = 'panS024sdf2r1qpmy5zk2@1!%^$3$%FdlsjGkJsf5sdf7sdgp784w862)erfkuA44^!$F&ksdfiwlgfpyb05sdf1l5w1aE(*@RTTnvaiuleqpfhbsysmkwmauxplgrbazGR(@R)hW';
            $hash = hash('sha256', $hash);
            $hash = substr($hash, 0, 10);
            $escaped = str_replace('_', $hash, $string);
            $escaped = str_replace('"', '_DQ_', $escaped);
            $escaped = str_replace("'", '_SQ_', $escaped);
            $escaped = str_replace('\\', '_BS_', $escaped);
            $escaped = str_replace('%', '_M_', $escaped);
            $escaped = str_replace('?', '_QM_', $escaped);
            $escaped = str_replace('.', '_P_', $escaped); 
            $escaped = str_replace('$', '_D_', $escaped);
            $escaped = str_replace('@', '_AT_', $escaped);
            $escaped = str_replace('*', '_S_', $escaped);
            $escaped = str_replace('^', '_H_', $escaped);
            $escaped = str_replace('#', '_HASH_', $escaped);
            $escaped = str_replace('!', '_RM_', $escaped);
            $escaped = str_replace('~', '_AB_', $escaped);
            $escaped = str_replace(';', '_SC_', $escaped);
            $escaped = str_replace(':', '_C_', $escaped);
            $escaped = str_replace('&', '_AMP_', $escaped);
            $escaped = str_replace('(', '_LR_', $escaped);
            $escaped = str_replace(')', '_RR_', $escaped);
            $escaped = str_replace('[', '_LC_', $escaped);
            $escaped = str_replace(']', '_RC_', $escaped);
            $escaped = str_replace('}', '_CR_', $escaped);
            $escaped = str_replace('{', '_CL_', $escaped);
            $escaped = str_replace(',', '_COMMA_', $escaped);
            $escaped = str_replace('<', '_LESS_', $escaped);
            $escaped = str_replace('>', '_BIG_', $escaped);
            $escaped = str_replace('-', '_DASH_', $escaped);
            $escaped = str_replace('+', '_PLUS_', $escaped);
            $escaped = str_replace('`', '_TXT_', $escaped);
            $escaped = str_replace('=', '_EQ_', $escaped);
            return $escaped;
        }

        // RETURN A STRING TO ITS ORIGINAL FORM AFTER GETTING IT FROM THE DATABASE
        public static function SqlReescapeString(string $string) : string 
        {
            if (empty($string)) return '';
            $hash = 'panS024sdf2r1qpmy5zk2@1!%^$3$%FdlsjGkJsf5sdf7sdgp784w862)erfkuA44^!$F&ksdfiwlgfpyb05sdf1l5w1aE(*@RTTnvaiuleqpfhbsysmkwmauxplgrbazGR(@R)hW';
            $hash = hash('sha256', $hash);
            $hash = substr($hash, 0, 10);
            if (strpos($string, '_') === false && strpos($string, $hash) === false) {
                return $string;
            }
            $escaped = str_replace('_DQ_', '"', $string);
            $escaped = str_replace('_SQ_', "'", $escaped);
            $escaped = str_replace('_BS_', '\\', $escaped);
            $escaped = str_replace('_M_', '%', $escaped);
            $escaped = str_replace('_QM_', '?', $escaped);
            $escaped = str_replace('_P_', '.', $escaped);
            $escaped = str_replace('_D_', '$', $escaped);
            $escaped = str_replace('_AT_', '@', $escaped);
            $escaped = str_replace('_S_', '*', $escaped);
            $escaped = str_replace('_H_', '^', $escaped);
            $escaped = str_replace('_HASH_', '#', $escaped);
            $escaped = str_replace('_RM_', '!', $escaped);
            $escaped = str_replace('_AB_', '~', $escaped);
            $escaped = str_replace('_SC_', ';', $escaped);
            $escaped = str_replace('_C_', ':', $escaped);
            $escaped = str_replace('_AMP_', '&', $escaped);
            $escaped = str_replace('_LR_', '(', $escaped);
            $escaped = str_replace('_RR_', ')', $escaped);
            $escaped = str_replace('_LC_', '[', $escaped);
            $escaped = str_replace('_RC_', ']', $escaped);
            $escaped = str_replace('_CR_', '}', $escaped);
            $escaped = str_replace('_CL_', '{', $escaped);
            $escaped = str_replace('_COMMA_', ',', $escaped);
            $escaped = str_replace('_LESS_', '<', $escaped);
            $escaped = str_replace('_BIG_', '>', $escaped);
            $escaped = str_replace('_DASH_', '-', $escaped);
            $escaped = str_replace('_PLUS_', '+', $escaped);
            $escaped = str_replace('_TXT_', '`', $escaped);
            $escaped = str_replace('_EQ_', '=', $escaped);
            $escaped = str_replace($hash, '_', $escaped);
            return $escaped;
        }
    }

?>