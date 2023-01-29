var resizeTime;
var resizeTimeout = false;
var resizeDelta = 200; 
var animationFinished = true; 
var domain = "localhost/NewSite";
var dayToAddMeeting = -1;
var indexOfEditedMeeting = -1;
var changedMeetingsIds = [];
var removedMeetingsIds = [];

function resize()
{
    if ($(".user-profile-menu-w").length)
    {
        $(".user-profile-menu-w").css("left", (100 + ($(".header-login").width() / 2) - ($(".user-profile-menu-w").width() / 2)) + "px");
    }

    if ($(".circle-field-value").length)
    {
        for (let i = 0; i < $(".circle-field-value").length; i++)
        {
            let width = $($(".circle-field-value")[i]).parent().width() - 10 - $($(".circle-field-txt")[i]).width();
            $($(".circle-field-value")[i]).css("width", width + "px");
        }
    }    

    if ($(".secondary-screen-w").length)
    {
        $(".secondary-screen-w").css("height", window.innerHeight + "px");
        $(".secondary-screen-w").css("width", window.innerWidth + "px");
        $(".secondary-screen-w").css("margin-top", $(window).scrollTop() + "px");
    } 

    if ($(".ssc-field").length)
    {
        for (let i = 0; i < $(".ssc-field").length; i++)
        {
            let parent = $($(".ssc-field")[i]);
            let width = parent.width() - 10 - parent.find(".ssc-field-name").width();
            parent.find(".ssc-field-value").css("width", width + "px");
        }
    }
    
    if ($(".new-meeting-field").length)
    {
        for (let i = 0; i < $(".new-meeting-field-value").length; i++)
        {
            let width = $($(".new-meeting-field-value")[i]).parent().width() - 10 - $($(".new-meeting-field-name")[i]).width();
            $($(".new-meeting-field-value")[i]).css("width", width + "px");
        }
    }
}

function addEvents()
{
    if ($(".header-login > a[logged]").length)
    {
        $(".header-login > a[logged]").off("click");
        $(".header-login > a[logged]").on("click", function(event) 
        {
            if (event.stopPropagation) event.stopPropagation();
            if (event.preventDefault) event.preventDefault();
            if (animationFinished)
            {
                animationFinished = false;
                var display = $(".user-profile-menu-w").css("display");
                if (display == "block")
                {
                    $(".user-profile-menu-w").animate({opacity: 0}, 500, function() {
                        $(".user-profile-menu-w").css("display", "none");
                        animationFinished = true;
                    });
                }
                else 
                {
                    $(".user-profile-menu-w").css("display", "block");
                    $(".user-profile-menu-w").animate({opacity: 1}, 500, function() {
                        animationFinished = true;
                    });
                }                
            }           
        });

        $("html").on("click", function()
        {
            animationFinished = false;
            $(".user-profile-menu-w").animate({opacity: 0}, 500, function() {
                $(".user-profile-menu-w").css("display", "none");
                animationFinished = true;
            });
        });
    }

    if ($(".sch-button").length)
    {
        $(".sch-button").off("click");
        $(".sch-button").on("click", function()
        {
            var index = $(this).parent().parent().index("[day]");
            dayToAddMeeting = index;
            indexOfEditedMeeting = -1;
            $(".selected-sch-options-w").css("display", "none");
            $("input[type=checkbox]").prop("checked", false);
            var addMeetingScreen = getAddMeetingScreen();
            $("body").append(addMeetingScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({opacity: 1}, 400, function() 
            {
                animationFinished = true;
                addSecondaryScreenEvents();
            });
        });
    }

    $("#updateCircle").off("click");
    $("#updateCircle").on("click", function()
    {
        var lrd = $("#lastRegistrationDate").length > 0 ? $("#lastRegistrationDate").val() : "none";
        var lrt = $("#lastRegistrationTime").length > 0 ? $("#lastRegistrationTime").val() : "none";
        $.ajax
        ({
            url         : "http://" + domain + "/pHandler",
            type        : "POST",                                                      
            data        : 
            {
                circleInstructorName: $("#circleInstructorName").val(),
                lastRegistrationDate: lrd,
                lastRegistrationTime: lrt,
                circleSchedule: JSON.stringify(circleMeetings),
                chMeetingIds: JSON.stringify(changedMeetingsIds),
                rmMeetingIds: JSON.stringify(removedMeetingsIds),
                finMeetings: JSON.stringify(finishedMeetings),
                editCircleId: edCircleId
            },                             
            success     : function(output)
            {
                if (output.substring(0, 7) == 'CONFIRM') 
                {
                    output = output.split("CONFIRM").join("");
                    $("body").append(output);
                    $("body").css("overflow", "hidden");
                    resize();
                    animationFinished = false;
                    $(".secondary-screen-w").animate({opacity: 1}, 400, function() 
                    {
                        animationFinished = true;
                        addSecondaryScreenEvents();
                    });
                }
                else if (output == 'החוג עודכן בהצלחה') {
                    window.location.replace("http://" + domain + "/index");
                    //location.reload();
                }
                else if (output != '') {
                    alert(output);
                }
                else {
                    alert("עדכון החוג נכשל, אנא נסה/י מאוחר יותר");
                }
            }
        });
    });

    if ($("#managerStudentsList").length)
    {
        $("#managerStudentsList").off("click");
        $("#managerStudentsList").on("click", function()
        {
            var managerStudentsListScreen = getManagerStudentsListScreen();
            $("body").append(managerStudentsListScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({opacity: 1}, 400, function() 
            {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    listType: 'studentsForManager'
                },                             
                success     : function(output)
                {
                    if (output.substring(0, 7) == "SUCCESS") 
                    {
                        output = output.substring(7);
                        $(".ssc-body-w").find(".loading").remove();
                        $(".ssc-body-w").append(output);
                        $(".ssc-footer-w > div").on("click", function() {
                            $(".secondary-screen-w").click();
                        });
                        addSecondaryScreenEvents();
                    } 
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                    }
                }
            });
        });
    }

    if ($("#managerInstructorsList").length)
    {
        $("#managerInstructorsList").off("click");
        $("#managerInstructorsList").on("click", function()
        {
            var managerInstructrsListScreen = getManagerInstructorsListScreen();
            $("body").append(managerInstructrsListScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({opacity: 1}, 400, function() 
            {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    listType: 'instructorsForManager'
                },                             
                success     : function(output)
                {
                    if (output.substring(0, 7) == "SUCCESS") 
                    {
                        output = output.substring(7);
                        $(".ssc-body-w").find(".loading").remove();
                        $(".ssc-body-w").append(output);
                        $(".ssc-footer-w > div").on("click", function() {
                            $(".secondary-screen-w").click();
                        });
                        addSecondaryScreenEvents();
                    } 
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                    }
                }
            });
        });
    }

    if ($("#manageManagerMessages").length)
    {
        $("#manageManagerMessages").off("click");
        $("#manageManagerMessages").on("click", function()
        {
            var managerMessagesScreen = getManagerMessagesScreen();
            $("body").append(managerMessagesScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({opacity: 1}, 400, function() 
            {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    listType: 'messagesOfManager'
                },                             
                success     : function(output)
                {
                    if (output.substring(0, 7) == "SUCCESS") 
                    {
                        output = output.substring(7);
                        $(".ssc-body-w").find(".loading").remove();
                        $(".ssc-body-w").append(output);
                        $(".ssc-footer-w > div").on("click", function() {
                            $(".secondary-screen-w").click();
                        });
                        addSecondaryScreenEvents();
                    } 
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                    }
                }
            });
        });
    }

    if ($("#profile").length)
    {
        $("#profile").off("click");
        $("#profile").on("click", function()
        {
            var profileScreen = getProfileScreen();
            $("body").append(profileScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({opacity: 1}, 400, function() 
            {
                animationFinished = true;
                addSecondaryScreenEvents();
            });
        });
    }
}

function validTime(time)
{
    var splitted = time.split(":");
    if (splitted.length == 3)
    {
        for (let i = 0; i < splitted.length; i++) 
        {
            if (splitted[i].length != 2 || Number(splitted[i]) < 0) {
                return false;
            }            
        }
        if (Number(splitted[0]) >= 24 || Number(splitted[1]) >= 60 || Number(splitted[2]) >= 60) {
            return false;
        }
        return true;
    }
    return false;
}

function dateCompare(time1, time2) 
{
    var t1 = new Date();
    var parts = time1.split(":");
    t1.setHours(parts[0], parts[1], parts[2], 0);
    var t2 = new Date();
    parts = time2.split(":");
    t2.setHours(parts[0], parts[1], parts[2], 0);
  
    if (t1.getTime() > t2.getTime()) return 1;
    if (t1.getTime() < t2.getTime()) return -1;
    if (t1.getTime() == t2.getTime()) return 0;
}

function addSecondaryScreenEvents()
{
    $(".secondary-screen-w").on("click", function()
    {
        animationFinished = false;
        $(".secondary-screen-w").animate({opacity: 0}, 400, function() 
        {
            animationFinished = true;
            $(".secondary-screen-w").remove();
            $("body").css("overflow-x", "hidden");
            $("body").css("overflow-y", "auto");
        });
    });

    $(".secondary-screen").on("click", function(event) 
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();
    });

    $(".close-secondary-sc").on("click", function()
    {
        animationFinished = false;
        $(".secondary-screen-w").animate({opacity: 0}, 400, function() 
        {
            animationFinished = true;
            $(".secondary-screen-w").remove();
            $("body").css("overflow-x", "hidden");
            $("body").css("overflow-y", "auto");
        });
    });
    
    if ($("#newMeetingEnd").length && $("#newMeetingBegin").length) 
    {
        $("#newMeetingEnd, #newMeetingBegin").off("input");
        $("#newMeetingEnd, #newMeetingBegin").on("input", function()
        {
            var end = $("#newMeetingEnd").val();
            var start = $("#newMeetingBegin").val();
            if (validTime(end) && validTime(start)) {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
        });

        $("#addNewMeeting").off("click");
        $("#addNewMeeting").on("click", function() {
            addNewMeeting(); 
        });

        $("#editMeeting").off("click");
        $("#editMeeting").on("click", function()
        {
            var added = addNewMeeting();
            
            if (added == true)
            {
                for (let i = 0; i < $(".inner-meeting-w").length; i++)
                {
                    if ($($(".inner-meeting-w")[i]).find(".meeting-w").length > 0)
                    {
                        for (let j = 0; j < $($(".inner-meeting-w")[i]).find(".meeting-w").length; j++) {
                            $($($(".inner-meeting-w")[i]).find(".meeting-w")[j]).css("margin-top", "10px");
                        }
                        $($($(".inner-meeting-w")[i]).find(".meeting-w")[0]).css("margin-top", "0px");
                    }
                }
            }                           
        });
    }

    if ($(".ssc-field-value").length)
    {
        $(".ssc-field-value, textarea").off("input");
        $(".ssc-field-value, textarea").on("input", function()
        {
            var empty = false;
            for (let i = 0; i < $(".ssc-field-value").length; i++)
            {
                if ($($(".ssc-field-value")[i]).val() == "") 
                {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    empty = true;
                    break;
                }
            }
            for (let i = 0; i < $(".field-textarea").length; i++) 
            {
                if ($($(".field-textarea")[i]).val() == "") 
                {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    empty = true;
                    break;
                }
            }
            if (!empty) {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }
        });
    }

    if ($("#updateCircleMeetings").length)
    {
        $("#updateCircleMeetings").off("click");
        $("#updateCircleMeetings").on("click", function()
        {
            var lrd = $("#lastRegistrationDate").length > 0 ? $("#lastRegistrationDate").val() : "none";
            var lrt = $("#lastRegistrationTime").length > 0 ? $("#lastRegistrationTime").val() : "none";
            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    circleInstructorName: $("#circleInstructorName").val(),
                    lastRegistrationDate: lrd,
                    lastRegistrationTime: lrt,
                    circleSchedule: JSON.stringify(circleMeetings),
                    chMeetingIds: JSON.stringify(changedMeetingsIds),
                    rmMeetingIds: JSON.stringify(removedMeetingsIds),
                    finMeetings: JSON.stringify(finishedMeetings),
                    editCircleId: edCircleId,
                    confirmed: 'confirmed'
                },                             
                success     : function(output)
                {
                    if (output.substring(0, 7) == 'CONFIRM') 
                    {
                        output = output.split("CONFIRM").join("");
                        $("body").append(output);
                        $("body").css("overflow", "hidden");
                        resize();
                        animationFinished = false;
                        $(".secondary-screen-w").animate({opacity: 1}, 400, function() 
                        {
                            animationFinished = true;
                            addSecondaryScreenEvents();
                        });
                    }
                    else if (output == 'החוג עודכן בהצלחה') {
                        location.reload();
                    }
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("עדכון החוג נכשל, אנא נסה/י מאוחר יותר");
                    }
                }
            });
        });
    }

    if ($("[message]").length)
    {
        $("[message]").off("click");
        $("[message]").on("click", function()
        {            
            var parent = $(this).parent();
            var messageId = parent.attr("messageId");
            var act = $(this).attr("message");
            var button = $(this);

            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    actionMessageId: messageId,
                    action: act
                },                             
                success     : function(output)
                {
                    if (output == "הפעולה התבצעה בהצלחה") 
                    {
                        if (act == "activate") {
                            button.attr("message", "remove");
                            button.find("a").text("הסר/י");
                        }
                        else {
                            button.attr("message", "activate");
                            button.find("a").text("שחזר/י");
                        }
                    } 
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("הסרת התלמיד נכשלה, אנא נסה/י מאוחר יותר");
                    }
                }
            });
        });
    }

    if ($("[remove=student]").length)
    {
        $("[remove=student]").off("click");
        $("[remove=student]").on("click", function()
        {
            var parent = $(this).parent();
            var studentId = parent.attr("studentId");
            var container = parent.parent();
            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    removeStudentId: studentId
                },                             
                success     : function(output)
                {
                    if (output == "הסרת התלמיד התבצעה בהצלחה") 
                    {
                        parent.remove();
                        if (container.children().length == 0) {
                            container.append(`<div class="alt-text">לא קיימים תלמידים במערכת</div>`);
                        }
                    } 
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("הסרת התלמיד נכשלה, אנא נסה/י מאוחר יותר");
                    }
                }
            });
        });
    }

    if ($("[remove=instructor]").length)
    {
        $("[remove=instructor]").off("click");
        $("[remove=instructor]").on("click", function()
        {
            var parent = $(this).parent();
            var instructorId = parent.attr("instructorId");
            var container = parent.parent();
            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    removeInstructorId: instructorId
                },                             
                success     : function(output)
                {
                    if (output == "הסרת המדריך התבצעה בהצלחה") 
                    {
                        parent.remove();
                        if (container.children().length == 0) {
                            container.append(`<div class="alt-text">לא קיימים מדריכים במערכת</div>`);
                        }
                    } 
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("הסרת המדריך נכשלה, אנא נסה/י מאוחר יותר");
                    }
                }
            });
        });
    }

    if ($("#updateProfile").length)
    {
        $("input[type=text]").on("input", function()
        {
            if ($("#firstName").val() == "" || $("#lastName").val() == "" || $("#phone").val() == "" || $("#email").val() == "") {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }

            if ($("#firstName").val() != firstName || $("#lastName").val() != lastName || $("#phone").val() != phone || $("#email").val() != email) {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
        });

        $("#updateProfile").off("click");
        $("#updateProfile").on("click", function()
        {
            if ($("#firstName").val() == "" || $("#lastName").val() == "" || $("#phone").val() == "" || $("#email").val() == "") {
                return;
            }

            if ($("#firstName").val() == firstName && $("#lastName").val() == lastName && $("#phone").val() == phone && $("#email").val() == email) {
                return;
            }

            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    firstName: $("#firstName").val(),
                    lastName: $("#lastName").val(),
                    phone: $("#phone").val(),
                    email: $("#email").val()
                },                             
                success     : function(output)
                {
                    if (output == 'השינויים נשמרו בהצלחה') 
                    {
                        animationFinished = false;
                        $(".secondary-screen-w").animate({opacity: 0}, 400, function() 
                        {
                            animationFinished = true;
                            $(".secondary-screen-w").remove();
                            $("body").css("overflow-x", "hidden");
                            $("body").css("overflow-y", "auto");
                            location.reload(); 
                        });
                    } 
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("עדכון הפרופיל נכשל, אנא נסה/י מאוחר יותר");
                    }
                }
            });
        });
    }
}

function addNewMeeting()
{
    var end = $("#newMeetingEnd").val();
    var start = $("#newMeetingBegin").val();
    if (validTime(end) && validTime(start)) 
    {
        var localTime = new Date();
        var year = localTime.getFullYear();
        var month = localTime.getMonth() + 1;
        var day = localTime.getDate();
        var hours = localTime.getHours();
        var minutes = localTime.getMinutes();
        var seconds = localTime.getSeconds();
        var weekDay = localTime.getDay();
        var timeNow = hours + ':' + minutes + ':' + ':' + seconds;
        
        if (dateCompare(start, end) != -1) {
            alert("זמן סיום מפגש לפני זמן ההתחלה");
            return;
        }

        if (dayToAddMeeting >= weekDay)
        {
            if (dayToAddMeeting == weekDay)
            {
                if (dateCompare(timeNow, start) == 1 || dateCompare(timeNow, end) == 1) {
                    alert("1לא ניתן להוסיף מפגשים לשעה זו");
                    return false;
                }
            }

            for (let i = 0; i < circleMeetings[dayToAddMeeting].length; i++)
            {
                var iThStart = circleMeetings[dayToAddMeeting][i][0];
                var iThEnd = circleMeetings[dayToAddMeeting][i][1];
                
                if (indexOfEditedMeeting == -1 || (indexOfEditedMeeting != i && indexOfEditedMeeting != -1))
                {
                    if ((dateCompare(start, iThStart) == 1 || dateCompare(start, iThStart) == 0) && (dateCompare(end, iThEnd) == -1 || dateCompare(end, iThEnd) == 0)) {
                        alert("2לא ניתן להוסיף מפגשים לשעה זו");
                        return false;
                    }
    
                    if ((dateCompare(start, iThStart) == 1 || dateCompare(start, iThStart) == 0) && dateCompare(start, iThEnd) == -1) {
                        alert("3לא ניתן להוסיף מפגשים לשעה זו");
                        return false;
                    }
    
                    if (dateCompare(end, iThStart) == 1 && (dateCompare(end, iThEnd) == -1 || dateCompare(end, iThEnd) == 0)) {
                        alert("4לא ניתן להוסיף מפגשים לשעה זו");
                        return false;
                    }
    
                    if ((dateCompare(start, iThStart) == -1 || dateCompare(start, iThStart) == 0) && (dateCompare(end, iThEnd) == 1 || dateCompare(end, iThEnd) == 0)) {
                        alert("5לא ניתן להוסיף מפגשים לשעה זו");
                        return false;
                    }
                }                
            } 

            var dayElement = $($("[day=" + dayToAddMeeting + "]").find(".inner-schedule-item")[1]).find(".inner-meeting-w");
            if (dayElement.find(".inner-meeting-text").length) {
                dayElement.find(".inner-meeting-text").remove();
            }

            var newMeeting = indexOfEditedMeeting != -1 && circleMeetings[dayToAddMeeting][indexOfEditedMeeting].length == 3 ? 
                [start, end, circleMeetings[dayToAddMeeting][indexOfEditedMeeting][2]] : [start, end];

            if (indexOfEditedMeeting != -1)
            {
                $(dayElement.find(".meeting-w")[indexOfEditedMeeting]).remove();                
                $(".selected-sch-options-w").css("display", "none");
                /*if (dayElement.find(".meeting-w").length == 0) {
                    dayElement.append(`<div class="inner-meeting-text">מפגשים שישובצו ליום זה יופעו כאן</div>`);
                } */  
                
                if (circleMeetings[dayToAddMeeting][indexOfEditedMeeting].length == 3) 
                {
                    if (changedMeetingsIds.indexOf(circleMeetings[dayToAddMeeting][indexOfEditedMeeting][2]) == -1) {
                        changedMeetingsIds.push(circleMeetings[dayToAddMeeting][indexOfEditedMeeting][2]);
                    }                    
                }

                circleMeetings[dayToAddMeeting].splice(indexOfEditedMeeting, 1);
            } 

            var maxIndex = -1; 
            for (let i = 0; i < circleMeetings[dayToAddMeeting].length; i++)
            {
                var iThStart = circleMeetings[dayToAddMeeting][i][0];
                if (dateCompare(iThStart, start) == 1) {
                    maxIndex = i;
                    break;
                }
            }     
            
            if (dayElement.find(".meeting-w").length == 0) 
            {
                dayElement.prepend(`<label class="meeting-w noselect">
                    <input type="checkbox"/>מ: ` + start + ` עד: ` + end + `</label>`);   
                circleMeetings[dayToAddMeeting].push(newMeeting);                     
            }
            else 
            {                
                if (maxIndex >= 0)
                {     
                    circleMeetings[dayToAddMeeting].splice(maxIndex, 0, newMeeting);
                    $(dayElement.find(".meeting-w")[maxIndex]).before(`<label class="meeting-w noselect">
                        <input type="checkbox"/>מ: ` + start + ` עד: ` + end + `</label>`);
                }  
                else 
                {
                    circleMeetings[dayToAddMeeting].push(newMeeting);
                    dayElement.append(`<label class="meeting-w noselect">
                        <input type="checkbox"/>מ: ` + start + ` עד: ` + end + `</label>`); 
                }                      
            }

            for (let i = 0; i < dayElement.find(".meeting-w").length; i++) {
                $(dayElement.find(".meeting-w")[i]).css("margin-top", "10px");
            }
            $(dayElement.find(".meeting-w")[0]).css("margin-top", "0px");  
            $(".close-secondary-sc").click();
            indexOfEditedMeeting = -1;
            dayToAddMeeting = -1;
            addMeetingsEvents();
            return true;
        }
        else {
            alert("לא ניתן להוסיף מפגשים ליום זה");
            return false;
        }
    }
    else { 
        alert("נא להזין שעות תקינות");
        return false;
    }
}

function addMeetingsEvents()
{
    $("input[type=checkbox]").off("change");
    $("input[type=checkbox]").on("change", function()
    {
        if ($(":checkbox:checked").length > 0) 
        {
            $(".selected-sch-options-w").css("display", "block");
            if ($(this).prop("checked") == true)
            {
                $("input[type=checkbox]").prop("checked", false);
                $(this).prop("checked", true);
                dayToAddMeeting = $(this).parent().parent().parent().parent().index("[day]");
                indexOfEditedMeeting = $(this).parent().index();
            }            
        }
        else {
            $(".selected-sch-options-w").css("display", "none");
            dayToAddMeeting = -1;
            indexOfEditedMeeting = -1;
        }
    });

    $("#deleteMeetings").off("click");
    $("#deleteMeetings").on("click", function()
    {
        for (let i = 0; i < $(".inner-meeting-w").length; i++)
        {
            if ($($(".inner-meeting-w")[i]).find(".meeting-w").length > 0)
            {
                var meetings = $($(".inner-meeting-w")[i]).find(".meeting-w");
                var curDay = Number($($(".inner-meeting-w")[i]).parent().parent().attr("day"));

                var tempMeetings = [];
                for (let m = 0; m < circleMeetings[curDay].length; m++)
                {
                    if ($(meetings[m]).find("input[type=checkbox]").prop("checked") == false) {
                        tempMeetings.push(circleMeetings[curDay][m]);
                    }
                    else 
                    {   
                        // add the id of the meeting to the removedMeetingsIds array
                        if (circleMeetings[curDay][m].length == 3) 
                        {
                            removedMeetingsIds.push(circleMeetings[curDay][m][2]);
                            // check if this meeting id is inside the changedMeetingIds array before removing it
                            var indexInChanged = changedMeetingsIds.indexOf(circleMeetings[curDay][m][2]);
                            if (indexInChanged >= 0) {
                                changedMeetingsIds.splice(indexInChanged, 1);
                            }
                        }
                        $(meetings[m]).remove();
                    }
                }
                circleMeetings[curDay] = tempMeetings;

                if (circleMeetings[curDay].length == 0) { 
                    $($(".inner-meeting-w")[i]).append(`<div class="inner-meeting-text">מפגשים שישובצו ליום זה יופעו כאן</div>`);
                }
            }
            
            if ($($(".inner-meeting-w")[i]).find(".meeting-w").length)
            {
                $($(".inner-meeting-w")[i]).find(".meeting-w").css("margin-top", "10px");
                $($($(".inner-meeting-w")[i]).find(".meeting-w")[0]).css("margin-top", "0");
            }
        }
        $(".selected-sch-options-w").css("display", "none");
    }); 
    
    $("#changeMeeting").off("click");
    $("#changeMeeting").on("click", function()
    {
        if ($(":checkbox:checked").length > 1) {
            alert(`לא ניתן לשנות יותר ממפגש אחד בו זמנית`);
        }
        else if ($(":checkbox:checked").length == 1)
        {
            var editMeetingScreen = getEditMeetingScreen();
            $("body").append(editMeetingScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({opacity: 1}, 400, function() 
            {
                animationFinished = true;
                addSecondaryScreenEvents();
            });            
        }
    });
}

function getEditMeetingScreen()
{
    var editMeetingScreen = 
    `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">שינוי מפגש</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="new-meeting-field" style="margin-top: 0;">
                    <div class="new-meeting-field-name">שעת&nbsp;התחלה</div>
                    <input type="text" id="newMeetingBegin" class="new-meeting-field-value" placeholder="שעה&nbsp;(בפורמט: 00:00:00)"/>
                </div>
                <div class="new-meeting-field">
                    <div class="new-meeting-field-name">שעת&nbsp;סיום</div>
                    <input type="text" id="newMeetingEnd" class="new-meeting-field-value" placeholder="שעה&nbsp;(בפורמט: 00:00:00)"/>
                </div>
            </div>
            <div class="ssc-footer-w">
                <div id="editMeeting" class="ssc-disabled-action-btn noselect"><a>שנה/י מפגש</a></div>
            </div>
        </div>
    </div>`;
    return editMeetingScreen;
}

function getAddMeetingScreen()
{
    var addMeetingScreen = 
    `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">הוספת מפגש</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="new-meeting-field" style="margin-top: 0;">
                    <div class="new-meeting-field-name">שעת&nbsp;התחלה</div>
                    <input type="text" id="newMeetingBegin" class="new-meeting-field-value" placeholder="שעה&nbsp;(בפורמט: 00:00:00)"/>
                </div>
                <div class="new-meeting-field">
                    <div class="new-meeting-field-name">שעת&nbsp;סיום</div>
                    <input type="text" id="newMeetingEnd" class="new-meeting-field-value" placeholder="שעה&nbsp;(בפורמט: 00:00:00)"/>
                </div>
            </div>
            <div class="ssc-footer-w">
                <div id="addNewMeeting" class="ssc-disabled-action-btn noselect"><a>הוסף/י מפגש</a></div>
            </div>
        </div>
    </div>`;
    return addMeetingScreen;
}

function resizeEndded() 
{
    if (new Date() - resizeTime < resizeDelta) {
        setTimeout(resizeEndded, resizeDelta);
    } 
    else 
    {
        resizeTimeout = false;                        
        resize();
    }               
}

$(window).on("resize", function()
{         
    resize();
    resizeTime = new Date();
    if (resizeTimeout === false) 
    {
        resizeTimeout = true;
        setTimeout(resizeEndded, resizeDelta);
    }                               
});

$(window).on("load", function() {
    resize();
    addEvents();
    addMeetingsEvents();
});