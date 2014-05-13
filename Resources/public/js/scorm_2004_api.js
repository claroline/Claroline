var errorString = [];
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

function Initialize(arg)
{
    'use strict';

    console.log('*** Initialize ***');
    
    return 'true';
}

function Terminate(arg)
{
    'use strict';

    console.log('*** Terminate ***');

    return true;
}

function GetValue(arg)
{
    'use strict';

    console.log('*** GetValue:: ' + arg + ' ***');

    return '';
}

function SetValue(argName, argValue)
{
    'use strict';

    console.log('*** SetValue:: [' + argName + '] = ' + argValue + ' ***');

    return 'true';
}

function GetLastError()
{
    'use strict';

    console.log('*** GetLastError:: ');

    return '';
}

function GetErrorString(errorCode)
{
    'use strict';

    console.log('*** GetErrorString:: [' + errorCode + '] ***');

    return 'Unknown Error';
}

function GetDiagnostic(errorCode)
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

function Commit(arg)
{
    'use strict';

    console.log('*** LMSCommit ***');

    if (apiInitialized) {
        if (arg !== '') {
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