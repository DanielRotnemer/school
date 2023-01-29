var resizeTime;
var resizeTimeout = false;
var resizeDelta = 200; 
var animationFinished = true; 
var domain = "localhost/NewSite";
var addExpenseCircleName = "";

function resize()
{
    if ($(".user-profile-menu-w").length)
    {
        $(".user-profile-menu-w").css("left", (100 + ($(".header-login").width() / 2) - ($(".user-profile-menu-w").width() / 2)) + "px");
    }

    if ($(".field-w").length)
    {
        for (let i = 0; i < $(".field-w").length; i++)
        {
            let width = $($(".field-w")[i]).width();
            $($(".field-value")[i]).css("width", (width - $($(".field-name")[i]).width() - $($(".button")[i]).width() - 22) + "px");
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

    if ($("#datesRange").length)
    {
        let width = $("#datesRange").width() - 50 - 40 - $($("#datesRange").find(".field-name")[0]).width() - $($("#datesRange").find(".field-name")[1]).width();
        $("#datesRange").find(".field-value").css("width", (width / 2 ) + "px");
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

    if ($("[expenseCircleName]").length)
    {
        $("[expenseCircleName]").off("click");
        $("[expenseCircleName]").on("click", function()
        {
            addExpenseCircleName = $(this).attr("expenseCircleName"); 
            var circleExpenseScreen = getAddCircleExpenseScreen();
            $("body").append(circleExpenseScreen);
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

function closeSecondaryScreen()
{    
    $(".secondary-screen-w").animate({opacity: 0}, 400, function() 
    {
        animationFinished = true;
        $(".secondary-screen-w").remove();
        $("body").css("overflow-x", "hidden");
        $("body").css("overflow-y", "auto");
        addExpenseCircleName = "";
    });
}

function addSecondaryScreenEvents()
{
    $(".secondary-screen-w").on("click", function() {
        closeSecondaryScreen();
    });

    $(".secondary-screen").on("click", function(event) 
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();
    });

    $(".close-secondary-sc").on("click", function() {
        closeSecondaryScreen();
    });

    if ($(".ssc-field-value").length)
    {
        $(".ssc-field-value").off("input");
        $(".ssc-field-value").on("input", function()
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

    if ($("#addNewCircleExpense").length)
    {
        $("#addNewCircleExpense").off("click");
        $("#addNewCircleExpense").on("click", function()
        {
            for (let i = 0; i < $(".ssc-field-value").length; i++)
            {
                if ($($(".ssc-field-value")[i]).val() == "")  {
                    return;
                }
            }
            
            $.ajax
            ({
                url         : "http://" + domain + "/pHandler",
                type        : "POST",                                                      
                data        : 
                {
                    addExpenseToCircle: addExpenseCircleName,
                    expenseName: $("#expenseName").val(),
                    expenseDescription: $("#expenseDescription").val(),
                    expenseAmount: $("#expenseAmount").val()
                },                             
                success     : function(output)
                {
                    if (output == 'SUCCESS') {
                        window.location.replace(window.location.href); 
                    } 
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("הוספת ההוצאה נכשלה, אנא נסה/י מאוחר יותר");
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
                            window.location.replace(window.location.href); 
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

function getAddCircleExpenseScreen()
{
    var circleExpenseScreen = 
    `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">הוספת הוצאה לחוג</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="ssc-field" style="margin-top: 0;">
                    <div class="ssc-field-name">הוצאה</div>
                    <input type="text" id="expenseName" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">תיאור ההוצאה</div>
                    <input type="text" id="expenseDescription" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">עלות</div>
                    <input type="text" id="expenseAmount" class="ssc-field-value"/>
                </div>
            </div>
            <div class="ssc-footer-w">
                <div id="addNewCircleExpense" class="ssc-disabled-action-btn noselect"><a>הוסף/י הוצאה</a></div>
            </div>
        </div>
    </div>`;
    return circleExpenseScreen;
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
});