var resizeTime;
var resizeTimeout = false;
var resizeDelta = 200;
var animationFinished = true;
var domain = "localhost/NewSite";
var selectedMeetings = [];
var primarySelectedMeetings = [];
var registerCircleId = -1;
var registerStudentId = -1;
var editEventIndex = -1;
var selectedStudentsForMessage = [];
var circleRate = -1;

function resize() {
    if ($(".user-profile-menu-w").length) {
        $(".user-profile-menu-w").css("left", (100 + ($(".header-login").width() / 2) - ($(".user-profile-menu-w").width() / 2)) + "px");
    }

    if ($(".events-container").length) {
        $(".events-container").css("height", (window.innerHeight - $(".title").height() - $(".header").height()) + "px");
        $(".events-w").css("height", ($(".events-container").height() - 2) + "px");
        $(".events-w").css("max-height", ($(".events-container").height() - 2) + "px");
    }

    if ($(".secondary-screen-w").length) {
        $(".secondary-screen-w").css("height", window.innerHeight + "px");
        $(".secondary-screen-w").css("width", window.innerWidth + "px");
        $(".secondary-screen-w").css("margin-top", $(window).scrollTop() + "px");
    }

    if ($(".ssc-field").length) {
        for (let i = 0; i < $(".ssc-field").length; i++) {
            let parent = $($(".ssc-field")[i]);
            let width = parent.width() - 10 - parent.find(".ssc-field-name").width();
            parent.find(".ssc-field-value").css("width", width + "px");
        }
    }
}

function addEvents() {
    if ($(".header-login > a[logged]").length) {
        $(".header-login > a[logged]").off("click");
        $(".header-login > a[logged]").on("click", function (event) {
            if (event.stopPropagation) event.stopPropagation();
            if (event.preventDefault) event.preventDefault();
            if (animationFinished) {
                animationFinished = false;
                var display = $(".user-profile-menu-w").css("display");
                if (display == "block") {
                    $(".user-profile-menu-w").animate({ opacity: 0 }, 500, function () {
                        $(".user-profile-menu-w").css("display", "none");
                        animationFinished = true;
                    });
                }
                else {
                    $(".user-profile-menu-w").css("display", "block");
                    $(".user-profile-menu-w").animate({ opacity: 1 }, 500, function () {
                        animationFinished = true;
                    });
                }
            }
        });

        $("html").on("click", function () {
            animationFinished = false;
            $(".user-profile-menu-w").animate({ opacity: 0 }, 500, function () {
                $(".user-profile-menu-w").css("display", "none");
                animationFinished = true;
            });
        });
    }

    if ($(".read-msg-w").length) {
        $(".read-msg-w").off("click");
        $(".read-msg-w").on("click", function () {
            var parent = $(this).parent();
            var messagesBar = parent.parent();
            var index = $(this).index(".read-msg-w");
            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        readMsgId: messagesIds[index],
                        readMsgByType: userType,
                        readMsgById: userId
                    },
                    success: function (output) {
                        if (output == "read successfully") {
                            animationFinished = false;
                            parent.animate({ height: 0 }, 400, function () {
                                animationFinished = true;
                                parent.remove();
                                messagesIds.splice(index, 1);
                                if (messagesIds.length == 0) {
                                    messagesBar.append(`<div class="message-w" style="margin-top: 0;">
                                    <div class="message-text">הנך מעודכן/ת, לא נמצאו עבורך הודעות חדשות כרגע</div>
                                </div>`);
                                }
                                else {
                                    $($(".message-w")[0]).css("margin-top", "0px");
                                }
                            });
                        }
                        else {
                            alert("ההסרה נכשלה, נסה/י שוב מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($(".subscribe-w").length) {
        $(".subscribe-w").off("click");
        $(".subscribe-w").on("click", function () {
            var index = $(this).index(".subscribe-w");
            var action = circles[index][0];
            var circleId = circles[index][1];

            var registrationScreen = getCircleRegistrationScreen();
            $("body").append(registrationScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        requestType: 'getCircleSchedule',
                        wantedCircleId: circleId,
                        targetAction: action
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            selectedMeetings = [];
                            primarySelectedMeetings = [];
                            output = output.split("SUCCESS").join("");
                            var registeredMeetings = output.substring(0, output.indexOf('|'));
                            if (registeredMeetings.split(" ").join("") != "") {
                                var registeredMeetingsArray = registeredMeetings.split(",");
                                for (let i = 0; i < registeredMeetingsArray.length; i++) {
                                    selectedMeetings.push(Number(registeredMeetingsArray[i]));
                                    primarySelectedMeetings.push(Number(registeredMeetingsArray[i]));
                                }
                            }
                            output = output.substring(output.indexOf('|') + 1);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            registerCircleId = circleId;
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("טעינת נתוני החוג נכשלה, נסה/י שוב מאוחר יותר");
                        }
                        addSecondaryScreenEvents();
                    }
                });
        });
    }

    if ($("#addStudent").length) {
        $("#addStudent").off("click");
        $("#addStudent").on("click", function () {
            var addStudentScreen = getAddStudentScreen();
            $("body").append(addStudentScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });
        });
    }

    if ($("#addInstructor").length) {
        $("#addInstructor").off("click");
        $("#addInstructor").on("click", function () {
            var addInstructorScreen = getAddInstructorScreen();
            $("body").append(addInstructorScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });
        });
    }

    if ($("#createCircle").length) {
        $("#createCircle").off("click");
        $("#createCircle").on("click", function () {
            var newCircleScreen = getNewCircleScreen();
            $("body").append(newCircleScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });
        });
    }

    if ($("#createEvent").length) {
        $("#createEvent").off("click");
        $("#createEvent").on("click", function () {
            var newEventScreen = getAddEventScreen();
            $("body").append(newEventScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });
        });
    }

    if ($("#myStudents").length) {
        $("#myStudents").off("click");
        $("#myStudents").on("click", function () {
            var studentsScreen = getStudentsScreen();
            $("body").append(studentsScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                $(".ssc-footer-w").find("div").on("click", function () {
                    closeSecondaryScreen();
                });
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'studentsForInstructor'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            addSecondaryScreenEvents();
                        }
                        else if (output != '') {
                            $(".ssc-body-w").find(".loading").text(output);
                            //alert(output);
                        }
                        else {
                            $(".ssc-body-w").find(".loading").text("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                            //alert("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#addStudentToCircle").length) {
        $("#addStudentToCircle").off("click");
        $("#addStudentToCircle").on("click", function () {
            var registerStudentScreen = getRegisterStudentScreen();
            $("body").append(registerStudentScreen);
            $("body").css("overflow", "hidden");
            if (myCircles.length == 0) {
                $(".ssc-body-w").empty();
                $(".ssc-body-w").append('<div class="alt-text">לא קיימים עבורך חוגים נוספים לשבוע זה</div>');
            }
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                if (myCircles.length == 0) {
                    return;
                }
            });

            selectedMeetings = [];
            registerCircleId = Number(myCircles[0][1]);
            registerStudentId = Number(students[0][0]);

            $("#selectedCircle").off("change");
            $("#selectedCircle").on("change", function () {
                var index = $(this)[0].selectedIndex;
                registerCircleId = Number(myCircles[index][1]);

                $.ajax
                    ({
                        url: "http://" + domain + "/pHandler",
                        type: "POST",
                        data:
                        {
                            requestType: 'getCircleSchedule',
                            wantedCircleId: registerCircleId,
                            targetAction: 'registerByInstructor',
                            studentId: registerStudentId
                        },
                        success: function (output) {
                            if (output.substring(0, 7) == "SUCCESS") {
                                selectedMeetings = [];
                                primarySelectedMeetings = [];
                                output = output.split("SUCCESS").join("");
                                var registeredMeetings = output.substring(0, output.indexOf('|'));
                                if (registeredMeetings.split(" ").join("") != "") {
                                    var registeredMeetingsArray = registeredMeetings.split(",");
                                    for (let i = 0; i < registeredMeetingsArray.length; i++) {
                                        selectedMeetings.push(Number(registeredMeetingsArray[i]));
                                        primarySelectedMeetings.push(Number(registeredMeetingsArray[i]));
                                    }
                                }
                                output = output.substring(output.indexOf('|') + 1);
                                $(".loading").remove();
                                $(".circle-meetings-outer-wrapper").remove();
                                $(".ssc-body-w").append(output);
                                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn animated-transition noselect");
                            }
                            else if (output != '') {
                                alert(output);
                            }
                            else {
                                alert("טעינת נתוני החוג נכשלה, נסה/י שוב מאוחר יותר");
                            }
                            addSecondaryScreenEvents();
                        }
                    });
            });

            $("#studentId").off("change");
            $("#studentId").on("change", function () {
                var index = $(this)[0].selectedIndex;
                registerStudentId = Number(students[index][0]);

                $.ajax
                    ({
                        url: "http://" + domain + "/pHandler",
                        type: "POST",
                        data:
                        {
                            requestType: 'getCircleSchedule',
                            wantedCircleId: registerCircleId,
                            targetAction: 'registerByInstructor',
                            studentId: registerStudentId
                        },
                        success: function (output) {
                            if (output.substring(0, 7) == "SUCCESS") {
                                selectedMeetings = [];
                                primarySelectedMeetings = [];
                                output = output.split("SUCCESS").join("");
                                var registeredMeetings = output.substring(0, output.indexOf('|'));
                                if (registeredMeetings.split(" ").join("") != "") {
                                    var registeredMeetingsArray = registeredMeetings.split(",");
                                    for (let i = 0; i < registeredMeetingsArray.length; i++) {
                                        selectedMeetings.push(Number(registeredMeetingsArray[i]));
                                        primarySelectedMeetings.push(Number(registeredMeetingsArray[i]));
                                    }
                                }
                                output = output.substring(output.indexOf('|') + 1);
                                $(".loading").remove();
                                $(".circle-meetings-outer-wrapper").remove();
                                $(".ssc-body-w").append(output);
                                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn animated-transition noselect");
                            }
                            else if (output != '') {
                                alert(output);
                            }
                            else {
                                alert("טעינת נתוני החוג נכשלה, נסה/י שוב מאוחר יותר");
                            }
                            addSecondaryScreenEvents();
                        }
                    });
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        requestType: 'getCircleSchedule',
                        wantedCircleId: registerCircleId,
                        targetAction: 'registerByInstructor',
                        studentId: registerStudentId
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            selectedMeetings = [];
                            primarySelectedMeetings = [];
                            output = output.split("SUCCESS").join("");
                            var registeredMeetings = output.substring(0, output.indexOf('|'));
                            if (registeredMeetings.split(" ").join("") != "") {
                                var registeredMeetingsArray = registeredMeetings.split(",");
                                for (let i = 0; i < registeredMeetingsArray.length; i++) {
                                    selectedMeetings.push(Number(registeredMeetingsArray[i]));
                                    primarySelectedMeetings.push(Number(registeredMeetingsArray[i]));
                                }
                            }
                            output = output.substring(output.indexOf('|') + 1);
                            $(".loading").remove();
                            $(".circle-meetings-outer-wrapper").remove();
                            $(".ssc-body-w").append(output);
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("טעינת נתוני החוג נכשלה, נסה/י שוב מאוחר יותר");
                        }
                        addSecondaryScreenEvents();
                    }
                });
        });
    }

    if ($("#sendManagerMessage").length) {
        $("#sendManagerMessage").off("click");
        $("#sendManagerMessage").on("click", function () {
            var sendManagerMessageScreen = getSendManagerMessageScreen();
            $("body").append(sendManagerMessageScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });
        });
    }

    if ($("#sendInstructorMessage").length) {
        $("#sendInstructorMessage").off("click");
        $("#sendInstructorMessage").on("click", function () {
            var sendInstructorMessageScreen = getSendInstructorMessageScreen();
            $("body").append(sendInstructorMessageScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'selectStudentsForInstructor'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            selectedStudentsForMessage = [];
                            output = output.substring(7);
                            $(".ssc-body-w").find(".student-list-items-w").remove();
                            $(".ssc-body-w").append(output);
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

    if ($("#managerStudentsList").length) {
        $("#managerStudentsList").off("click");
        $("#managerStudentsList").on("click", function () {
            var managerStudentsListScreen = getManagerStudentsListScreen();
            $("body").append(managerStudentsListScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'studentsForManager'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            $(".ssc-footer-w > div").on("click", function () {
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

    if ($("#managerInstructorsList").length) {
        $("#managerInstructorsList").off("click");
        $("#managerInstructorsList").on("click", function () {
            var managerInstructrsListScreen = getManagerInstructorsListScreen();
            $("body").append(managerInstructrsListScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'instructorsForManager'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            $(".ssc-footer-w > div").on("click", function () {
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

    if ($("#manageManagerMessages").length) {
        $("#manageManagerMessages").off("click");
        $("#manageManagerMessages").on("click", function () {
            var managerMessagesScreen = getManagerMessagesScreen();
            $("body").append(managerMessagesScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'messagesOfManager'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            $(".ssc-footer-w > div").on("click", function () {
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

    if ($("#manageInstructorMessages").length) {
        $("#manageInstructorMessages").off("click");
        $("#manageInstructorMessages").on("click", function () {
            var instructorMessagesScreen = getInstructorMessagesScreen();
            $("body").append(instructorMessagesScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                $(".ssc-footer-w > div").on("click", function () {
                    $(".secondary-screen-w").click();
                });
                addSecondaryScreenEvents();
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'messagesOfInstructor'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
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

    if ($("#studentCircleComments").length) {
        $("#studentCircleComments").off("click");
        $("#studentCircleComments").on("click", function () {
            var studentCircleCommentsScreen = getStudentCircleCommentsScreen();
            $("body").append(studentCircleCommentsScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                circleRate = -1;
                if (circlesEligibleForComments.length == 0) {
                    $(".ssc-footer-w > div").on("click", function () {
                        $(".secondary-screen-w").click();
                    });
                }
            });
        });
    }

    if ($("#instructorCircleComments").length) {
        $("#instructorCircleComments").off("click");
        $("#instructorCircleComments").on("click", function () {
            var instructorCommentsScreen = getInstructorCircleCommentsScreen();
            $("body").append(instructorCommentsScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                circleRate = -1;
                if (circlesEligibleForComments.length == 0) {
                    $(".ssc-footer-w > div").on("click", function () {
                        $(".secondary-screen-w").click();
                    });
                }
            });
        });
    }

    if ($("#instructorReadComments").length) {
        $("#instructorReadComments").off("click");
        $("#instructorReadComments").on("click", function () {
            var instructorReadCommentsScreen = getInstructorReadCommentsScreen();
            $("body").append(instructorReadCommentsScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                if (weeklyFinishedCircles.length == 0) {
                    $(".ssc-footer-w > div").on("click", function () {
                        $(".secondary-screen-w").click();
                    });
                }
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'specificCircleComments',
                        commentsCircleId: weeklyFinishedCircles[0][0]
                    },
                    success: function (output) {
                        if (output.indexOf('SUCCESS') == 0) {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".student-list-items-w").remove();
                            $(".ssc-body-w").append(output);
                            $(".ssc-footer-w > div").on("click", function () {
                                $(".secondary-screen-w").click();
                            });
                            addSecondaryScreenEvents();
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("טעינת הנתונים נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#studentReadCircleComments").length) {
        $("#studentReadCircleComments").off("click");
        $("#studentReadCircleComments").on("click", function () {
            var studentReadCommentsScreen = getStudentReadCommentsScreen();
            $("body").append(studentReadCommentsScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                if (weeklyFinishedCircles.length == 0) {
                    $(".ssc-footer-w > div").on("click", function () {
                        $(".secondary-screen-w").click();
                    });
                }
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'specificCircleComments',
                        commentsCircleId: weeklyFinishedCircles[0][0]
                    },
                    success: function (output) {
                        if (output.indexOf('SUCCESS') == 0) {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".student-list-items-w").remove();
                            $(".ssc-body-w").append(output);
                            $(".ssc-footer-w > div").on("click", function () {
                                $(".secondary-screen-w").click();
                            });
                            addSecondaryScreenEvents();
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("טעינת הנתונים נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#myInstructorEvents").length) {
        $("#myInstructorEvents").off("click");
        $("#myInstructorEvents").on("click", function () {
            var instructorEventsScreen = getInstructorEventScreen();
            $("body").append(instructorEventsScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                $(".ssc-footer-w").find("div").on("click", function () {
                    closeSecondaryScreen();
                });
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'eventsListForInstructor'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            addSecondaryScreenEvents();
                        }
                        else if (output != '') {
                            $(".ssc-body-w").find(".loading").text(output);
                        }
                        else {
                            $(".ssc-body-w").find(".loading").text("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#myInstructorCircles").length) {
        $("#myInstructorCircles").off("click");
        $("#myInstructorCircles").on("click", function () {
            var instructorCirclesScreen = getInstructorCirclesScreen();
            $("body").append(instructorCirclesScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                $(".ssc-footer-w").find("div").on("click", function () {
                    closeSecondaryScreen();
                });
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'circlesListForInstructor'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            addSecondaryScreenEvents();
                        }
                        else if (output != '') {
                            $(".ssc-body-w").find(".loading").text(output);
                        }
                        else {
                            $(".ssc-body-w").find(".loading").text("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#myStudentCircles").length) {
        $("#myStudentCircles").off("click");
        $("#myStudentCircles").on("click", function () {
            var studentCirclessScreen = getStudentCirclesScreen();
            $("body").append(studentCirclessScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
                $(".ssc-footer-w").find("div").on("click", function () {
                    closeSecondaryScreen();
                });
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'circlesListForStudent'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            addSecondaryScreenEvents();
                        }
                        else if (output != '') {
                            $(".ssc-body-w").find(".loading").text(output);
                        }
                        else {
                            $(".ssc-body-w").find(".loading").text("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("[editEvent]").length) {
        $("[editEvent]").off("click");
        $("[editEvent]").on("click", function () {
            editEventIndex = $(this).index("[editEvent]");
            var editEventScreen = getEditEventScreen();
            $("body").append(editEventScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        editEventId: $($("[editEvent]")[editEventIndex]).attr("editEvent"),
                        type: 'eventInfo'
                    },
                    success: function (output) {
                        if (output.substring(0, 7) == "SUCCESS") {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".loading").remove();
                            $(".ssc-body-w").append(output);
                            resize();
                            addSecondaryScreenEvents();
                        }
                        else if (output != '') {
                            $(".ssc-body-w").find(".loading").text(output);
                        }
                        else {
                            $(".ssc-body-w").find(".loading").text("טעינת הנתונים נכשלה, נסה/י שוב מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("[removeEvent]").length) {
        $("[removeEvent]").off("click");
        $("[removeEvent]").on("click", function () {
            var rmEventId = $(this).attr("removeEvent");
            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        removeEventId: rmEventId
                    },
                    success: function (output) {
                        if (output == "האירוע הוסר בהצלחה") {
                            location.reload();
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("הסרת האירוע נכשלה, נסה/י שוב מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("[removeCircle]").length) {
        $("[removeCircle]").off("click");
        $("[removeCircle]").on("click", function () {
            var rmCircleId = $(this).attr("removeCircle");
            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        removeCircleId: rmCircleId
                    },
                    success: function (output) {
                        if (output == "החוג הוסר בהצלחה") {
                            location.reload();
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("הסרת החוג נכשלה, נסה/י שוב מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#profile").length) {
        $("#profile").off("click");
        $("#profile").on("click", function () {
            var profileScreen = getProfileScreen();
            $("body").append(profileScreen);
            $("body").css("overflow", "hidden");
            resize();
            animationFinished = false;
            $(".secondary-screen-w").animate({ opacity: 1 }, 400, function () {
                animationFinished = true;
                addSecondaryScreenEvents();
            });
        });
    }
}

function closeSecondaryScreen() {
    animationFinished = false;
    $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
        animationFinished = true;
        $(".secondary-screen-w").remove();
        $("body").css("overflow-x", "hidden");
        $("body").css("overflow-y", "auto");
        selectedMeetings = [];
        selectedStudentsForMessage = [];
        primarySelectedMeetings = [];
        registerCircleId = -1;
        registerStudentId = -1;
        editEventIndex = -1;
    });
}

function arrayEquals(array1, array2) {
    if (array1.length != array2.length) { return false; }
    for (let i = 0; i < array1.length; i++) {
        if (array1[i] != array2[i]) {
            return false;
        }
    }
    return true;
}

function addSecondaryScreenEvents() {
    $(".secondary-screen-w").on("click", function () {
        closeSecondaryScreen();
    });

    $(".secondary-screen").on("click", function (event) {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();
    });

    $(".close-secondary-sc").on("click", function () {
        closeSecondaryScreen();
    });

    if ($("[remove=student]").length) {
        $("[remove=student]").off("click");
        $("[remove=student]").on("click", function () {
            var parent = $(this).parent();
            var studentId = parent.attr("studentId");
            var container = parent.parent();
            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        removeStudentId: studentId
                    },
                    success: function (output) {
                        if (output == "הסרת התלמיד התבצעה בהצלחה") {
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

    if ($("[remove=instructor]").length) {
        $("[remove=instructor]").off("click");
        $("[remove=instructor]").on("click", function () {
            var parent = $(this).parent();
            var instructorId = parent.attr("instructorId");
            var container = parent.parent();
            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        removeInstructorId: instructorId
                    },
                    success: function (output) {
                        if (output == "הסרת המדריך התבצעה בהצלחה") {
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

    if ($(".ssc-field-value").length) {
        $(".ssc-field-value, textarea").off("input");
        $(".ssc-field-value, textarea").on("input", function () {
            var empty = false;
            for (let i = 0; i < $(".ssc-field-value").length; i++) {
                if ($($(".ssc-field-value")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    empty = true;
                    break;
                }
            }
            for (let i = 0; i < $(".field-textarea").length; i++) {
                if ($($(".field-textarea")[i]).val() == "") {
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

    if ($("#instructorSendComment").length && $("#selectedCircleInstComment").length) {
        $("#commentContent").off("input");
        $("#commentContent").on("input", function () {
            if ($(this).val() == "" || circleRate == -1) {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }

            var circleIndex = $("#selectedCircleInstComment")[0].selectedIndex;
            if (studentsOfCirclesEligibleForComments[circleIndex].length == 0) {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
        });

        $(".rate-itm").off("click");
        $(".rate-itm").on("click", function () {
            circleRate = $(this).index(".rate-itm") + 1;
            $(".rate-itm").css("background", "#00aaee");
            $(this).css("background", "#10a26b");

            var circleIndex = $("#selectedCircleInstComment")[0].selectedIndex;
            if (studentsOfCirclesEligibleForComments[circleIndex].length == 0 || $("#commentContent").val() == "") {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }
        });

        $("#selectedCircleInstComment").off("change");
        $("#selectedCircleInstComment").on("change", function () {
            if ($("#commentContent").val() == "" || circleRate == -1) {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }

            var circleIndex = $("#selectedCircleInstComment")[0].selectedIndex;
            $("#selectedStudentInstComment").empty();

            for (let i = 0; i < studentsOfCirclesEligibleForComments[circleIndex].length; i++) {
                $("#selectedStudentInstComment").append(`<option>` + studentsOfCirclesEligibleForComments[circleIndex][i][1] + `</option>`);
            }
            if (studentsOfCirclesEligibleForComments[circleIndex].length == 0) {
                $("#selectedStudentInstComment").append(`<option>לא קיימים משתתפים לחוג זה</option>`);
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
        });

        $("#instructorSendComment").off("click");
        $("#instructorSendComment").on("click", function () {
            if ($("#commentContent").val() == "" || circleRate == -1) { return; }
            var circleIndex = $("#selectedCircleInstComment")[0].selectedIndex;

            if (studentsOfCirclesEligibleForComments[circleIndex].length == 0) { return; }

            var studentIndex = $("#selectedStudentInstComment")[0].selectedIndex;

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        circleInstructorAddComment: $("#commentContent").val(),
                        circleId: circlesEligibleForComments[circleIndex][1],
                        rate: circleRate,
                        onStudentId: studentsOfCirclesEligibleForComments[circleIndex][studentIndex][0]
                    },
                    success: function (output) {
                        if (output == "המשוב נשלח בהצלחה") {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
                                circleRate = -1;
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
                            alert("הוספת המשוב נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#selectedCircleReadComments").length) {
        $("#selectedCircleReadComments").off("change");
        $("#selectedCircleReadComments").on("change", function () {
            var selectedCircleId = weeklyFinishedCircles[$(this)[0].selectedIndex][0];
            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        listType: 'specificCircleComments',
                        commentsCircleId: selectedCircleId
                    },
                    success: function (output) {
                        if (output.indexOf('SUCCESS') == 0) {
                            output = output.substring(7);
                            $(".ssc-body-w").find(".student-list-items-w").remove();
                            $(".ssc-body-w").append(output);
                            $(".ssc-footer-w > div").on("click", function () {
                                $(".secondary-screen-w").click();
                            });
                            addSecondaryScreenEvents();
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("טעינת הנתונים נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#createNewCircle").length) {
        $("#createNewCircle").off("click");
        $("#createNewCircle").on("click", function () {
            for (let i = 0; i < $(".ssc-field-value").length; i++) {
                if ($($(".ssc-field-value")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    return;
                }
            }
            for (let i = 0; i < $(".field-textarea").length; i++) {
                if ($($(".field-textarea")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    break;
                }
            }

            $.ajax
            ({
                url: "http://" + domain + "/pHandler",
                type: "POST",
                data:
                {
                    newCircleName: $("#circleName").val(),
                    newCircleDescription: $("#circleDescription").val()
                },
                success: function (output) {
                    if (output == "added successfully") {
                        animationFinished = false;
                        $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
                            animationFinished = true;
                            $(".secondary-screen-w").remove();
                            $("body").css("overflow-x", "hidden");
                            $("body").css("overflow-y", "auto");
                        });
                    }
                    else if (output != '') {
                        alert(output);
                    }
                    else {
                        alert("יצירת החוג נכשלה, אנא נסה/י מאוחר יותר");
                    }
                }
            });
        });
    }

    if ($("#createNewEvent").length) {
        $("#createNewEvent").off("click");
        $("#createNewEvent").on("click", function () {
            for (let i = 0; i < $(".ssc-field-value").length; i++) {
                if ($($(".ssc-field-value")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    return;
                }
            }

            for (let i = 0; i < $(".field-textarea").length; i++) {
                if ($($(".field-textarea")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    return;
                }
            }

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        newEventName: $("#eventName").val(),
                        newEventDescription: $("#eventDescription").val(),
                        newEventInstructor: $("#selectInstructor").val(),
                        newEventStartDate: $("#eventStartDate").val(),
                        newEventStartHour: $("#eventStartHour").val(),
                        newEventEndDate: $("#eventEndDate").val(),
                        newEventEndHour: $("#eventEndHour").val()
                    },
                    success: function (output) {
                        if (output == 'האירוע נוסף בהצלחה') {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
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
                            alert("יצירת האירוע נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#addNewInstructor").length) {
        $("#addNewInstructor").off("click");
        $("#addNewInstructor").on("click", function () {
            for (let i = 0; i < $(".ssc-field-value").length; i++) {
                if ($($(".ssc-field-value")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    return;
                }
            }

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        instructorFirstName: $("#firstName").val(),
                        instructorLastName: $("#lastName").val(),
                        instructorMail: $("#mail").val(),
                        instructorPhone: $("#phone").val(),
                        instructorId: $("#id").val(),
                        instructorBirthDate: $("#birthDate").val()
                    },
                    success: function (output) {
                        if (output == "added successfully") {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
                                animationFinished = true;
                                $(".secondary-screen-w").remove();
                                $("body").css("overflow-x", "hidden");
                                $("body").css("overflow-y", "auto");
                            });
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("הוספת המדריך נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#addNewStudent").length) {
        $("#addNewStudent").off("click");
        $("#addNewStudent").on("click", function () {
            for (let i = 0; i < $(".ssc-field-value").length; i++) {
                if ($($(".ssc-field-value")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    return;
                }
            }

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        studentFirstName: $("#firstName").val(),
                        studentLastName: $("#lastName").val(),
                        studentMail: $("#mail").val(),
                        studentPhone: $("#phone").val(),
                        studentId: $("#id").val(),
                        studentBirthDate: $("#birthDate").val()
                    },
                    success: function (output) {
                        if (output == "added successfully") {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
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
                            alert("הוספת התלמיד נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#registerToCircle").length) {
        $(".choose-meeting").off("click");
        $(".choose-meeting").on("click", function () {
            var index = $(this).parent().find(".meeting-times").index(".meeting-times");
            if (selectedMeetings.indexOf(index) == -1) {
                if (selectedMeetings.length > 0) {
                    var max = index;
                    var maxIndex = selectedMeetings.length;
                    for (let i = 0; i < selectedMeetings.length; i++) {
                        if (selectedMeetings[i] > max) {
                            max = selectedMeetings[i];
                            maxIndex = i;
                            break;
                        }
                    }
                    selectedMeetings.splice(maxIndex, 0, index);
                }
                else {
                    selectedMeetings.push(index);
                }
                $(this).text("הסר/י");
            }
            else {
                var indexToRemove = selectedMeetings.indexOf(index);
                selectedMeetings.splice(indexToRemove, 1);
                $(this).text("בחר/י");
            }
            if (arrayEquals(selectedMeetings, primarySelectedMeetings)) {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn animated-transition noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }
        });

        $("#registerToCircle").off("click");
        $("#registerToCircle").on("click", function () {
            if (arrayEquals(selectedMeetings, primarySelectedMeetings)) { return; }
            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        meetings: JSON.stringify(selectedMeetings),
                        prevRegisteredMeetings: JSON.stringify(primarySelectedMeetings),
                        circleId: registerCircleId,
                        operation: 'registerForMeetings'
                    },
                    success: function (output) {
                        if (output == 'ההרשמה בוצעה בהצלחה') {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
                                animationFinished = true;
                                selectedMeetings = [];
                                primarySelectedMeetings = [];
                                registerCircleId = -1;
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
                            alert("ההרשמה למפגשים של חוג זה נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#registerStudentToCircle").length) {
        $(".choose-meeting").off("click");
        $(".choose-meeting").on("click", function () {
            var index = $(this).parent().find(".meeting-times").index(".meeting-times");
            if (selectedMeetings.indexOf(index) == -1) {
                if (selectedMeetings.length > 0) {
                    var max = index;
                    var maxIndex = selectedMeetings.length;
                    for (let i = 0; i < selectedMeetings.length; i++) {
                        if (selectedMeetings[i] > max) {
                            max = selectedMeetings[i];
                            maxIndex = i;
                            break;
                        }
                    }
                    selectedMeetings.splice(maxIndex, 0, index);
                }
                else {
                    selectedMeetings.push(index);
                }
                $(this).text("הסר/י");
            }
            else {
                var indexToRemove = selectedMeetings.indexOf(index);
                selectedMeetings.splice(indexToRemove, 1);
                $(this).text("בחר/י");
            }
            if (arrayEquals(selectedMeetings, primarySelectedMeetings)) {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn animated-transition noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }
        });

        $("#registerStudentToCircle").off("click");
        $("#registerStudentToCircle").on("click", function () {
            if (arrayEquals(selectedMeetings, primarySelectedMeetings)) { return; }
            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        meetings: JSON.stringify(selectedMeetings),
                        prevRegisteredMeetings: JSON.stringify(primarySelectedMeetings),
                        circleId: registerCircleId,
                        operation: 'registerForMeetingsByInstructor',
                        studentId: registerStudentId
                    },
                    success: function (output) {
                        if (output == 'ההרשמה בוצעה בהצלחה') {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
                                animationFinished = true;
                                selectedMeetings = [];
                                primarySelectedMeetings = [];
                                registerCircleId = -1;
                                registerStudentId = -1;
                                $(".secondary-screen-w").remove();
                                $("body").css("overflow-x", "hidden");
                                $("body").css("overflow-y", "auto");
                            });
                        }
                        else if (output != '') {
                            alert(output);
                        }
                        else {
                            alert("ההרשמה למפגשים של חוג זה נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#publishManagerMessage").length) {
        $("#messageContent").off("input");
        $("#messageContent").on("input", function () {
            if ($(this).val() != "") {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
        });

        $("#publishManagerMessage").off("click");
        $("#publishManagerMessage").on("click", function () {
            if ($("#messageContent").val() != "") {
                var index = $("#selectedTarget")[0].selectedIndex;
                var messageTo = index == 0 ? 'all' : instructors[index - 1][1];
                $.ajax
                    ({
                        url: "http://" + domain + "/pHandler",
                        type: "POST",
                        data:
                        {
                            sendMessageTo: messageTo,
                            messageContent: $("#messageContent").val()
                        },
                        success: function (output) {
                            if (output == 'added successfully') {
                                animationFinished = false;
                                $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
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
                                alert("פרסום ההודעה נכשלה, אנא נסה/י שוב מאוחר יותר");
                            }
                        }
                    });
            }
        });
    }

    if ($("#publishInstructorMessage").length) {
        $("#messageContent").off("input");
        $("#messageContent").on("input", function () {
            if ($(this).val() != "" && selectedStudentsForMessage.length > 0) {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
        });

        if ($("[selectForMsg]").length) {
            $("[selectForMsg]").off("click");
            $("[selectForMsg]").on("click", function () {
                var studentId = $(this).attr("selectForMsg");
                var action = "";
                if (selectedStudentsForMessage.indexOf(studentId) < 0) {
                    selectedStudentsForMessage.push(studentId);
                    action = "הסר/י";
                }
                else {
                    var index = selectedStudentsForMessage.indexOf(studentId);
                    selectedStudentsForMessage.splice(index, 1);
                    action = "בחר/י";
                }
                $(this).find("a").text(action);

                if ($("#messageContent").val() != "" && selectedStudentsForMessage.length > 0) {
                    $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
                }
                else {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                }
            });
        }

        $("#publishInstructorMessage").off("click");
        $("#publishInstructorMessage").on("click", function () {
            if ($(this).val() == "" && selectedStudentsForMessage.length == 0) { return; }

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        sendMessageTo: JSON.stringify(selectedStudentsForMessage),
                        messageContent: $("#messageContent").val()
                    },
                    success: function (output) {
                        if (output == 'added successfully') {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
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
                            alert("הסרת התלמיד נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("[message]").length) {
        $("[message]").off("click");
        $("[message]").on("click", function () {
            var parent = $(this).parent();
            var messageId = parent.attr("messageId");
            var act = $(this).attr("message");
            var button = $(this);

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        actionMessageId: messageId,
                        action: act
                    },
                    success: function (output) {
                        if (output == "הפעולה התבצעה בהצלחה") {
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

    if ($("#studentSendComment").length) {
        $("#commentContent").off("input");
        $("#commentContent").on("input", function () {
            if ($(this).val() == "" || circleRate == -1) {
                $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
            }
            else {
                $(".ssc-footer-w > div").attr("class", "ssc-action-btn animated-transition noselect");
            }
        });

        $(".rate-itm").off("click");
        $(".rate-itm").on("click", function () {
            circleRate = $(this).index(".rate-itm") + 1;
            $(".rate-itm").css("background", "#00aaee");
            $(this).css("background", "#10a26b");
        });

        $("#studentSendComment").off("click");
        $("#studentSendComment").on("click", function () {
            if ($("#commentContent").val() == "" || circleRate == -1) { return; }
            var circleIndex = $("#selectedCircle")[0].selectedIndex;

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        circleStudentAddComment: $("#commentContent").val(),
                        circleId: circlesEligibleForComments[circleIndex][1],
                        rate: circleRate
                    },
                    success: function (output) {
                        if (output == "המשוב נשלח בהצלחה") {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
                                circleRate = -1;
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
                            alert("הסרת התלמיד נכשלה, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#saveEditEvent").length) {
        $("#saveEditEvent").off("click");
        $("#saveEditEvent").on("click", function () {
            if ($(".ssc-field-value").length == 0) {
                closeSecondaryScreen();
                return;
            }

            for (let i = 0; i < $(".ssc-field-value").length; i++) {
                if ($($(".ssc-field-value")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    return;
                }
            }

            for (let i = 0; i < $(".field-textarea").length; i++) {
                if ($($(".field-textarea")[i]).val() == "") {
                    $(".ssc-footer-w > div").attr("class", "ssc-disabled-action-btn noselect");
                    return;
                }
            }

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        editEventId: $($("[editEvent]")[editEventIndex]).attr("editEvent"),
                        type: 'saveEdits',
                        eventName: $("#eventName").val(),
                        eventDescription: $("#eventDescription").val(),
                        eventInstructor: $("#selectInstructor").val(),
                        eventStartDate: $("#eventStartDate").val(),
                        eventStartHour: $("#eventStartHour").val(),
                        eventEndDate: $("#eventEndDate").val(),
                        eventEndHour: $("#eventEndHour").val()
                    },
                    success: function (output) {
                        if (output == 'השינויים נשמרו בהצלחה') {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
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
                            alert("עדכון האירוע נכשל, אנא נסה/י מאוחר יותר");
                        }
                    }
                });
        });
    }

    if ($("#updateProfile").length) {
        $("input[type=text]").on("input", function () {
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
        $("#updateProfile").on("click", function () {
            if ($("#firstName").val() == "" || $("#lastName").val() == "" || $("#phone").val() == "" || $("#email").val() == "") {
                return;
            }

            if ($("#firstName").val() == firstName && $("#lastName").val() == lastName && $("#phone").val() == phone && $("#email").val() == email) {
                return;
            }

            $.ajax
                ({
                    url: "http://" + domain + "/pHandler",
                    type: "POST",
                    data:
                    {
                        firstName: $("#firstName").val(),
                        lastName: $("#lastName").val(),
                        phone: $("#phone").val(),
                        email: $("#email").val()
                    },
                    success: function (output) {
                        if (output == 'השינויים נשמרו בהצלחה') {
                            animationFinished = false;
                            $(".secondary-screen-w").animate({ opacity: 0 }, 400, function () {
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

function getStudentCircleCommentsScreen() {
    var studentCircleCommentsScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">הוסף/י משוב לחוג</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">`;
    if (circlesEligibleForComments.length > 0) {
        studentCircleCommentsScreen +=
            `<div class="ssc-field" style="margin-top: 0;">
                        <div class="ssc-field-name">בחר/י&nbsp;חוג</div>
                        <select id="selectedCircle" class="ssc-field-value">`;
        for (let i = 0; i < circlesEligibleForComments.length; i++) {
            studentCircleCommentsScreen +=
                `<option>` + circlesEligibleForComments[i][0] + `, ע"י ` + circlesEligibleForComments[i][2] + `</option>`;
        }
        studentCircleCommentsScreen +=
            `</select>
                    </div>
                    <div class="ssc-field" style="height: 20px;">
                        <div class="ssc-field-name" style="height: 20px; line-height: 20px;">דרג/י&nbsp;חוג</div>
                        <div class="rate-itm" style="margin-right: 10px;">1</div>
                        <div class="rate-itm" style="margin-right: 5px;">2</div>
                        <div class="rate-itm" style="margin-right: 5px;">3</div>
                        <div class="rate-itm" style="margin-right: 5px;">4</div>
                        <div class="rate-itm" style="margin-right: 5px;">5</div>
                    </div>
                    <div class="ssc-field-name" style="margin-top: 15px; width: 100%; height: 20px; line-height: 20px; direction: rtl; text-align: right;">תוכן המשוב:</div>
                    <textarea id="commentContent" class="field-textarea"></textarea>`;
    }
    else {
        var text = circlesEligibleForComments.length > 0 ? `בחר חוג על מנת להשאיר משוב`
            : `לא ניתן להגיב על חוגים כעת`;
        studentCircleCommentsScreen +=
            `<div class="alt-text">` + text + `</div>`;
    }
    studentCircleCommentsScreen +=
        `</div>`;
    var action = circlesEligibleForComments.length > 0 ? `שלח משוב` : `סגור`;
    var id = circlesEligibleForComments.length > 0 ? `id="studentSendComment"` : ``;
    var cls = circlesEligibleForComments.length > 0 ? `class="ssc-disabled-action-btn animated-transition noselect"` :
        `class="ssc-action-btn animated-transition noselect"`;
    studentCircleCommentsScreen +=
        `<div class="ssc-footer-w">
                <div ` + id + ` ` + cls + `><a>` + action + `</a></div>
            </div>
        </div>
    </div>`;
    return studentCircleCommentsScreen;
}

function getStudentReadCommentsScreen() {
    var studentReadCommentsScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">קריאת משובים</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">`;
    if (weeklyFinishedCircles.length > 0) {
        studentReadCommentsScreen +=
            `<div class="ssc-field" style="margin-top: 0;">
                        <div class="ssc-field-name">בחר/י&nbsp;חוג</div>
                        <select id="selectedCircleReadComments" class="ssc-field-value">`;
        for (let i = 0; i < weeklyFinishedCircles.length; i++) {
            studentReadCommentsScreen +=
                `<option>` + weeklyFinishedCircles[i][1] + `</option>`;
        }
        studentReadCommentsScreen +=
            `</select>
                    </div>
                    <div class="ssc-field-name" style="margin-top: 20px; width: 100%; height: 20px; line-height: 20px; direction: rtl; text-align: right;">משובים:</div>
                    <div class="student-list-items-w" style="margin-top: 20px;">
                        <div class="alt-text">טוען משובים</div>
                    </div>`;
    }
    else {
        studentReadCommentsScreen +=
            `<div class="alt-text">לא נמצאו עבורך משובים במערכת</div>`;
    }
    studentReadCommentsScreen +=
        `</div>
            <div class="ssc-footer-w">
                <div class="ssc-action-btn animated-transition noselect"><a>סגור/י</a></div>
            </div>
        </div>
    </div>`;
    return studentReadCommentsScreen;
}

function getInstructorReadCommentsScreen() {
    var instructorReadCommentsScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">קריאת משובים</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">`;
    if (weeklyFinishedCircles.length > 0) {
        instructorReadCommentsScreen +=
            `<div class="ssc-field" style="margin-top: 0;">
                        <div class="ssc-field-name">בחר/י&nbsp;חוג</div>
                        <select id="selectedCircleReadComments" class="ssc-field-value">`;
        for (let i = 0; i < weeklyFinishedCircles.length; i++) {
            instructorReadCommentsScreen +=
                `<option>` + weeklyFinishedCircles[i][1] + `</option>`;
        }
        instructorReadCommentsScreen +=
            `</select>
                    </div>
                    <div class="ssc-field-name" style="margin-top: 20px; width: 100%; height: 20px; line-height: 20px; direction: rtl; text-align: right;">משובים:</div>
                    <div class="student-list-items-w" style="margin-top: 20px;">
                        <div class="alt-text">טוען משובים</div>
                    </div>`;
    }
    else {
        instructorReadCommentsScreen +=
            `<div class="alt-text">עוד לא התקבלו משובים עבור חוגים בהנחייתך</div>`;
    }
    instructorReadCommentsScreen +=
        `</div>
            <div class="ssc-footer-w">
                <div class="ssc-action-btn animated-transition noselect"><a>סגור/י</a></div>
            </div>
        </div>
    </div>`;
    return instructorReadCommentsScreen;
}

function getInstructorCircleCommentsScreen() {
    var instructorCircleCommentsScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">הוסף/י משוב לתלמידים</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">`;
    if (circlesEligibleForComments.length > 0) {
        instructorCircleCommentsScreen +=
            `<div class="ssc-field" style="margin-top: 0;">
                        <div class="ssc-field-name">בחר/י&nbsp;חוג</div>
                        <select id="selectedCircleInstComment" class="ssc-field-value">`;
        for (let i = 0; i < circlesEligibleForComments.length; i++) {
            instructorCircleCommentsScreen +=
                `<option>` + circlesEligibleForComments[i][0] + `</option>`;
        }
        instructorCircleCommentsScreen +=
            `</select>
                    </div>
                    <div class="ssc-field">
                        <div class="ssc-field-name">בחר/י&nbsp;תלמיד</div>
                        <select id="selectedStudentInstComment" class="ssc-field-value">`;
        for (let i = 0; i < studentsOfCirclesEligibleForComments[0].length; i++) {
            instructorCircleCommentsScreen +=
                `<option>` + studentsOfCirclesEligibleForComments[0][i][1] + `</option>`;
        }
        if (studentsOfCirclesEligibleForComments[0].length == 0) {
            instructorCircleCommentsScreen +=
                `<option>לא קיימים משתתפים לחוג זה</option>`;
        }
        instructorCircleCommentsScreen +=
            `</select>
                    </div>
                    <div class="ssc-field" style="height: 20px;">
                        <div class="ssc-field-name" style="height: 20px; line-height: 20px;">דרג/י&nbsp;תלמיד</div>
                        <div class="rate-itm" style="margin-right: 10px;">1</div>
                        <div class="rate-itm" style="margin-right: 5px;">2</div>
                        <div class="rate-itm" style="margin-right: 5px;">3</div>
                        <div class="rate-itm" style="margin-right: 5px;">4</div>
                        <div class="rate-itm" style="margin-right: 5px;">5</div>
                    </div>
                    <div class="ssc-field-name" style="margin-top: 15px; width: 100%; height: 20px; line-height: 20px; direction: rtl; text-align: right;">תוכן המשוב:</div>
                    <textarea id="commentContent" class="field-textarea"></textarea>`;
    }
    else {
        var text = circlesEligibleForComments.length > 0 ? `בחר חוג על מנת להשאיר משוב`
            : `לא ניתן להגיב על חוגים כעת`;
        instructorCircleCommentsScreen +=
            `<div class="alt-text">` + text + `</div>`;
    }
    instructorCircleCommentsScreen +=
        `</div>`;
    var action = circlesEligibleForComments.length > 0 ? `שלח משוב` : `סגור`;
    var id = circlesEligibleForComments.length > 0 ? `id="instructorSendComment"` : ``;
    var cls = circlesEligibleForComments.length > 0 ? `class="ssc-disabled-action-btn animated-transition noselect"` :
        `class="ssc-action-btn animated-transition noselect"`;
    instructorCircleCommentsScreen +=
        `<div class="ssc-footer-w">
                <div ` + id + ` ` + cls + `><a>` + action + `</a></div>
            </div>
        </div>
    </div>`;
    return instructorCircleCommentsScreen;
}

function getRegisterStudentScreen() {
    var registerStudentScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">רשום/י תלמיד לחוג</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="ssc-field" style="margin-top: 0;">
                    <div class="ssc-field-name">בחר/י&nbsp;חוג</div>
                    <select id="selectedCircle" class="ssc-field-value">`;
    for (let i = 0; i < myCircles.length; i++) {
        registerStudentScreen +=
            `<option vlaue="` + myCircles[i][0] + `">` + myCircles[i][0] + `</option>`;
    }
    registerStudentScreen +=
        `</select>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">שם&nbsp;התלמיד</div>
                    <select id="studentId" class="ssc-field-value">`;
    for (let i = 0; i < students.length; i++) {
        registerStudentScreen +=
            `<option vlaue="` + students[i][0] + `">` + students[i][1] + ` ` + students[i][2] + ` - ` + students[i][3] + `</option>`;
    }
    registerStudentScreen +=
        `</select>
                </div>            
                <div class="loading">בחר/י חוג והכנס ת"ז של תלמיד כדי להציג מפגשים להרשמה</div>
            </div>
            <div class="ssc-footer-w">
                <div id="registerStudentToCircle" class="ssc-disabled-action-btn animated-transition noselect"><a>בצע/י רישום</a></div>
            </div>
        </div>
    </div>`;
    return registerStudentScreen;
}

function getStudentsScreen() {
    var studentsScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">התלמידים שלי</div>
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

function getAddStudentScreen() {
    var addStudentScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">הוספת תלמיד למערכת</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="ssc-field" style="margin-top: 0;">
                    <div class="ssc-field-name">שם&nbsp;פרטי</div>
                    <input type="text" id="firstName" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">שם&nbsp;משפחה</div>
                    <input type="text" id="lastName" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">מייל</div>
                    <input type="text" id="mail" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">פלאפון</div>
                    <input type="text" id="phone" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">תעודת&nbsp;זהות</div>
                    <input type="text" id="id" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">תאריך&nbsp;לידה</div>
                    <input type="date" id="birthDate" class="ssc-field-value" placeholder="בפורמט (1952-07-21)"/>
                </div>
            </div>
            <div class="ssc-footer-w">
                <div id="addNewStudent" class="ssc-disabled-action-btn noselect"><a>הוסף/י תלמיד</a></div>
            </div>
        </div>
    </div>`;
    return addStudentScreen;
}

function getAddInstructorScreen() {
    var addInstructorScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">הוספת מדריך למערכת</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="ssc-field" style="margin-top: 0;">
                    <div class="ssc-field-name">שם&nbsp;פרטי</div>
                    <input type="text" id="firstName" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">שם&nbsp;משפחה</div>
                    <input type="text" id="lastName" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">מייל</div>
                    <input type="text" id="mail" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">פלאפון</div>
                    <input type="text" id="phone" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">תעודת&nbsp;זהות</div>
                    <input type="text" id="id" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">תאריך&nbsp;לידה</div>
                    <input type="date" id="birthDate" class="ssc-field-value" placeholder="בפורמט (1952-07-21)"/>
                </div>
            </div>
            <div class="ssc-footer-w">
                <div id="addNewInstructor" class="ssc-disabled-action-btn noselect"><a>הוסף/י מדריך</a></div>
            </div>
        </div>
    </div>`;
    return addInstructorScreen;
}

function getAddEventScreen() {
    var newEventScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">יצירת אירוע חדש במערכת</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="ssc-field" style="margin-top: 0;">
                    <div class="ssc-field-name">שם&nbsp;האירוע</div>
                    <input type="text" id="eventName" class="ssc-field-value"/>
                </div>
                <div class="ssc-field-name" style="display: block; margin-top: 20px; width: 100%; height: 20px; line-height: 20px; direction: rtl; text-align: right;">תיאור&nbsp;האירוע</div>
                <textarea id="eventDescription" class="field-textarea"></textarea>                
                <div class="ssc-field">
                    <div class="ssc-field-name">מדריך</div>
                    <select id="selectInstructor" class="ssc-field-value">
                        <option>ללא מדריך</option>`;
    for (let i = 0; i < instructors.length; i++) {
        newEventScreen += `<option>` + instructors[i][0] + `</option>`;
    }
    newEventScreen +=
        `</select>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">תאריך&nbsp;התחלה</div>
                    <input type="date" id="eventStartDate" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">שעת&nbsp;התחלה</div>
                    <input type="text" id="eventStartHour" class="ssc-field-value" placeholder="שעה (בפורמט: 00:00:00)"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">תאריך&nbsp;סיום</div>
                    <input type="date" id="eventEndDate" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">שעת&nbsp;סיום</div>
                    <input type="text" id="eventEndHour" class="ssc-field-value" placeholder="שעה (בפורמט: 00:00:00)"/>
                </div>
            </div>
            <div class="ssc-footer-w">
                <div id="createNewEvent" class="ssc-disabled-action-btn noselect"><a>צור/י אירוע</a></div>
            </div>
        </div>
    </div>`;
    return newEventScreen;
}

function getNewCircleScreen() {
    var newCircleScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">יצירת חוג חדש במערכת</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="ssc-field" style="margin-top: 0;">
                    <div class="ssc-field-name">שם&nbsp;החוג</div>
                    <input type="text" id="circleName" class="ssc-field-value"/>
                </div>
                <div class="ssc-field">
                    <div class="ssc-field-name">תיאור&nbsp;החוג</div>
                    <input type="text" id="circleDescription" class="ssc-field-value"/>
                </div>
            </div>
            <div class="ssc-footer-w">
                <div id="createNewCircle" class="ssc-disabled-action-btn noselect"><a>צור/י חוג</a></div>
            </div>
        </div>
    </div>`;
    return newCircleScreen;
}

function getCircleRegistrationScreen() {
    var registrationScreen =
        `<div class="secondary-screen-w">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">הרשם לחוג</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="loading">...טוען נתונים</div>
            </div>
            <div class="ssc-footer-w">
                <div id="registerToCircle" class="ssc-disabled-action-btn animated-transition
                 noselect"><a>בצע הרשמה</a></div>
            </div>
        </div>
    </div>`;
    return registrationScreen;
}

function getSendManagerMessageScreen() {
    var sendManagerMessageScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">שלח הודעה</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="ssc-field" style="margin-top: 0;">
                    <div class="ssc-field-name">שלח ל:</div>
                    <select id="selectedTarget" class="ssc-field-value">
                    <option>לכולם</option>`;
    for (let i = 0; i < instructors.length; i++) {
        sendManagerMessageScreen +=
            `<option>` + instructors[i][0] + `</option>`;
    }
    sendManagerMessageScreen +=
        `</select>
                </div>
                <div class="ssc-field-name" style="margin-top: 20px; width: 100%; height: 20px; line-height: 20px; direction: rtl; text-align: right;">תוכן ההודעה:</div>
                <textarea id="messageContent" class="field-textarea"></textarea>
            </div>
            <div class="ssc-footer-w">
                <div id="publishManagerMessage" class="ssc-disabled-action-btn noselect"><a>שלח/י הודעה</a></div>
            </div>
        </div>
    </div>`;
    return sendManagerMessageScreen;
}

function getSendInstructorMessageScreen() {
    var sendInstructorMessageScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">שלח הודעה לתלמידים</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="ssc-field-name" style="width: 100%; height: 20px; line-height: 20px; direction: rtl; text-align: right;">תוכן ההודעה:</div>
                <textarea id="messageContent" class="field-textarea"></textarea>
                <div class="student-list-items-w" style="margin-top: 25px;">
                    <div class="loading">...טוען נתונים</div>
                </div>
            </div>
            <div class="ssc-footer-w">
                <div id="publishInstructorMessage" class="ssc-disabled-action-btn noselect"><a>שלח/י הודעה</a></div>
            </div>
        </div>
    </div>`;
    return sendInstructorMessageScreen;
}

function getInstructorMessagesScreen() {
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

function getStudentCirclesScreen() {
    var studentCirclesScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">החוגים שלי</div>
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
    return studentCirclesScreen;
}

function getEditEventScreen() {
    var editEventScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">עריכת אירוע</div>
                <img class="close-secondary-sc" src="http://` + domain + `/images/close.png"/>
            </div>
            <div class="ssc-body-w">
                <div class="loading">...טוען נתונים</div>
            </div>
            <div class="ssc-footer-w">
                <div id="saveEditEvent" class="ssc-action-btn animated-transition noselect"><a>שמור/י</a></div>
            </div>
        </div>
    </div>`;
    return editEventScreen;
}

function getInstructorCirclesScreen() {
    var instructorCirclesScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">החוגים שלי</div>
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
    return instructorCirclesScreen;
}

function getInstructorEventScreen() {
    var instructorEventsScreen =
        `<div class="secondary-screen-w noselect">
        <div class="secondary-screen" style="width: 460px;">
            <div class="secondary-sc-title-w">
                <div class="secondary-sc-title">האירועים שלי</div>
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
    return instructorEventsScreen;
}

function resizeEndded() {
    if (new Date() - resizeTime < resizeDelta) {
        setTimeout(resizeEndded, resizeDelta);
    }
    else {
        resizeTimeout = false;
        resize();
    }
}

$(window).on("resize", function () {
    resize();
    resizeTime = new Date();
    if (resizeTimeout === false) {
        resizeTimeout = true;
        setTimeout(resizeEndded, resizeDelta);
    }
});

$(window).on("load", function () {
    resize();
    addEvents();
});