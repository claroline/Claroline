function APIClass()
{
    //SCORM 1.2
    this.LMSInitialize = LMSInitialize;
    this.LMSFinish = LMSFinish;
    this.LMSGetValue = LMSGetValue;
    this.LMSSetValue = LMSSetValue;
    this.LMSCommit = LMSCommit;
    this.LMSGetLastError = LMSGetLastError;
    this.LMSGetErrorString = LMSGetErrorString;
    this.LMSGetDiagnostic = LMSGetDiagnostic;
}

function LMSInitialize(arg)
{
    console.log('*** LMSInitialize ***');

    if (arg != "") {
        this.apiLastError = "201";

        return "false";
    }
    this.apiLastError = "0";
    this.apiInitialized = true;

    return "true";
}

function LMSFinish(arg)
{
    console.log('*** LMSFinish ***');

    if (this.apiInitialized) {

        if (arg != "") {
            this.apiLastError = "201";

            return "false";
        }
        this.apiLastError = "0";
        this.apiInitialized = false;

        return "true";
    } else {
        this.apiLastError = "301";   // not initialized

        return "false";
    }
}

function LMSGetValue(arg)
{
    console.log('*** LMSGetValue:: ' + arg + ' ***');

    if (this.apiInitialized) {

        switch (arg) {
            case 'cmi.core._children' :
            case 'cmi.core.student_id' :
            case 'cmi.core.student_name' :
            case 'cmi.core.lesson_location' :
            case 'cmi.core.credit' :
            case 'cmi.core.lesson_status' :
            case 'cmi.core.entry' :
            case 'cmi.core.score._children' :
            case 'cmi.core.score.raw' :
            case 'cmi.core.score.min' :
            case 'cmi.core.score.max' :
            case 'cmi.core.total_time' :
            case 'cmi.suspend_data' :
            case 'cmi.launch_data' :
            case 'cmi.core.lesson_mode' :
                this.apiLastError = "0";

                return scoData[arg];
            case 'cmi.core.exit' :
            case 'cmi.core.session_time' :
                this.apiLastError = "404"; // write only

                return "";
            default :
                this.apiLastError = "401";

                return "";
       }
    } else {
        // not initialized error
        this.apiLastError = "301";

        return "";
    }
}

function LMSSetValue(argName, argValue)
{
    console.log('*** LMSSetValue:: [' + argName + '] = ' + argValue + ' ***');

    if (this.apiInitialized) {

        switch (argName) {
            case 'cmi.core._children' :
            case 'cmi.core.score._children' :
                this.apiLastError = "402"; // invalid set value, element is a keyword

                return "false";
            case 'cmi.core.student_id' :
            case 'cmi.core.student_name' :
            case 'cmi.core.credit' :
            case 'cmi.core.entry' :
            case 'cmi.core.total_time' :
            case 'cmi.launch_data' :
            case 'cmi.core.lesson_mode' :
                this.apiLastError = "403"; // read only

                return "false";
            case 'cmi.core.lesson_location' :

                if (argValue.length > 255) {
                    this.apiLastError = "405";

                    return "false";
                }
                scoData[argName] = argValue;
                this.apiLastError = "0";

                return "true";
            case 'cmi.core.lesson_status' :
                upperCaseVal = argValue.toUpperCase();

                if (
                    upperCaseVal != "PASSED"
                    && upperCaseVal != "FAILED"
                    && upperCaseVal != "COMPLETED"
                    && upperCaseVal != "INCOMPLETE"
                    && upperCaseVal != "BROWSED"
                    && upperCaseVal != "NOT ATTEMPTED"
                ) {
                    this.apiLastError = "405";

                    return "false";
                }
                scoData[argName] = argValue;
                this.apiLastError = "0";

                return "true";
            case 'cmi.core.score.raw' :
            case 'cmi.core.score.min' :
            case 'cmi.core.score.max' :

                if (isNaN(parseInt(argValue)) || (argValue < 0) || (argValue > 100)) {
                    this.apiLastError = "405";
                    return "false";
                }
                scoData[argName] = argValue;
                this.apiLastError = "0";

                return "true";
            case 'cmi.core.exit' :
                upperCaseVal = argValue.toUpperCase();

                if (
                    upperCaseVal != "TIME-OUT"
                    && upperCaseVal != "SUSPEND"
                    && upperCaseVal != "LOGOUT"
                    && upperCaseVal != ""
                ) {
                    this.apiLastError = "405";

                    return "false";
                }
                scoData[argName] = argValue;
                this.apiLastError = "0";

                return "true";
            case 'cmi.core.session_time' :
                // regexp to check format
                // hhhh:mm:ss.ss
                var re = /^[0-9]{2,4}:[0-9]{2}:[0-9]{2}(.[0-9]{1,2})?$/;

                if (!re.test(argValue)) {
                    this.apiLastError = "405";

                    return "false";
                }
                // check that minuts and second are 0 <= x < 60
                var splitted_val = argValue.split(":");

                if ( splitted_val[1] < 0 || splitted_val[1] >= 60 || splitted_val[2] < 0 || splitted_val[2] >= 60 ) {
                    this.apiLastError = "405";

                    return "false";
                }

                scoData[argName] = argValue;
                this.apiLastError = "0";

                return "true";
            case 'cmi.suspend_data' :
                if (argValue.length > 4096) {
                    this.apiLastError = "405";
                    return "false";
                }
                scoData[argName] = argValue;
                this.apiLastError = "0";

                return "true";
            default :
                this.apiLastError = "401";

                return "false";
        }
    } else {
        // not initialized error
        this.apiLastError = "301";

        return "false";
    }
}

function LMSGetLastError()
{
    console.log('*** LMSGetLastError:: ' + this.apiLastError + ' ***');

    return this.apiLastError;
}

function LMSGetErrorString(errorCode)
{
    console.log('*** LMSGetErrorString:: [' + errorCode + '] ***');

    switch (errorCode) {
        case "0" :
        case "101" :
        case "201" :
        case "202" :
        case "203" :
        case "301" :
        case "401" :
        case "402" :
        case "403" :
        case "404" :
        case "405" :

            return this.errorString[errorCode];
        default :

            return "Unknown Error";
    }
}

function LMSGetDiagnostic(errorCode)
{
    var index = errorCode;

    if (index == "") {
        index = this.apiLastError;
    }
    console.log('*** LMSGetDiagnostic:: [' + index + '] ***');

    switch (index) {
        case "0" :
        case "101" :
        case "201" :
        case "202" :
        case "203" :
        case "301" :
        case "401" :
        case "402" :
        case "403" :
        case "404" :
        case "405" :

            return this.errorString[index];
        default :

            return "Unknown Error";
    }
}

function LMSCommit(arg)
{
    console.log('*** LMSCommit ***');

    if (this.apiInitialized) {
        if (arg != "") {
            this.apiLastError = "201";

            return "false";
        } else {
            this.apiLastError = "0";
            commitResult();

            return "true";
        }
    } else {
        this.apiLastError = "301";

        return "false";
    }
}

function commitResult()
{
    var datasString = "" + scormId
                    + "<-;->" + scoData["cmi.core.student_id"]
                    + "<-;->" + scoData["cmi.core.lesson_mode"]
                    + "<-;->" + scoData["cmi.core.lesson_location"]
                    + "<-;->" + scoData["cmi.core.lesson_status"]
                    + "<-;->" + scoData["cmi.core.credit"]
                    + "<-;->" + scoData["cmi.core.score.raw"]
                    + "<-;->" + scoData["cmi.core.score.min"]
                    + "<-;->" + scoData["cmi.core.score.max"]
                    + "<-;->" + scoData["cmi.core.session_time"]
                    + "<-;->" + scoData["cmi.core.total_time"]
                    + "<-;->" + scoData["cmi.suspend_data"]
                    + "<-;->" + scoData["cmi.core.entry"]
                    + "<-;->" + scoData["cmi.core.exit"]
    $.ajax({
        url: Routing.generate(
            'claro_scorm_info_commit',
            {
                'datasString': datasString
            }
        ),
        type: 'POST',
        success: function () {
            console.log("*** Commit Succeded ***");
        }
    });
}

var API = new APIClass();
var api = new APIClass();

var scoData = new Array();
scoData["cmi.core.student_id"] = document.getElementById('twig-scorm-data').getAttribute('student-id');
scoData["cmi.core.student_name"] = document.getElementById('twig-scorm-data').getAttribute('student-name');
scoData["cmi.core.lesson_mode"] = document.getElementById('twig-scorm-data').getAttribute('lesson-mode');
scoData["cmi.core.lesson_location"] = document.getElementById('twig-scorm-data').getAttribute('lesson-location');
scoData["cmi.core.lesson_status"] = document.getElementById('twig-scorm-data').getAttribute('lesson-status');
scoData["cmi.core.credit"] = document.getElementById('twig-scorm-data').getAttribute('credit');
scoData["cmi.core.score.raw"] = document.getElementById('twig-scorm-data').getAttribute('score-raw');
scoData["cmi.core.score.max"] = document.getElementById('twig-scorm-data').getAttribute('score-max');
scoData["cmi.core.score.min"] = document.getElementById('twig-scorm-data').getAttribute('score-min');
scoData["cmi.core.total_time"] = document.getElementById('twig-scorm-data').getAttribute('total-time');
scoData["cmi.core.total_time"] = "" + (scoData["cmi.core.total_time"] / 144000) + ':' + (scoData["cmi.core.total_time"] / 6000) + ':' + (scoData["cmi.core.total_time"] / 100) + '.' + (scoData["cmi.core.total_time"] % 100);
scoData["cmi.core.entry"] = document.getElementById('twig-scorm-data').getAttribute('entry');
scoData["cmi.suspend_data"] = document.getElementById('twig-scorm-data').getAttribute('suspend-data');
scoData["cmi.launch_data"] = document.getElementById('twig-scorm-data').getAttribute('launch-data');
scoData["cmi.core._children"] = "student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,lesson_mode,exit,session_time";
scoData["cmi.core.score._children"] = "raw,min,max";
scoData["cmi.core.session_time"] = "0000:00:00.00";
scoData["cmi.core.exit"] = "";

var errorString = new Array();
errorString["0"] = "No error";
errorString["101"] = "General Exception";
errorString["201"] = "Invalid Argument Error";
errorString["202"] = "Element cannot have children";
errorString["203"] = "Element not an array.  Cannot have count";
errorString["301"] = "Not initialized";
errorString["401"] = "Not implemented error";
errorString["402"] = "Invalid set value, element is a keyword";
errorString["403"] = "Element is read only";
errorString["404"] = "Element is write only";
errorString["405"] = "Incorrect Data Type";

var scormId = document.getElementById('twig-scorm-data').getAttribute('scorm-id');
var apiInitialized = false;
var apiLastError = "301";