var scoData = {};

if (trackingDetails !== undefined) {
    scoData = trackingDetails;
}

scoData['cmi.credit'] = 'credit';
scoData['cmi.interactions._children'] = 'id,type,objectives,timestamp,correct_responses,weighting,learner_response,result,latency,description';
scoData['cmi.learner_preference._children'] = 'audio_level,language,delivery_speed,audio_captioning';
scoData['cmi.mode'] = 'normal';
scoData['cmi.objectives._children'] = 'id,score,success_status,completion_status,progress_measure,description';
scoData['cmi.score._children'] = 'scaled,raw,min,max';

if (scoData['cmi.comments_from_learner'] === undefined) {
    scoData['cmi.comments_from_learner'] = {};
    scoData['cmi.comments_from_learner._children'] = 'comment,location,timestamp';
}

if (scoData['cmi.comments_from_lms'] === undefined) {
    scoData['cmi.comments_from_lms'] = {};
    scoData['cmi.comments_from_lms._children'] = 'comment,location,timestamp';
}

if (scoData['cmi.completion_status'] === undefined) {
    scoData['cmi.completion_status'] = 'unknown';
}

if (scoData['cmi.entry'] === undefined) {
    scoData['cmi.entry'] = '';
}

if (scoData['cmi.exit'] === undefined) {
    scoData['cmi.exit'] = '';
}

if (scoData['cmi.interactions'] === undefined) {
    scoData['cmi.interactions'] = {};
}

if (scoData['cmi.location'] === undefined) {
    scoData['cmi.location'] = '';
}

if (scoData['cmi.objectives'] === undefined) {
    scoData['cmi.objectives'] = {};
}

if (scoData['cmi.success_status'] === undefined) {
    scoData['cmi.success_status'] = 'unknown';
}

if (scoData['cmi.time_limit_action'] === undefined) {
    scoData['cmi.time_limit_action'] = 'continue,no message';
}

if (scoData['cmi.total_time'] === undefined) {
    scoData['cmi.total_time'] = 'PT0S';
}

var errorString = {};
errorString['0'] = 'No error';
errorString['101'] = 'General Exception';
errorString['102'] = 'General Initialization Failure';
errorString['103'] = 'Already Initialized';
errorString['104'] = 'Content Instance Terminated';
errorString['111'] = 'General Termination Failure';
errorString['112'] = 'Termination Before Initialization';
errorString['113'] = 'Termination After Termination';
errorString['122'] = 'Retrieve Data Before Initialization';
errorString['123'] = 'Retrieve Data After Termination';
errorString['132'] = 'Store Data Before Initialization';
errorString['133'] = 'Store Data After Termination';
errorString['142'] = 'Commit Before Initialization';
errorString['143'] = 'Commit After Termination';
errorString['201'] = 'General Argument Error';
errorString['301'] = 'General Get Failure';
errorString['351'] = 'General Set Failure';
errorString['391'] = 'General Commit Failure';
errorString['401'] = 'Undefined Data Model Element';
errorString['402'] = 'Unimplemented Data Model Element';
errorString['403'] = 'Data Model Element Value Not Initialized';
errorString['404'] = 'Data Model Element Is Read Only';
errorString['405'] = 'Data Model Element Is Write Only';
errorString['406'] = 'Data Model Element Type Mismatch';
errorString['407'] = 'Data Model Element Value Out Of Range';
errorString['408'] = 'Data Model Dependency Not Established';

var scoId = document.getElementById('twig-scorm-data').getAttribute('data-sco-id');
var apiInitialized = false;
var apiTerminated = false;
var apiLastError = '0';
var pattern = '^[-]?[0-9]+([.][0-9]{1,7})?$';
var regex = new RegExp(pattern);

function commitResult(mode)
{
    'use strict';
    
    $.ajax({
        async: false,
        url: Routing.generate(
            'claro_scorm_2004_tracking_commit',
            {'mode': mode, 'scoId': scoId}
        ),
        type: 'POST',
        data: JSON.stringify(scoData),
        contentType: 'application/json',
        dataType: 'json',
        success: function () {
            console.log('*** Commit Succeded ***');
        }
    });
}

function Initialize(arg)
{
    'use strict';

    console.log('*** Initialize ***');

    if (arg !== '') {
        // General Argument Error
        apiLastError = '201';

        return 'false';
    } else if (apiInitialized) {
        // Already Initialized
        apiLastError = '103';

        return 'false';
    } else if (apiTerminated) {
        // Content Instance Terminated
        apiLastError = '104';

        return 'false';
    }
    apiLastError = '0';
    apiInitialized = true;

    return 'true';
}

function Terminate(arg)
{
    'use strict';

    console.log('*** Terminate ***');

    if (arg !== '') {
        // General Argument Error
        apiLastError = '201';

        return 'false';
    } else if (!apiInitialized) {
        // Termination Before Initialization
        apiLastError = '112';

        return 'false';
    } else if (apiTerminated) {
        // Termination After Termination
        apiLastError = '113';

        return 'false';
    }
    apiLastError = '0';
    apiTerminated = true;
    apiInitialized = false;
    // Set value for 'cmi.entry' depending on 'cmi.exit'
    if (scoData['cmi.exit'].toUpperCase() === 'SUSPEND' ||
        scoData['cmi.exit'].toUpperCase() === 'LOGOUT') {
    
        scoData['cmi.entry'] = 'resume';
    } else {
        scoData['cmi.entry'] = '';
    }
    commitResult('log');

    return 'true';
}

function GetValue(arg)
{
    'use strict';

    console.log('*** GetValue:: ' + arg + ' ***');

    if (!apiInitialized) {
        // Retrieve Data Before Initialization
        apiLastError = '122';

        return '';
    } else if (apiTerminated) {
        // Retrieve Data After Termination
        apiLastError = '123';

        return '';
    }

    switch (arg) {
        case 'cmi.comments_from_learner':
        case 'cmi.comments_from_lms':
        case 'cmi.interactions':
        case 'cmi.learner_preference':
        case 'cmi.objectives':
        case 'cmi.score':
            // General Get Failure
            apiLastError = '301';
        
            return '';
            
        case 'cmi.exit':
        case 'cmi.session_time':
            // Data Model Element Is Write Only
            apiLastError = '405';
        
            return '';
            
        case 'cmi.comments_from_learner._count':
            apiLastError = '0';
        
            return scoData['cmi.comments_from_learner'].length;
            
        case 'cmi.comments_from_lms._count':
            apiLastError = '0';
        
            return scoData['cmi.comments_from_lms'].length;
            
        case 'cmi.interactions._count':
            apiLastError = '0';
        
            return scoData['cmi.interactions'].length;
            
        case 'cmi.objectives._count':
            apiLastError = '0';
            
            return scoData['cmi.objectives'].length;
            
        case 'cmi.comments_from_learner._children':
        case 'cmi.comments_from_lms._children':
        case 'cmi.completion_status':
        case 'cmi.completion_threshold':
        case 'cmi.credit':
        case 'cmi.entry':
        case 'cmi.interactions._children':
        case 'cmi.launch_data':
        case 'cmi.learner_id':
        case 'cmi.learner_name':
        case 'cmi.learner_preference._children':
        case 'cmi.location':
        case 'cmi.max_time_allowed':
        case 'cmi.mode':
        case 'cmi.objectives._children':
        case 'cmi.scaled_passing_score':
        case 'cmi.score._children':
        case 'cmi.time_limit_action':
        case 'cmi.total_time':
            apiLastError = '0';

            return scoData[arg];
            
        case 'cmi.learner_preference.audio_level':
        case 'cmi.learner_preference.language':
        case 'cmi.learner_preference.delivery_speed':
        case 'cmi.learner_preference.audio_captioning':
        case 'cmi.progress_measure':
        case 'cmi.score.scaled':
        case 'cmi.score.raw':
        case 'cmi.score.max':
        case 'cmi.score.min':
        case 'cmi.success_status':
        case 'cmi.suspend_data':
            
            if (scoData[arg] === undefined) {
                // Data Model Element Value Not Initialized
                apiLastError = '403';
                
                return '';
            }
            apiLastError = '0';

            return scoData[arg];
            
        default:
            var splitted = arg.split('.');
            
            if (splitted[0] === 'cmi' && splitted[2].trim()) {
            
                var cmiIndex = 'cmi.' + splitted[1];
                var nIndex = splitted[2];
                
                switch (splitted[1]) {
                    case 'comments_from_learner' :
                    case 'comments_from_lms' :
                    
                        if (scoData[cmiIndex][nIndex] !== undefined) {
                            
                            switch (splitted[3]) {
                                case 'comment':
                                case 'location':
                                case 'timestamp':
                                    
                                    if (scoData[cmiIndex][nIndex][splitted[3]] !== undefined) {
                                        apiLastError = '0';

                                        return scoData[cmiIndex][nIndex][splitted[3]];
                                    } else {
                                        // Data Model Element Value Not Initialized
                                        apiLastError = '403';
                                        
                                        return '';
                                    }
                                    break;
                                    
                                default:
                                    // Undefined Data Model Element
                                    apiLastError = '401';
                                    
                                    return '';
                            }
                        } else {
                            
                            switch (splitted[3]) {
                                case 'comment':
                                case 'location':
                                case 'timestamp':
                                    // Data Model Element Value Not Initialized
                                    apiLastError = '403';
                                    break;
                                    
                                default:
                                    // Undefined Data Model Element
                                    apiLastError = '401';
                            }
                            
                            return '';
                        }
                        break;
                        
                    case 'interactions' :
                    
                        if (scoData[cmiIndex][nIndex] !== undefined) {
                            
                            switch (splitted[3]) {
                                case 'id':
                                case 'type':
                                case 'timestamp':
                                case 'weighting':
                                case 'learner_response':
                                case 'result':
                                case 'latency':
                                case 'description':
                                    
                                    if (scoData[cmiIndex][nIndex][splitted[3]] !== undefined) {
                                        apiLastError = '0';

                                        return scoData[cmiIndex][nIndex][splitted[3]];
                                    } else {
                                        // Data Model Element Value Not Initialized
                                        apiLastError = '403';
                                        
                                        return '';
                                    }
                                    break;
                                    
                                case 'objectives':
                                
                                    if (splitted[4] === '_count') {
                                        apiLastError = '0';

                                        return scoData[cmiIndex][nIndex]['objectives'].length;
                                    } else if (splitted[4].trim() && scoData[cmiIndex][nIndex]['objectives'][splitted[4]] !== undefined) {
                                        var objectiveIndex = splitted[4];
                                        
                                        if (splitted[5] === 'id') {
                                            
                                            if (scoData[cmiIndex][nIndex]['objectives'][objectiveIndex]['id'] !== undefined) {
                                                apiLastError = '0';

                                                return scoData[cmiIndex][nIndex]['objectives'][objectiveIndex]['id'];
                                            } else {
                                                // Data Model Element Value Not Initialized
                                                apiLastError = '403';
                                                
                                                return '';
                                            }
                                        } else {
                                            // Undefined Data Model Element
                                            apiLastError = '401';
                                            
                                            return '';
                                        }
                                    } else {
                                        if (splitted[5] === 'id') {
                                            // Data Model Element Value Not Initialized
                                            apiLastError = '403';
                                        } else {
                                            // Undefined Data Model Element
                                            apiLastError = '401';
                                        }
                                        
                                        return '';
                                    }
                                    break;
                                    
                                case 'correct_responses':
                                
                                    if (splitted[4] === '_count') {
                                        apiLastError = '0';

                                        return scoData[cmiIndex][nIndex]['correct_responses'].length;
                                    } else if (splitted[4].trim() && scoData[cmiIndex][nIndex]['correct_responses'][splitted[4]] !== undefined) {
                                        var responseIndex = splitted[4];
                                        
                                        if (splitted[5] === 'pattern') {
                                            
                                            if (scoData[cmiIndex][nIndex]['correct_responses'][responseIndex]['pattern'] !== undefined) {
                                                apiLastError = '0';

                                                return scoData[cmiIndex][nIndex]['correct_responses'][responseIndex]['pattern'];
                                            } else {
                                                // Undefined Data Model Element
                                                apiLastError = '401';

                                                return '';
                                            }
                                        } else {
                                            // Undefined Data Model Element
                                            apiLastError = '401';
                                            
                                            return '';
                                        }
                                    } else {
                                        if (splitted[5] === 'pattern') {
                                            // Data Model Element Value Not Initialized
                                            apiLastError = '403';
                                        } else {
                                            // Undefined Data Model Element
                                            apiLastError = '401';
                                        }
                                        
                                        return '';
                                    }
                                    break;
                                    
                                default:
                                    // Undefined Data Model Element
                                    apiLastError = '401';
                                    
                                    return '';
                            }
                        } else {
                            // Undefined Data Model Element
                            apiLastError = '401';
                            
                            return '';
                        }
                        break;
                        
                    case 'objectives' :
                    
                        if (scoData[cmiIndex][nIndex] !== undefined) {
                            
                            switch (splitted[3]) {
                                case 'id':
                                case 'success_status':
                                case 'completion_status':
                                case 'progress_measure':
                                case 'description':
                                    
                                    if (scoData[cmiIndex][nIndex][splitted[3]] !== undefined) {
                                        apiLastError = '0';
                                    
                                        return scoData[cmiIndex][nIndex][splitted[3]];
                                    } else {
                                        // Data Model Element Value Not Initialized
                                        apiLastError = '403';
                                        
                                        return '';
                                    }
                                    break;
                                    
                                case 'score':
                                
                                    switch (splitted[4]) {
                                        case '_children':
                                        case 'scaled':
                                        case 'raw':
                                        case 'min':
                                        case 'max':
                                            var scoreIndex = 'score.' + splitted[4];
                                            
                                            if (scoData[cmiIndex][nIndex][scoreIndex] !== undefined) {
                                                apiLastError = '0';
                                            
                                                return scoData[cmiIndex][nIndex][scoreIndex];
                                            } else {
                                                // Data Model Element Value Not Initialized
                                                apiLastError = '403';

                                                return '';
                                            }
                                            break;
                                            
                                        default:
                                            // Undefined Data Model Element
                                            apiLastError = '401';

                                            return '';
                                    }
                                    break;
                                    
                                default:
                                    // Undefined Data Model Element
                                    apiLastError = '401';
                                    
                                    return '';
                            }
                        } else {
                            
                            switch (splitted[3]) {
                                case 'id':
                                case 'success_status':
                                case 'completion_status':
                                case 'progress_measure':
                                case 'description':
                                    // Data Model Element Value Not Initialized
                                    apiLastError = '403';
                                    break;
                                    
                                case 'score':
                                
                                    switch (splitted[4]) {
                                        case '_children':
                                        case 'scaled':
                                        case 'raw':
                                        case 'min':
                                        case 'max':
                                            // Data Model Element Value Not Initialized
                                            apiLastError = '403';
                                            break;
                                            
                                        default:
                                            // Undefined Data Model Element
                                            apiLastError = '401';
                                    }
                                    break;
                                    
                                default:
                                    // Undefined Data Model Element
                                    apiLastError = '401';
                            }
                            
                            return '';
                        }
                        break;
                        
                    default:
                        // Undefined Data Model Element
                        apiLastError = '401';

                        return '';
                }
            } else {
                // Undefined Data Model Element
                apiLastError = '401';

                return '';
            }
    }
}

function SetValue(argName, argValue)
{
    'use strict';

    console.log('*** SetValue:: [' + argName + '] = ' + argValue + ' ***');

    if (!apiInitialized) {
        // Store Data Before Initialization
        apiLastError = '132';

        return '';
    } else if (apiTerminated) {
        // Store Data After Termination
        apiLastError = '133';

        return '';
    }
    
    var argStringValue;
    var argFloatValue;
    
    switch (argName) {
        case 'cmi.comments_from_learner':
        case 'cmi.comments_from_lms':
        case 'cmi.interactions':
        case 'cmi.learner_preference':
        case 'cmi.objectives':
        case 'cmi.score':
            // General Get Failure
            apiLastError = '301';
        
            return 'false';
            
        case 'cmi.comments_from_learner._children':
        case 'cmi.comments_from_learner._count':
        case 'cmi.comments_from_lms._children':
        case 'cmi.comments_from_lms._count':
        case 'cmi.completion_threshold':
        case 'cmi.credit':
        case 'cmi.entry':
        case 'cmi.interactions._children':
        case 'cmi.interactions._count':
        case 'cmi.launch_data':
        case 'cmi.learner_id':
        case 'cmi.learner_name':
        case 'cmi.learner_preference._children':
        case 'cmi.max_time_allowed':
        case 'cmi.mode':
        case 'cmi.objectives._children':
        case 'cmi.objectives._count':
        case 'cmi.scaled_passing_score':
        case 'cmi.score._children':
        case 'cmi.time_limit_action':
        case 'cmi.total_time':
            // Data Model Element Is Read Only
            apiLastError = '404';
        
            return 'false';
            
        case 'cmi.completion_status':
            var upperCaseLessonStatus = argValue.toUpperCase();

            if (upperCaseLessonStatus !== 'COMPLETED' &&
                upperCaseLessonStatus !== 'INCOMPLETE' &&
                upperCaseLessonStatus !== 'NOT_ATTEMPTED' &&
                upperCaseLessonStatus !== 'UNKNOWN') {
                // Data Model Element Type Mismatch
                apiLastError = '406';

                return 'false';
            }
            scoData[argName] = argValue;
            apiLastError = '0';

            return 'true';
            
        case 'cmi.exit':
            var upperCaseExit = argValue.toUpperCase();

            if (upperCaseExit !== 'TIME-OUT' &&
                upperCaseExit !== 'SUSPEND' &&
                upperCaseExit !== 'LOGOUT' &&
                upperCaseExit !== 'NORMAL' &&
                upperCaseExit !== '') {
                // Data Model Element Type Mismatch
                apiLastError = '406';

                return 'false';
            }
            scoData[argName] = argValue;
            apiLastError = '0';

            return 'true';
            
        case 'cmi.learner_preference.audio_level':
        case 'cmi.learner_preference.delivery_speed':
            argStringValue = '' + argValue;

            if (regex.test(argStringValue)) {
                argFloatValue = parseFloat(argValue);
                
                if (argFloatValue >= 0) {
                    scoData[argName] = argFloatValue;
                    apiLastError = '0';

                    return 'true';
                } else {
                    // Data Model Element Value Out Of Range
                    apiLastError = '407';

                    return 'false';
                }
            } else {
                // Data Model Element Type Mismatch
                apiLastError = '406';
                
                return 'false';
            }
            break;
            
        case 'cmi.learner_preference.audio_captioning':
            if (argValue !== '-1' &&
                argValue !== '0' &&
                argValue !== '1') {
                // Data Model Element Type Mismatch
                apiLastError = '406';

                return 'false';
            }
            scoData[argName] = argValue;
            apiLastError = '0';

            return 'true';
            
        case 'cmi.progress_measure':
            argStringValue = '' + argValue;

            if (regex.test(argStringValue)) {
                argFloatValue = parseFloat(argValue);
                
                if (argFloatValue >= 0 && argFloatValue <= 1) {
                    scoData[argName] = argFloatValue;
                    apiLastError = '0';

                    return 'true';
                } else {
                    // Data Model Element Value Out Of Range
                    apiLastError = '407';

                    return 'false';
                }
            } else {
                // Data Model Element Type Mismatch
                apiLastError = '406';
                
                return 'false';
            }
            break;
            
        case 'cmi.score.scaled':
            argStringValue = '' + argValue;

            if (regex.test(argStringValue)) {
                argFloatValue = parseFloat(argValue);
                
                if (argFloatValue >= -1 && argFloatValue <= 1) {
                    scoData[argName] = argFloatValue;
                    apiLastError = '0';

                    return 'true';
                } else {
                    // Data Model Element Value Out Of Range
                    apiLastError = '407';

                    return 'false';
                }
            } else {
                // Data Model Element Type Mismatch
                apiLastError = '406';
                
                return 'false';
            }
            break;
            
        case 'cmi.score.raw':
        case 'cmi.score.max':
        case 'cmi.score.min':
            argStringValue = '' + argValue;

            if (regex.test(argStringValue)) {
                scoData[argName] = parseFloat(argValue);
                apiLastError = '0';

                return 'true';
            } else {
                // Data Model Element Type Mismatch
                apiLastError = '406';
                
                return 'false';
            }
            break;
        
        case 'cmi.success_status':
            var upperCaseSuccessStatus = argValue.toUpperCase();

            if (upperCaseSuccessStatus !== 'PASSED' &&
                upperCaseSuccessStatus !== 'FAILED' &&
                upperCaseSuccessStatus !== 'UNKNOWN') {
                // Data Model Element Type Mismatch
                apiLastError = '406';

                return 'false';
            }
            scoData[argName] = argValue;
            apiLastError = '0';

            return 'true';
        
        case 'cmi.learner_preference.language':
        case 'cmi.location':
        case 'cmi.session_time':
        case 'cmi.suspend_data':
            scoData[argName] = argValue;
            apiLastError = '0';
        
            return 'true';
            
        default:
            var splitted = argName.split('.');
            
            if (splitted[0] === 'cmi' && splitted[2].trim()) {
                var cmiIndex = 'cmi.' + splitted[1];
                var nIndex = splitted[2];

                if (!scoData[cmiIndex]) {
                    scoData[cmiIndex] = {};
                }
                if (!scoData[cmiIndex][nIndex]) {
                    scoData[cmiIndex][nIndex] = {};
                }
                
                switch (splitted[1]) {
                    case 'comments_from_learner' :
                    case 'comments_from_lms' :
                            
                        switch (splitted[3]) {
                            case 'comment':
                            case 'location':
                            case 'timestamp':

                                if (scoData[cmiIndex][nIndex] === undefined) {
                                    scoData[cmiIndex][nIndex] = {};
                                }
                                scoData[cmiIndex][nIndex][splitted[3]] = argValue;
                                apiLastError = '0';

                                return 'true';

                            default:
                                // Undefined Data Model Element
                                apiLastError = '401';

                                return 'false';
                        }
                        break;
                        
                    case 'interactions' :
                        
                        switch (splitted[3]) {
                            case 'type':
                                var upperCaseType = argValue.toUpperCase();
                                
                                if (upperCaseType !== 'TRUE-FALSE' &&
                                    upperCaseType !== 'CHOICE' &&
                                    upperCaseType !== 'FILL-IN' &&
                                    upperCaseType !== 'LONG-FILL-IN' &&
                                    upperCaseType !== 'LIKERT' &&
                                    upperCaseType !== 'MATCHING' &&
                                    upperCaseType !== 'PERFORMANCE' &&
                                    upperCaseType !== 'SEQUENCING' &&
                                    upperCaseType !== 'NUMERIC' &&
                                    upperCaseType !== 'OTHER') {
                                
                                    // Data Model Element Type Mismatch
                                    apiLastError = '406';

                                    return 'false';
                                } else {
                                    
                                    if (scoData[cmiIndex][nIndex] === undefined) {
                                        scoData[cmiIndex][nIndex] = {};
                                    }
                                    scoData[cmiIndex][nIndex]['type'] = argValue;
                                    apiLastError = '0';

                                    return 'true';
                                }
                                break;
                                
                            case 'weighting':
                                argStringValue = '' + argValue;

                                if (regex.test(argStringValue)) {

                                    if (scoData[cmiIndex][nIndex] === undefined) {
                                        scoData[cmiIndex][nIndex] = {};
                                    }
                                    scoData[cmiIndex][nIndex]['weighting'] = parseFloat(argValue);
                                    apiLastError = '0';

                                    return 'true';
                                } else {
                                    // Data Model Element Type Mismatch
                                    apiLastError = '406';

                                    return 'false';
                                }
                                break;
                                
                            case 'result':
                                var upperCaseResult = argValue.toUpperCase();
                                
                                if (upperCaseResult === 'CORRECT' ||
                                    upperCaseResult === 'INCORRECT' ||
                                    upperCaseResult === 'UNANTICIPATED' ||
                                    upperCaseResult === 'NEUTRAL') {

                                        if (scoData[cmiIndex][nIndex] === undefined) {
                                            scoData[cmiIndex][nIndex] = {};
                                        }
                                        scoData[cmiIndex][nIndex]['result'] = argValue;
                                        apiLastError = '0';

                                        return 'true';
                                } else if (regex.test('' + argValue)) {

                                    if (scoData[cmiIndex][nIndex] === undefined) {
                                        scoData[cmiIndex][nIndex] = {};
                                    }
                                    scoData[cmiIndex][nIndex]['weighting'] = parseFloat(argValue);
                                    apiLastError = '0';

                                    return 'true';
                                } else {
                                    // Data Model Element Type Mismatch
                                    apiLastError = '406';

                                    return 'false';   
                                }
                                break;
                                
                            case 'id':
                            case 'timestamp':
                            case 'learner_response':
                            case 'latency':
                            case 'description':

                                if (scoData[cmiIndex][nIndex] === undefined) {
                                    scoData[cmiIndex][nIndex] = {};
                                }
                                scoData[cmiIndex][nIndex][splitted[3]] = argValue;
                                apiLastError = '0';

                                return 'true';
                                
                            case 'objectives':
                                
                                if (splitted[4] === '_count') {
                                    // Data Model Element Is Read Only
                                    apiLastError = '404';

                                    return 'false';
                                } else {
                                    
                                    if (splitted[4].trim()) {
                                        var objectiveIndex = splitted[4];
                                        
                                        if (splitted[5] === 'id') {
                                            
                                            if (scoData[cmiIndex][nIndex]['objectives'] === undefined) {
                                                scoData[cmiIndex][nIndex]['objectives'] = {};
                                            }
                                            
                                            if (scoData[cmiIndex][nIndex]['objectives'][objectiveIndex] === undefined) {
                                                scoData[cmiIndex][nIndex]['objectives'][objectiveIndex] = {};
                                            }
                                            scoData[cmiIndex][nIndex]['objectives'][objectiveIndex]['id'] = argValue;
                                            apiLastError = '0';

                                            return 'true';
                                        } else {
                                            // Undefined Data Model Element
                                            apiLastError = '401';

                                            return 'false';
                                        }
                                        
                                    } else {
                                        // Undefined Data Model Element
                                        apiLastError = '401';

                                        return 'false';
                                    }
                                }
                                break;
                                
                            case 'correct_responses':
                                
                                if (splitted[4] === '_count') {
                                    // Data Model Element Is Read Only
                                    apiLastError = '404';

                                    return 'false';
                                } else {
                                    
                                    if (splitted[4].trim()) {
                                        var responseIndex = splitted[4];
                                        
                                        if (splitted[5] === 'pattern') {
                                            
                                            if (scoData[cmiIndex][nIndex]['correct_responses'] === undefined) {
                                                scoData[cmiIndex][nIndex]['correct_responses'] = {};
                                            }
                                            
                                            if (scoData[cmiIndex][nIndex]['correct_responses'][responseIndex] === undefined) {
                                                scoData[cmiIndex][nIndex]['correct_responses'][responseIndex] = {};
                                            }
                                            scoData[cmiIndex][nIndex]['correct_responses'][responseIndex]['pattern'] = argValue;
                                            apiLastError = '0';

                                            return 'true';
                                        } else {
                                            // Undefined Data Model Element
                                            apiLastError = '401';

                                            return 'false';
                                        }
                                        
                                    } else {
                                        // Undefined Data Model Element
                                        apiLastError = '401';

                                        return 'false';
                                    }
                                }
                                break;
                                
                            default:
                                // Undefined Data Model Element
                                apiLastError = '401';

                                return 'false';
                        }
                        break;
                        
                    case 'objectives' : 
                        
                        switch (splitted[3]) {
                            case 'success_status':
                                var upperCaseObjSuccessStatus = argValue.toUpperCase();
                                
                                if (upperCaseObjSuccessStatus !== 'PASSED' &&
                                    upperCaseObjSuccessStatus !== 'FAILED' &&
                                    upperCaseObjSuccessStatus !== 'UNKNOWN') {
                                
                                    // Data Model Element Type Mismatch
                                    apiLastError = '406';

                                    return 'false';
                                } else {
                                    scoData[cmiIndex][nIndex]['success_status'] = argValue;
                                    apiLastError = '0';

                                    return 'true';
                                }
                                break;
                                
                            case 'completion_status':
                                var upperCaseObjCompletionStatus = argValue.toUpperCase();
                                
                                if (upperCaseObjCompletionStatus !== 'COMPLETED' &&
                                    upperCaseObjCompletionStatus !== 'INCOMPLETE' &&
                                    upperCaseObjCompletionStatus !== 'NOT_ATTEMPTED' &&
                                    upperCaseObjCompletionStatus !== 'UNKNOWN') {
                                
                                    // Data Model Element Type Mismatch
                                    apiLastError = '406';

                                    return 'false';
                                } else {
                                    scoData[cmiIndex][nIndex]['completion_status'] = argValue;
                                    apiLastError = '0';

                                    return 'true';
                                }
                                break;
                                
                            case 'progress_measure':
                                argStringValue = '' + argValue;

                                if (regex.test(argStringValue)) {
                                    argFloatValue = parseFloat(argValue);

                                    if (argFloatValue >= 0 && argFloatValue <= 1) {
                                        scoData[cmiIndex][nIndex]['progress_measure'] = argFloatValue;
                                        apiLastError = '0';

                                        return 'true';
                                    } else {
                                        // Data Model Element Value Out Of Range
                                        apiLastError = '407';

                                        return 'false';
                                    }
                                } else {
                                    // Data Model Element Type Mismatch
                                    apiLastError = '406';

                                    return 'false';
                                }
                                break;
                            
                            case 'id':
                            case 'description':
                                scoData[cmiIndex][nIndex][splitted[3]] = argValue;
                                apiLastError = '0';

                                return 'true';
                            
                            case 'score':
                                
                                switch (splitted[4]) {
                                    case '_children':
                                        // Data Model Element Is Read Only
                                        apiLastError = '404';

                                        return 'false';
                                        
                                    case 'scaled':
                                
                                        if (scoData[cmiIndex][nIndex]['score._children'] === undefined) {
                                            scoData[cmiIndex][nIndex]['score._children'] = 'scaled,raw,min,max';
                                        }
                                        argStringValue = '' + argValue;

                                        if (regex.test(argStringValue)) {
                                            argFloatValue = parseFloat(argValue);

                                            if (argFloatValue >= -1 && argFloatValue <= 1) {
                                                scoData[cmiIndex][nIndex]['score.scaled'] = argFloatValue;
                                                apiLastError = '0';

                                                return 'true';
                                            } else {
                                                // Data Model Element Value Out Of Range
                                                apiLastError = '407';

                                                return 'false';
                                            }
                                        } else {
                                            // Data Model Element Type Mismatch
                                            apiLastError = '406';

                                            return 'false';
                                        }
                                        break;
                                        
                                    case 'raw':
                                    case 'min':
                                    case 'max':
                                
                                        if (scoData[cmiIndex][nIndex]['score._children'] === undefined) {
                                            scoData[cmiIndex][nIndex]['score._children'] = 'scaled,raw,min,max';
                                        }
                                        argStringValue = '' + argValue;

                                        if (regex.test(argStringValue)) {
                                            argFloatValue = parseFloat(argValue);
                                            scoData[cmiIndex][nIndex]['score.' + splitted[4]] = argFloatValue;
                                            apiLastError = '0';

                                            return 'true';
                                        } else {
                                            // Data Model Element Type Mismatch
                                            apiLastError = '406';

                                            return 'false';
                                        }
                                        break;
                                        
                                    default:
                                        // Undefined Data Model Element
                                        apiLastError = '401';

                                        return 'false';
                                }
                                break;
                            
                            default:
                                // Undefined Data Model Element
                                apiLastError = '401';

                                return 'false';
                        }
                        break;
                        
                    default:
                        // Undefined Data Model Element
                        apiLastError = '401';

                        return '';
                }
            } else {
                // Undefined Data Model Element
                apiLastError = '401';

                return '';
            }
    }
}

function GetLastError()
{
    'use strict';

    console.log('*** GetLastError:: ' + apiLastError + ' ***');

    return apiLastError;
}

function GetErrorString(errorCode)
{
    'use strict';

    console.log('*** GetErrorString:: [' + errorCode + '] ***');
    
    switch (errorCode) {
        case '0':
        case '101':
        case '102':
        case '103':
        case '104':
        case '111':
        case '112':
        case '113':
        case '122':
        case '123':
        case '132':
        case '133':
        case '142':
        case '143':
        case '201':
        case '301':
        case '351':
        case '391':
        case '401':
        case '402':
        case '403':
        case '404':
        case '405':
        case '406':
        case '407':
        case '408':

            return errorString[errorCode];
        default :

            return '';
    }
}

function GetDiagnostic(errorCode)
{
    'use strict';

    var index = errorCode;

    if (index === '') {
        index = apiLastError;
    }
    console.log('*** GetDiagnostic:: [' + index + '] ***');

    switch (index) {
        case '0' :
        case '101' :
        case '102' :
        case '103' :
        case '104' :
        case '111' :
        case '112' :
        case '113' :
        case '122' :
        case '123' :
        case '132' :
        case '133' :
        case '142' :
        case '143' :
        case '201' :
        case '301' :
        case '351' :
        case '391' :
        case '401' :
        case '402' :
        case '403' :
        case '404' :
        case '405' :
        case '406' :
        case '407' :
        case '408' :

            return errorString[index];
        default :

            return '';
    }
}

function Commit(arg)
{
    'use strict';

    console.log('*** Commit ***');
    
    if (arg !== '') {
        // General Argument Error
        apiLastError = '201';

        return 'false';
    } else if (!apiInitialized) {
        // Commit Before Initialization
        apiLastError = '142';

        return 'false';
    } else if (apiTerminated) {
        // Commit After Termination
        apiLastError = '143';

        return 'false';
    }
    apiLastError = '0';
    commitResult('persist');

    return 'true';
}
function APIClass()
{
    'use strict';
    //SCORM 2004
    this.Initialize = Initialize;
    this.Terminate = Terminate;
    this.GetValue = GetValue;
    this.SetValue = SetValue;
    this.Commit = Commit;
    this.GetLastError = GetLastError;
    this.GetErrorString = GetErrorString;
    this.GetDiagnostic = GetDiagnostic;
}

var API_1484_11 = new APIClass();
var api_1484_11 = new APIClass();