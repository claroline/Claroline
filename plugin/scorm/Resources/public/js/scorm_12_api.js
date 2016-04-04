var scoData = [];
scoData['cmi.core.student_id'] = document.getElementById('twig-scorm-data').getAttribute('student-id');
scoData['cmi.core.student_name'] = document.getElementById('twig-scorm-data').getAttribute('student-name');
scoData['cmi.core.lesson_mode'] = document.getElementById('twig-scorm-data').getAttribute('lesson-mode');
scoData['cmi.core.lesson_location'] = document.getElementById('twig-scorm-data').getAttribute('lesson-location');
scoData['cmi.core.lesson_status'] = document.getElementById('twig-scorm-data').getAttribute('lesson-status');
scoData['cmi.core.credit'] = document.getElementById('twig-scorm-data').getAttribute('credit');
scoData['cmi.core.score.raw'] = document.getElementById('twig-scorm-data').getAttribute('score-raw');
scoData['cmi.core.score.max'] = document.getElementById('twig-scorm-data').getAttribute('score-max');
scoData['cmi.core.score.min'] = document.getElementById('twig-scorm-data').getAttribute('score-min');

var totalTime = document.getElementById('twig-scorm-data').getAttribute('total-time');
var totalTimeHour = totalTime / 144000;
totalTime %= 144000;
var totalTimeMinte = totalTime / 6000;
totalTime %= 6000;
var totalTimeSecond = totalTime / 100;
totalTime %= 100;

scoData['cmi.core.total_time'] = '' + totalTimeHour + ':' + totalTimeMinte + ':' +
    totalTimeSecond + '.' + totalTime;
scoData['cmi.core.entry'] = document.getElementById('twig-scorm-data').getAttribute('entry');
scoData['cmi.suspend_data'] = document.getElementById('twig-scorm-data').getAttribute('suspend-data');
scoData['cmi.launch_data'] = document.getElementById('twig-scorm-data').getAttribute('launch-data');
scoData['cmi.core._children'] = 'student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,lesson_mode,exit,session_time';
scoData['cmi.core.score._children'] = 'raw,min,max';
scoData['cmi.core.session_time'] = '0000:00:00.00';
scoData['cmi.core.exit'] = '';
scoData['cmi.student_data._children'] = 'mastery_score,time_limit_action,max_time_allowed';
scoData['cmi.student_data.mastery_score'] = document.getElementById('twig-scorm-data').getAttribute('mastery-score');
scoData['cmi.student_data.max_time_allowed'] = document.getElementById('twig-scorm-data').getAttribute('max-time-allowed');
scoData['cmi.student_data.time_limit_action'] = document.getElementById('twig-scorm-data').getAttribute('time-limit-action');

var errorString = [];
errorString['0'] = 'No error';
errorString['101'] = 'General Exception';
errorString['201'] = 'Invalid Argument Error';
errorString['202'] = 'Element cannot have children';
errorString['203'] = 'Element not an array.  Cannot have count';
errorString['301'] = 'Not initialized';
errorString['401'] = 'Not implemented error';
errorString['402'] = 'Invalid set value, element is a keyword';
errorString['403'] = 'Element is read only';
errorString['404'] = 'Element is write only';
errorString['405'] = 'Incorrect Data Type';

var scoId = document.getElementById('twig-scorm-data').getAttribute('sco-id');
var apiInitialized = false;
var apiLastError = '301';

function commitResult(mode)
{
    'use strict';

    var datasString = scoData['cmi.core.student_id'] +
        '<-;->' + scoData['cmi.core.lesson_mode'] +
        '<-;->' + scoData['cmi.core.lesson_location'] +
        '<-;->' + scoData['cmi.core.lesson_status'] +
        '<-;->' + scoData['cmi.core.credit'] +
        '<-;->' + scoData['cmi.core.score.raw'] +
        '<-;->' + scoData['cmi.core.score.min'] +
        '<-;->' + scoData['cmi.core.score.max'] +
        '<-;->' + scoData['cmi.core.session_time'] +
        '<-;->' + scoData['cmi.core.total_time'] +
        '<-;->' + scoData['cmi.suspend_data'] +
        '<-;->' + scoData['cmi.core.entry'] +
        '<-;->' + scoData['cmi.core.exit'];
    $.ajax({
        async: false,
        url: Routing.generate(
            'claro_scorm_12_tracking_commit',
            {'datasString': datasString, 'mode': mode, 'scoId': scoId}
        ),
        type: 'POST',
        success: function () {
            console.log('*** Commit Succeded ***');
        }
    });
}

function LMSInitialize(arg)
{
    'use strict';

    console.log('*** LMSInitialize ***');

    if (arg != '') {
        apiLastError = '201';

        return 'false';
    }
    apiLastError = '0';
    apiInitialized = true;

    return 'true';
}

function LMSFinish(arg)
{
    'use strict';

    console.log('*** LMSFinish ***');

    if (apiInitialized) {

        if (arg != '') {
            apiLastError = '201';

            return 'false';
        }
        apiLastError = '0';
        apiInitialized = false;
        // Set value for 'cmi.core.entry' depending on 'cmi.core.exit'
        if (scoData['cmi.core.exit'].toUpperCase() === 'SUSPEND') {
            scoData['cmi.core.entry'] = 'resume';
        } else {
            scoData['cmi.core.entry'] = '';
        }
        commitResult('log');

        return 'true';
    } else {
        apiLastError = '301';   // not initialized

        return 'false';
    }
}

function LMSGetValue(arg)
{
    'use strict';

    console.log('*** LMSGetValue:: ' + arg + ' ***');

    if (apiInitialized) {

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
            case 'cmi.student_data._children' :
            case 'cmi.student_data.mastery_score' :
            case 'cmi.student_data.max_time_allowed' :
            case 'cmi.student_data.time_limit_action' :
                apiLastError = '0';

                return scoData[arg];
            case 'cmi.core.exit' :
            case 'cmi.core.session_time' :
                apiLastError = '404'; // write only

                return '';
            default :
                apiLastError = '401';

                return '';
        }
    } else {
        // not initialized error
        apiLastError = '301';

        return '';
    }
}

function LMSSetValue(argName, argValue)
{
    'use strict';

    console.log('*** LMSSetValue:: [' + argName + '] = ' + argValue + ' ***');

    if (apiInitialized) {

        switch (argName) {
            case 'cmi.core._children' :
            case 'cmi.core.score._children' :
            case 'cmi.student_data._children' :
                apiLastError = '402'; // invalid set value, element is a keyword

                return 'false';
            case 'cmi.core.student_id' :
            case 'cmi.core.student_name' :
            case 'cmi.core.credit' :
            case 'cmi.core.entry' :
            case 'cmi.core.total_time' :
            case 'cmi.launch_data' :
            case 'cmi.core.lesson_mode' :
            case 'cmi.student_data.mastery_score' :
            case 'cmi.student_data.max_time_allowed' :
            case 'cmi.student_data.time_limit_action' :
                apiLastError = '403'; // read only

                return 'false';
            case 'cmi.core.lesson_location' :

                if (argValue.length > 255) {
                    apiLastError = '405';

                    return 'false';
                }
                scoData[argName] = argValue;
                apiLastError = '0';

                return 'true';
            case 'cmi.core.lesson_status' :
                var upperCaseLessonStatus = argValue.toUpperCase();

                if (upperCaseLessonStatus !== 'PASSED' &&
                    upperCaseLessonStatus !== 'FAILED' &&
                    upperCaseLessonStatus !== 'COMPLETED' &&
                    upperCaseLessonStatus !== 'INCOMPLETE' &&
                    upperCaseLessonStatus !== 'BROWSED' &&
                    upperCaseLessonStatus !== 'NOT ATTEMPTED') {

                    apiLastError = '405';

                    return 'false';
                }
                scoData[argName] = argValue;
                apiLastError = '0';

                return 'true';
            case 'cmi.core.score.raw' :
            case 'cmi.core.score.min' :
            case 'cmi.core.score.max' :

                if (isNaN(parseInt(argValue)) || (argValue < 0) || (argValue > 100)) {
                    apiLastError = '405';
                    return 'false';
                }
                scoData[argName] = argValue;
                apiLastError = '0';

                return 'true';
            case 'cmi.core.exit' :
                var upperCaseExit = argValue.toUpperCase();

                if (upperCaseExit !== 'TIME-OUT' &&
                    upperCaseExit !== 'SUSPEND' &&
                    upperCaseExit !== 'LOGOUT' &&
                    upperCaseExit !== '') {

                    apiLastError = '405';

                    return 'false';
                }
                scoData[argName] = argValue;
                apiLastError = '0';

                return 'true';
            case 'cmi.core.session_time' :
                // regex to check format
                // hhhh:mm:ss.ss
                var re = /^[0-9]{2,4}:[0-9]{2}:[0-9]{2}(.[0-9]{1,2})?$/;

                if (!re.test(argValue)) {
                    apiLastError = '405';

                    return 'false';
                }
                // check that minute and second are 0 <= x < 60
                var timeArray = argValue.split(':');

                if (timeArray[1] < 0 || timeArray[1] >= 60 || timeArray[2] < 0 || timeArray[2] >= 60) {
                    apiLastError = '405';

                    return 'false';
                }

                scoData[argName] = argValue;
                apiLastError = '0';

                return 'true';
            case 'cmi.suspend_data' :
                if (argValue.length > 4096) {
                    apiLastError = '405';
                    return 'false';
                }
                scoData[argName] = argValue;
                apiLastError = '0';

                return 'true';
            default :
                apiLastError = '401';

                return 'false';
        }
    } else {
        // not initialized error
        apiLastError = '301';

        return 'false';
    }
}

function LMSGetLastError()
{
    'use strict';

    console.log('*** LMSGetLastError:: ' + apiLastError + ' ***');

    return apiLastError;
}

function LMSGetErrorString(errorCode)
{
    'use strict';

    console.log('*** LMSGetErrorString:: [' + errorCode + '] ***');

    switch (errorCode) {
        case '0' :
        case '101' :
        case '201' :
        case '202' :
        case '203' :
        case '301' :
        case '401' :
        case '402' :
        case '403' :
        case '404' :
        case '405' :

            return errorString[errorCode];
        default :

            return 'Unknown Error';
    }
}

function LMSGetDiagnostic(errorCode)
{
    'use strict';

    var index = errorCode;

    if (index === '') {
        index = apiLastError;
    }
    console.log('*** LMSGetDiagnostic:: [' + index + '] ***');

    switch (index) {
        case '0' :
        case '101' :
        case '201' :
        case '202' :
        case '203' :
        case '301' :
        case '401' :
        case '402' :
        case '403' :
        case '404' :
        case '405' :

            return errorString[index];
        default :

            return 'Unknown Error';
    }
}

function LMSCommit(arg)
{
    'use strict';

    console.log('*** LMSCommit ***');

    if (apiInitialized) {
        if (arg != '') {
            apiLastError = '201';

            return 'false';
        } else {
            apiLastError = '0';
            commitResult('persist');

            return 'true';
        }
    } else {
        apiLastError = '301';

        return 'false';
    }
}

function APIClass()
{
    'use strict';
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

var API = new APIClass();
var api = new APIClass();