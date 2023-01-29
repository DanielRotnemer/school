function getManagerStudentsListScreen()
{
    var studentsScreen = 
    `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">רשימת התלמידים במערכת</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="loading">...טוען נתונים</div>
            </div>
            <div class="ssc-footer-w">
                <div class="ssc-action-btn animated-transition noselect"><a>סגור/י</a></div>
            </div>
        </div>
    </div>`;
    return studentsScreen;
}

function getManagerInstructorsListScreen()
{
    var instructorsScreen = 
    `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">רשימת המדריכים במערכת</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="loading">...טוען נתונים</div>
            </div>
            <div class="ssc-footer-w">
                <div class="ssc-action-btn animated-transition noselect"><a>סגור/י</a></div>
            </div>
        </div>
    </div>`;
    return instructorsScreen;
}

function getInstructorExpensesScreen()
{
    if (instructorSalaries.length == 0)
    {
        alert("לא קיימים מדריכים במערכת, לא ניתן להגדיר משכורות עבור מדריכים");
        return;
    }

    var instructorExpensesScreen = 
    `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">הוספת משכורת חודשית עבור מדריכים</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">            
                <div class="ssc-field" style="margin-top: 0;">
                    <div class="ssc-field-name">בחר/י&nbsp;מדריך</div>
                    <select id="selectedInstructorSalary" class="ssc-field-value">`;
                    for (let i = 0; i < instructorSalaries.length; i++)
                    {                            
                        instructorExpensesScreen +=
                        `<option>` + instructorSalaries[i][0] + `</option>`;                    
                    }
                    instructorExpensesScreen += 
                    `</select>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">שעות עבודה</div>
                    <input type="text" class="ssc-field-value" id="instructorHours"/>
                </div> 
                <div class="ssc-field">
                    <div class="ssc-field-name">שכר לשעה</div>
                    <input type="text" class="ssc-field-value" id="instructorHourSalary"/>
                </div>                
            </div>
            <div class="ssc-footer-w">
                <div id="setSalaries" class="ssc-disabled-action-btn animated-transition noselect"><a>עדכן/י</a></div>
            </div>
        </div>
    </div>`;
    return instructorExpensesScreen;
}

function getManagerMessagesScreen()
{
    var messagesScreen = 
    `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">ניהול ההודעות</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="loading">...טוען נתונים</div>
            </div>
            <div class="ssc-footer-w">
                <div class="ssc-action-btn animated-transition noselect"><a>סגור/י</a></div>
            </div>
        </div>
    </div>`;
    return messagesScreen;
}