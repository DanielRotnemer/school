<?php

    date_default_timezone_set("Asia/Jerusalem");
    session_start();
    require_once('C:\xampp\htdocs\NewSite\utilities\utilities.php');

    if (isset($_GET['report']))
    {
        $currentDateTime = date("Y-m-d H:i:s");
        $monthStart = date('Y-m-01').' 00:00:00'; 
        $monthEndUnix = date('Y-m-t').' 23:59:59'; 
        $monthStartDay = substr(date('01-m-Y'), 0, strpos(date('01-m-Y'), '-'));
        $monthEndDay = substr(date('t-m-Y'), 0, strpos(date('t-m-Y'), '-'));
        $monthStartDate = date('Y-m-01'); 
        $monthEnd = date("t/m/Y");
        $monthEndDate = date("Y-m-t");
        $currentDate = date('d/m/Y');

        $fromDateUnix = isset($_GET['from']) ? $_GET['from'] : date('T-m-01');
        $toDateUnix = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');

        $fromDate = str_replace('-', '/', $fromDateUnix);
        $fromDate = date('d/m/Y', strtotime($fromDate));
        $toDate = str_replace('-', '/', $toDateUnix);
        $toDate = date('d/m/Y', strtotime($toDate));
        
        if ($_GET['report'] == 'attendance')
        {
            $dataTable = 
            '<table>
                <tr>
                    <th colspan="4">דוח נוכחות לחוגים שהסתיימו מ: '.$fromDate.' עד : '.$toDate.'</th>
                </tr>
                <tr>
                    <th colspan="4">תקף ל: '.date('d/m/Y').'</th>
                </tr>
            </table>';

            // GET THE CIRCLES THAT ARE ALREADY FINISHED THIS MONTH
            $to = $toDateUnix.' 23:59:59';
            $from = $fromDateUnix.' 00:00:00';
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM circles_table WHERE BINARY EndDate<=? AND StartDate>=? ORDER BY StartDate ASC");
            $stmt->execute([$to, $from]); 
            $finishedCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            $pdo = null;

            if (count($finishedCirclesResult) > 0)
            {
                foreach ($finishedCirclesResult as $fcKey => $fcValue)
                {
                    $dataTable .= '<table>
                    <tr><td colspan="4"></td></tr>';
    
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
    
                    $dataTable .=
                    '<tr>
                        <th colspan="4">שם החוג: '.$fcValue['CircleName'].', בהדרכת: '.$instructorName.'.<th>
                    </tr>
                    <tr>
                        <th colspan="4">מ: '.$startDate.' עד: '.$endDate.'.</th>
                    </tr>
                    <tr>                
                        <th colspan="2">משתתפים</th>
                        <th colspan="2">מפגשים</th>
                    </tr>';
    
                    // SELECT ALL THE MEETINGS OF THIS CIRCLE
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT * FROM circle_meetings WHERE BINARY CircleId=?");
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
    
                        $meetingParticipants = '';
                        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                        foreach ($meetingParticipantsResult as $mpKey => $mpValue)
                        {
                            if (!in_array($mpValue['StudentId'], $circleParticipantsArray)) {
                                array_push($circleParticipantsArray, $mpValue['StudentId']);
                            }
                            
                            $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM students WHERE BINARY StudentId=?");
                            $stmt->execute([$mpValue['StudentId']]); 
                            $studentResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($studentResult) > 0)
                            {
                                $comma = $meetingParticipants == '' ? '' : ',';
                                $meetingParticipants .= $comma.' ';
                                $meetingParticipants .= $studentResult[0]['FirstName'].' '.$studentResult[0]['LastName'].' - '.$studentResult[0]['IdNumber'];
                            }  
                            else 
                            {
                                $comma = $meetingParticipants == '' ? '' : ',';
                                $meetingParticipants .= $comma.' ';
                                $meetingParticipants .= 'התלמיד לא קיים במערכת';
                            }                      
                        }
                        $pdo = null;
    
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
                                                
                        $dataTable .=
                        '<tr>
                            <td colspan="2">מספר משתתפים: '.count($meetingParticipantsResult).'. '.$meetingParticipants.'</td>
                            <td colspan="2">
                            '.$meetingWeekDay.' - '.$meetingDate.'
                            מ: '.$meetingStartHour.' עד: '.$meetingEndHour.'
                            </td>
                        </tr>';
                    }
    
                    $dataTable .= 
                    '<tr>
                        <th colspan="4">סך הכל '.count($circleParticipantsArray).' תלמידים נרשמו לחוג זה.</th>
                    </tr>';
    
                    $dataTable .= 
                    '</table>';
                }
            } 
            else
            {
                $dataTable .= 
                '<table>
                    <tr><td colspan="4"></td></tr>
                    <tr>
                        <td colspan="4">עדיין לא ניתן להפיק דוח נוכחות עבור חוגים</td>
                    </tr>
                </table>';
            } 
            
            $dataTable .= 
            '<table>
                <tr><td colspan="4"></td></tr>
                <tr>
                    <th colspan="4">משובים לחוגים שהסתיימו מ: '.$fromDate.' עד : '.$toDate.'</th>
                </tr>
            </table>';

            if (count($finishedCirclesResult) > 0)
            {
                foreach ($finishedCirclesResult as $fcKey => $fcValue)
                {
                    // SELECT COMMENTS FOR THIS CIRCLE
                    $pdo = UTILITIES::PDO_DB_Connection('school_circles');
                    $stmt = $pdo->prepare("SELECT * FROM circle_comments_table WHERE BINARY CircleId=?");
                    $stmt->execute([$fcValue['CircleId']]); 
                    $circleCommentsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null;

                    // SELECT INSTRUCTOR DETAILS
                    $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                    $stmt = $pdo->prepare("SELECT * FROM instructors WHERE BINARY InstructorId=?");
                    $stmt->execute([$fcValue['CircleInstructorId']]); 
                    $circleInstructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $pdo = null;

                    $instructorDetails = 'המדריך לא קיים במערכת';
                    if (count($circleInstructorResult) > 0) {
                        $instructorDetails = $circleInstructorResult[0]['FirstName'].' '.$circleInstructorResult[0]['LastName'].' - '.$circleInstructorResult[0]['IdNumber'];
                    }       
                    
                    $startDate = explode(' ', $fcValue['StartDate'])[0];
                    $startDate = str_replace('-', '/', $startDate);
                    $startDate = date('d/m/Y', strtotime($startDate));
                    $startDate = explode(' ', $fcValue['StartDate'])[1].' '.$startDate;
    
                    $endDate = explode(' ', $fcValue['EndDate'])[0];
                    $endDate = str_replace('-', '/', $endDate);
                    $endDate = date('d/m/Y', strtotime($endDate));
                    $endDate = explode(' ', $fcValue['EndDate'])[1].' '.$endDate;

                    $dataTable .=
                    '<table>
                        <tr><td colspan="4"></td></tr>
                        <tr>
                            <th colspan="4">משובים לחוג '.$fcValue['CircleName'].' בהנחיית: '.$instructorDetails.'</th>
                        </tr>
                        <tr>
                            <th colspan="4">מ: '.$startDate.' עד: '.$endDate.'.</th>
                        </tr>
                    </table>';

                    if (count($circleCommentsResult) > 0)
                    {
                        $dataTable .= '<table>';
                        $dataTable .=
                        '<tr>    
                            <th>אודות</th>
                            <th>דירוג</th>
                            <th>משוב</th>            
                            <th>מגיש המשוב</th>                            
                        </tr>';

                        foreach ($circleCommentsResult as $ccKey => $ccValue)
                        {
                            // SELECT THE DETAILS OF THE COMMENTER
                            $commenterDetails = '';
                            $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                            if (intval($ccValue['FromInstructorId']) != -1) {
                                $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM instructors WHERE BINARY InstructorId=?");
                                $stmt->execute([$ccValue['FromInstructorId']]);
                                $commenterDetails = 'המדריך לא קיים במערכת';
                            }
                            else {
                                $stmt = $pdo->prepare("SELECT FirstName,LastName,IdNumber FROM students WHERE BINARY StudentId=?");
                                $stmt->execute([$ccValue['FromStudentId']]);
                                $commenterDetails = 'התלמיד לא קיים במערכת';
                            }                             
                            $commenterDetailsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $pdo = null;

                            if (count($commenterDetailsResult) > 0) 
                            {
                                $commenterDetails = '';
                                if (intval($ccValue['FromInstructorId']) != -1) {
                                    $commenterDetails = 'מדריך החוג - ';
                                }
                                $commenterDetails .= $commenterDetailsResult[0]['FirstName'].' '.$commenterDetailsResult[0]['LastName'].' - '.$commenterDetailsResult[0]['IdNumber'];
                            }

                            $studentData = 'לא רלוונטי';
                            if (intval($ccValue['OnStudentId']) != -1)
                            {
                                // SELECT THE STUDENT DETAILS
                                $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                                $stmt = $pdo->prepare("SELECT IdNumber,FirstName,LastName FROM students WHERE BINARY StudentId=?");
                                $stmt->execute([$ccValue['OnStudentId']]); 
                                $studentResult = $stmt->fetchAll(PDO::FETCH_ASSOC);  
                                $pdo = null;

                                $studentData = 'אודות התלמיד: התלמיד לא קיים במערכת';
                                if (count($studentResult) > 0) {
                                    $studentData = 'אודות התלמיד: '.$studentResult[0]['FirstName'].' '.$studentResult[0]['LastName'].' - '.$studentResult[0]['IdNumber'];
                                }
                            }

                            $dataTable .=
                            '<tr>
                                <td>'.$studentData.'</td>
                                <td>'.$ccValue['Rate'].'</td>
                                <td>'.$ccValue['Comment'].'</td>
                                <td>'.$commenterDetails.'</td> 
                            </tr>';
                        }

                        $dataTable .= '</table>';
                    }
                    else
                    {
                        $dataTable .= 
                        '<table>
                            <tr>
                                <td colspan="4">לא קיימים משובים במערכת עבור חוג זה</td>
                            </tr>
                        </table>';
                    }
                }
            }
            else
            {
                $dataTable .= 
                '<table>
                    <tr>
                        <td colspan="4">עדיין לא הסתיימו חוגים, לא קיימים משובים</td>
                    </tr>
                </table>';
            } 

            $filename = 'דוח נוכחות חוגים מ: '.$fromDate.' עד: '.$toDate;
            $filename .= '.xls';
            header('Content-Type: application/xls');
            header('Content-Disposition: attachment; filename='.$filename);
            echo $dataTable;
            exit;
        }
        else if ($_GET['report'] == 'allExpenses')
        {
            $dataTable = 
            '<table>
                <tr>
                    <th colspan="4">דוח הוצאות מערכת מ: '.$fromDate.' עד : '.$toDate.'</th>
                </tr>
                <tr>
                    <th colspan="4">תקף ל: '.date('d/m/Y').'</th>
                </tr>
            </table>';
            
            // GET THE CURRENT BUDGET FROM THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
            $stmt = $pdo->prepare("SELECT * FROM total_budget");
            $stmt->execute(); 
            $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalBudget = floatval($totalBudgetResult[0]['TotalSchoolBudget']);

            // SELECT THE MONTHLY SALARIES
            $salariesEndDate = strtotime($toDateUnix.' 23:59:59') >= strtotime(date('Y-m-d H:i:s')) ? date("Y-m-t") : $toDateUnix;
            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
            $stmt = $pdo->prepare("SELECT * FROM instructors_salaries WHERE BINARY Date>=? AND Date<=? ORDER BY Date ASC");
            $stmt->execute([$fromDateUnix, $salariesEndDate]); 
            $salariesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // SELECT ALL EVENT EXPENSES FOR THIS DATE RANGE
            $stmt = $pdo->prepare("SELECT * FROM events_budget WHERE BINARY ExpenseDate>=? ORDER BY ExpenseDate ASC");
            $stmt->execute([$monthStart]);
            $eventExpensesResults = $stmt->fetchAll(PDO::FETCH_ASSOC); 

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
            
            $sumInstructors = 0;
            $monthlySumInstructors = 0;

            $dataTable .=
            '<table>
                <tr><td colspan="4"></td></tr>
                <tr>                    
                    <td colspan="2">'.$totalBudget.'</td>
                    <td colspan="2">תקציב המערכת הנוכחי</td>
                </tr>
                <tr><td colspan="4"></td></tr>
            </table>';

            $dataTable .=
            '<table>
                <tr>
                    <th colspan="4">דו"ח הוצאות עבור הוצאות כלליות</th>
                </tr>
                <tr>
                    <th>הוצאה</th>
                    <th>תיאור</th>
                    <th>עלות</th>
                    <th>תאריך</th>
                </tr>';
                $sumGeneral = 0;
                $sumGeneralMonthly = 0;
                if (count($monthlyExpensesResult) > 0)
                {
                    foreach ($monthlyExpensesResult as $merKey => $merValue)
                    {
                        $expenseDate = str_replace('-', '/', $merValue['ExpenseDate']);
                        $expenseDate = date('d/m/Y', strtotime($expenseDate));  

                        $dataTable .=
                        '<tr>
                            <td>'.$merValue['ExpenseName'].'</td>
                            <td>'.$merValue['ExpenseDescription'].'</td>
                            <td>'.$merValue['ExpenseAmount'].'</td>
                            <td>'.$expenseDate.'</td>
                        </tr>';
                        $sumGeneral += floatval($merValue['ExpenseAmount']);
                        if (strtotime($merValue['ExpenseDate'].' 00:00:00') >= strtotime($monthStart)) {
                            $sumGeneralMonthly += floatval($merValue['ExpenseAmount']);
                        }
                    }
                    $dataTable .=
                    '<rt>
                        <th colspan="4">סה"כ הוצאות כלליות: '.$sumGeneral.' שקלים</th>
                    </tr>';
                }
                else 
                {
                    $dataTable .=
                    '<tr>
                        <th colspan="4">לא נמצאו הוצאות כלליות</th>
                    </tr>';
                }
                $dataTable .=
                '<tr><td colspan="4"></td></tr>
            </table>';

            $dataTable .=
            '<table>
                <tr>
                    <th colspan="4">דו"ח הוצאות עבור מדריכים</th>
                </tr>
                <tr>
                    <th>הוצאה</th>
                    <th>תיאור</th>
                    <th>עלות</th>
                    <th>תאריך</th>
                </tr>';

                if (count($salariesResult) > 0)
                {
                    foreach ($salariesResult as $sKey => $sValue)
                    {
                        // SELECT THE DETAILS OF THE INSTRUCTOR
                        $pdo = UTILITIES::PDO_DB_Connection('school_entities');
                        $stmt = $pdo->prepare("SELECT InstructorId,FirstName,LastName,IdNumber FROM instructors WHERE BINARY InstructorId=?");
                        $stmt->execute([$sValue['InstructorId']]); 
                        $instructorResult = $stmt->fetchAll(PDO::FETCH_ASSOC);     
                        
                        $instructorDetails = 'לא קיים במערכת';
                        if (count($instructorResult) > 0) {
                            $instructorDetails = $instructorResult[0]['FirstName'].' '.$instructorResult[0]['LastName'].' - '.$instructorResult[0]['IdNumber'];
                        }

                        $expenseDate = str_replace('-', '/', $sValue['Date']);
                        $expenseDate = date('d/m/Y', strtotime($expenseDate));

                        $dataTable .=
                        '<tr> 
                            <td>משכורת מדריך לפי שעות</td>
                            <td>משכורת חודשית עבור המדריך: '.$instructorDetails.'</td>
                            <td>'.$sValue['Salary'].'</td>
                            <td>'.$expenseDate.'</td>
                        </tr>';
                        $sumInstructors += floatval($sValue['Salary']);
                        if ($sValue['Date'] == date('Y-m-t')) {
                            $monthlySumInstructors += floatval($sValue['Salary']);
                        }
                    }
                    $dataTable .=
                    '<tr>
                        <th colspan="4">סה"כ משכורות מדריכים: '.$sumInstructors.' שקלים</td>
                    </div>';
                } 
                else
                {
                    $dataTable .= 
                    '<tr>
                        <th colspan="4">עדיין לא קיימים מדריכים במערכת.</th>
                    </tr>';
                }    

                $dataTable .=
                '<tr><td colspan="4"></td></tr>
            </table>';

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

            $dataTable .=
            '<table>
                <tr>
                    <th colspan="4">סיכום ההוצאות</th>
                </tr>
                <tr>
                    <th colspan="4">סך כל ההוצאות עבור מדריכים והוצאות כלליות מ - '.$fromDate.' עד - '.$toDate.': '.($sumGeneral + $sumInstructors).' שקלים</th>
                </tr>
                <tr>
                    <th colspan="4">סך כל ההוצאות המתוכננות לחודש זה נכון להיום ('.date('d/m/Y').'): '.$totalExpenses.' שקלים</th>
                </tr>
                <tr>
                    <th colspan="4">שארית התקציב הכללי לאחר ניכוי ההוצאות עבור חודש זה: '.$remainingBudget.' שקלים</th>
                </tr>
            </table>';

            $filename = 'דוח הוצאות מערכת מ: '.$fromDate.' עד: '.$toDate;
            $filename .= '.xls';
            header('Content-Type: application/xls');
            header('Content-Disposition: attachment; filename='.$filename);
            echo $dataTable;
            exit;
        }
        else if ($_GET['report'] == 'circleExpenses')
        {
            // SELECT THE PERMANENT CIRCLE NAMES
            $pdo = UTILITIES::PDO_DB_Connection('school_circles');
            $stmt = $pdo->prepare("SELECT * FROM permanent_circles");
            $stmt->execute();
            $permanentCirclesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            // GET THE CURRENT BUDGET FROM THE DATABASE
            $pdo = UTILITIES::PDO_DB_Connection('school_budgets');
            $stmt = $pdo->prepare("SELECT * FROM total_budget");
            $stmt->execute(); 
            $totalBudgetResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalBudget = floatval($totalBudgetResult[0]['TotalSchoolBudget']);
            $pdo = null;

            $dataTable = 
            '<table>
                <tr>
                    <th colspan="4">דוח הוצאות עבור חוגים מ: '.$fromDate.' עד : '.$toDate.'</th>
                </tr>
                <tr>
                    <th colspan="4">תקף ל: '.date('d/m/Y').'</th>
                </tr> 
            </table>';

            $dataTable .=
            '<table>
                <tr><td colspan="4"></td></tr>
                <tr>                    
                    <td colspan="2">'.$totalBudget.'</td>
                    <td colspan="2">תקציב המערכת הנוכחי</td>
                </tr>
                <tr><td colspan="4"></td></tr>
            </table>';

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

                    $dataTable .= '<table>';

                    $dataTable .=
                    '<tr>
                        <th colspan="4">שם החוג: '.$pcValue['PermanentCircleName'].'</th>
                    </tr>
                    <tr>
                        <th>הוצאה</th>
                        <th>תיאור</th>
                        <th>עלות</th>
                        <th>תאריך</th>
                    </tr>';

                    if (count($circlesBudgetsResult) > 0)
                    {
                        foreach ($circlesBudgetsResult as $cbKey => $cbValue)
                        {
                            $expenseDate = str_replace('-', '/', $cbValue['ExpenseDate']);
                            $expenseDate = date('d/m/Y', strtotime($expenseDate));

                            $dataTable .=
                            '<tr>
                                <td>'.$cbValue['ExpenseName'].'</td>
                                <td>'.$cbValue['ExpenseDescription'].'</td>
                                <td>'.$cbValue['ExpenseAmount'].'</td>
                                <td>'.$expenseDate.'</td>                                
                            </tr>';
                            $circleExpensesSum += floatval($cbValue['ExpenseAmount']);
                        } 
                        $totalCirclesExpenses += $circleExpensesSum; 
                        $dataTable .= 
                        '<tr>
                            <th colspan="4">סיכום ההוצאות עבור חוג זה לתקופה הנ"ל: '.$circleExpensesSum.' שקלים</th>
                        </tr>';
                    }
                    else
                    {
                        $dataTable .=
                        '<tr>
                            <th colspan="4">לא נקבעו הוצאות עבור חוג זה לתקופה הנ"ל</th>
                        </tr>';
                    }
                    $dataTable .= 
                    '<tr><td colspan="4"></td></tr>
                    </table>';
                }
                $dataTable .= 
                '<table>
                    <tr>
                        <th colspan="4">סיכום ההוצאות עבור חוגים מ '.$fromDate.' עד '.$toDate.': '.$totalCirclesExpenses.' שקלים</th>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                </table>';
            }
            else 
            {
                $dataTable .= 
                '<table>
                    <tr>
                        <th colspan="4">עדיין לא קיימים חוגים במערכת</th>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                </table>';
            }

            $filename = 'דוח הוצאות חוגים מ: '.$fromDate.' עד: '.$toDate;
            $filename .= '.xls';
            header('Content-Type: application/xls');
            header('Content-Disposition: attachment; filename='.$filename);
            echo $dataTable;
            exit;
        }
        else if ($_GET['report'] == 'eventExpenses')
        {
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
            $totalBudget = floatval($totalBudgetResult[0]['TotalSchoolBudget']);

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

            $dataTable = 
            '<table>
                <tr>
                    <th colspan="4">דוח הוצאות עבור אירועים מ: '.$fromDate.' עד : '.$toDate.'</th>
                </tr>
                <tr>
                    <th colspan="4">תקף ל: '.date('d/m/Y').'</th>
                </tr> 
            </table>';

            $dataTable .=
            '<table>
                <tr><td colspan="4"></td></tr>
                <tr>                    
                    <td colspan="2">'.$totalBudget.'</td>
                    <td colspan="2">תקציב המערכת הנוכחי</td>
                </tr>
                <tr><td colspan="4"></td></tr>
            </table>';

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

                    $dataTable .= '<table>';

                    $dataTable .=
                    '<tr>
                        <th colspan="4">שם האירוע: '.$eventName.'</th>
                    </tr>
                    <tr>
                        <th>הוצאה</th>
                        <th>תיאור</th>
                        <th>עלות</th>
                        <th>תאריך</th>
                    </tr>';

                    $eventExpensesSum = 0;
                    if (count($eValue) > 0)
                    {
                        foreach ($eValue as $exKey => $exValue)
                        {
                            $expenseDate = str_replace('-', '/', $exValue['ExpenseDate']);
                            $expenseDate = date('d/m/Y', strtotime($expenseDate));

                            $dataTable .=
                            '<tr>
                                <td>'.$exValue['ExpenseName'].'</td>
                                <td>'.$exValue['ExpenseDescription'].'</td>
                                <td>'.$exValue['ExpenseAmount'].'</td>
                                <td>'.$expenseDate.'</td>
                            </tr>';
                            $eventExpensesSum += floatval($exValue['ExpenseAmount']);
                        } 
                        $totalEventsExpenses += $eventExpensesSum; 
                        $dataTable .= 
                        '<tr>
                            <th colspan="4">סיכום ההוצאות עבור אירוע זה לתקופה הנ"ל: '.$eventExpensesSum.' שקלים</th>
                        </tr>';
                    }
                    else
                    {
                        $dataTable .=
                        '<tr>
                            <th colspan="4">לא נקבעו הוצאות עבור אירוע זה לתקופה הנ"ל</th>
                        </tr>';
                    }
                    $dataTable .= 
                    '<tr><td colspan="4"></td></tr>
                    </table>';
                }
                $dataTable .= 
                '<table>
                    <tr>
                        <th colspan="4">סיכום ההוצאות עבור אירועים מ '.$fromDate.' עד '.$toDate.': '.$totalEventsExpenses.' שקלים.</th>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                </table>';
            }
            else 
            {
                $dataTable .= 
                '<table>
                    <tr>
                        <th colspan="4">לא נמצאו הוצאות עבור אירועים בתאריכים המבוקשים</th>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                </table>';
            }

            $filename = 'דוח הוצאות אירועים מ: '.$fromDate.' עד: '.$toDate;
            $filename .= '.xls';
            header('Content-Type: application/xls');
            header('Content-Disposition: attachment; filename='.$filename);
            echo $dataTable;
            exit;
        }
    }

?>