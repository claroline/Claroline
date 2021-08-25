/* eslint-disable no-console */

import {actions} from '#/plugin/scorm/resources/scorm/player/actions'

const scorm12Errors = {
  '0': 'No error',
  '101': 'General Exception',
  '201': 'Invalid Argument Error',
  '202': 'Element cannot have children',
  '203': 'Element not an array.  Cannot have count',
  '301': 'Not initialized',
  '401': 'Not implemented error',
  '402': 'Invalid set value, element is a keyword',
  '403': 'Element is read only',
  '404': 'Element is write only',
  '405': 'Incorrect Data Type'
}

const scorm2004Errors = {
  '0': 'No error',
  '101': 'General Exception',
  '102': 'General Initialization Failure',
  '103': 'Already Initialized',
  '104': 'Content Instance Terminated',
  '111': 'General Termination Failure',
  '112': 'Termination Before Initialization',
  '113': 'Termination After Termination',
  '122': 'Retrieve Data Before Initialization',
  '123': 'Retrieve Data After Termination',
  '132': 'Store Data Before Initialization',
  '133': 'Store Data After Termination',
  '142': 'Commit Before Initialization',
  '143': 'Commit After Termination',
  '201': 'General Argument Error',
  '301': 'General Get Failure',
  '351': 'General Set Failure',
  '391': 'General Commit Failure',
  '401': 'Undefined Data Model Element',
  '402': 'Unimplemented Data Model Element',
  '403': 'Data Model Element Value Not Initialized',
  '404': 'Data Model Element Is Read Only',
  '405': 'Data Model Element Is Write Only',
  '406': 'Data Model Element Type Mismatch',
  '407': 'Data Model Element Value Out Of Range',
  '408': 'Data Model Dependency Not Established'
}

function commitResult(scoId, mode, scoData, dispatch, currentUser) {
  if (currentUser) {
    dispatch(actions.commitData(scoId, mode, scoData))
  }
}

function APIClass(sco, scormData, tracking, dispatch, currentUser) {
  this.apiInitialized = false
  this.apiLastError = 'scorm_12' === scormData.version ? '301' : '0'
  this.scoData = Array.isArray(tracking['details']) ? {} : Object.assign({}, tracking['details'])

  if ('scorm_12' === scormData.version) {
    this.scoData['cmi.core.student_id'] = currentUser ? currentUser.autoId : -1
    this.scoData['cmi.core.student_name'] = currentUser ? `${currentUser.firstName}, ${currentUser.lastName}` : 'anon., anon.'
    this.scoData['cmi.core.lesson_mode'] = tracking['lessonMode']
    this.scoData['cmi.core.lesson_location'] = tracking['lessonLocation']
    this.scoData['cmi.core.credit'] = tracking['credit']

    let totalTime = tracking['totalTimeInt']
    const totalTimeHours = totalTime / 144000
    totalTime %= 144000
    const totalTimeMinutes = totalTime / 6000
    totalTime %= 6000
    const totalTimeSeconds = totalTime / 100
    totalTime %= 100
    this.scoData['cmi.core.total_time'] = `${totalTimeHours}:${totalTimeMinutes}:${totalTimeSeconds}.${totalTime}`
    this.scoData['cmi.core.entry'] = tracking['entry']
    this.scoData['cmi.suspend_data'] = tracking['suspendData']
    this.scoData['cmi.launch_data'] = null !== sco.data.launchData ? sco.data.launchData : ''
    this.scoData['cmi.core._children'] = 'student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,lesson_mode,exit,session_time'
    this.scoData['cmi.core.score._children'] = 'raw,min,max'
    this.scoData['cmi.core.session_time'] = '0000:00:00.00'
    this.scoData['cmi.core.exit'] = ''
    this.scoData['cmi.student_data._children'] = 'mastery_score,time_limit_action,max_time_allowed'
    this.scoData['cmi.student_data.mastery_score'] = null !== sco.data.scoreToPassInt ? sco.data.scoreToPassInt : ''
    this.scoData['cmi.student_data.max_time_allowed'] = null !== sco.data.maxTimeAllowed ? sco.data.maxTimeAllowed : ''
    this.scoData['cmi.student_data.time_limit_action'] = null !== sco.data.timeLimitAction ? sco.data.timeLimitAction : ''
    this.scoData['cmi.progress_measure'] = tracking['progression'] ? tracking['progression'] / 100 : 0
  } else {
    this.scoData['cmi.learner_id'] = currentUser ? currentUser.autoId : -1
    this.scoData['cmi.learner_name'] = currentUser ? `${currentUser.firstName}, ${currentUser.lastName}` : 'anon., anon.'
    this.scoData['cmi.time_limit_action'] = null !== sco.data.timeLimitAction ? sco.data.timeLimitAction : 'continue,no message'
    this.scoData['cmi.total_time'] = null !== tracking['totalTimeString'] ? tracking['totalTimeString'] : 'PT0S'
    this.scoData['cmi.launch_data'] = null !== sco.data.launchData ? sco.data.launchData : ''
    this.scoData['cmi.completion_threshold'] = null !== sco.data.completionThreshold ? sco.data.completionThreshold : ''
    this.scoData['cmi.max_time_allowed'] = null !== sco.data.maxTimeAllowed ? sco.data.maxTimeAllowed : ''
    this.scoData['cmi.scaled_passing_score'] = null !== sco.data.scoreToPassDecimal ? sco.data.scoreToPassDecimal : ''
    this.scoData['cmi.credit'] = 'credit'
    this.scoData['cmi.interactions._children'] = 'id,type,objectives,timestamp,correct_responses,weighting,learner_response,result,latency,description'
    this.scoData['cmi.learner_preference._children'] = 'audio_level,language,delivery_speed,audio_captioning'
    this.scoData['cmi.mode'] = 'normal'
    this.scoData['cmi.objectives._children'] = 'id,score,success_status,completion_status,progress_measure,description'
    this.scoData['cmi.score._children'] = 'scaled,raw,min,max'
    this.scoData['cmi.progress_measure'] = tracking['progression'] ? tracking['progression'] / 100 : 0

    if (undefined === this.scoData['cmi.comments_from_learner']) {
      this.scoData['cmi.comments_from_learner'] = {}
      this.scoData['cmi.comments_from_learner._children'] = 'comment,location,timestamp'
    }
    if (undefined === this.scoData['cmi.comments_from_lms']) {
      this.scoData['cmi.comments_from_lms'] = {}
      this.scoData['cmi.comments_from_lms._children'] = 'comment,location,timestamp'
    }
    if (undefined === this.scoData['cmi.completion_status']) {
      this.scoData['cmi.completion_status'] = 'unknown'
    }
    if (undefined === this.scoData['cmi.entry']) {
      this.scoData['cmi.entry'] = ''
    }
    if (undefined === this.scoData['cmi.exit']) {
      this.scoData['cmi.exit'] = ''
    }
    if (undefined === this.scoData['cmi.interactions']) {
      this.scoData['cmi.interactions'] = {}
    }
    if (undefined === this.scoData['cmi.location']) {
      this.scoData['cmi.location'] = ''
    }
    if (undefined === this.scoData['cmi.objectives']) {
      this.scoData['cmi.objectives'] = {}
    }
    if (undefined === this.scoData['cmi.success_status']) {
      this.scoData['cmi.success_status'] = 'unknown'
    }
    if (undefined === this.scoData['cmi.time_limit_action']) {
      this.scoData['cmi.time_limit_action'] = 'continue,no message'
    }
    if (undefined === this.scoData['cmi.total_time']) {
      this.scoData['cmi.total_time'] = 'PT0S'
    }
  }

  // Scorm 2004 only
  this.apiTerminated = false
  this.pattern = '^[-]?[0-9]+([.][0-9]{1,7})?$'
  this.regex = new RegExp(this.pattern)

  /*****************
   *   SCORM 1.2   *
   *****************/
  this.LMSInitialize = (arg) => {
    console.log('LMSInitialize', arg)
    if (arg != '') {
      this.apiLastError = '201'

      return 'false'
    }
    this.apiLastError = '0'
    this.apiInitialized = true

    return 'true'
  }

  this.LMSFinish = (arg) => {
    console.log('LMSFinish', arg)
    if (this.apiInitialized) {

      if ('' !== arg) {
        this.apiLastError = '201'

        return 'false'
      }
      this.apiLastError = '0'
      this.apiInitialized = false
      // Set value for 'cmi.core.entry' depending on 'cmi.core.exit'
      if ('SUSPEND' === this.scoData['cmi.core.exit'].toUpperCase()) {
        this.scoData['cmi.core.entry'] = 'resume'
      } else {
        this.scoData['cmi.core.entry'] = ''
      }
      commitResult(sco.id, 'log', this.scoData, dispatch, currentUser)

      return 'true'
    } else {
      this.apiLastError = '301'   // not initialized

      return 'false'
    }
  }

  this.LMSGetValue = (arg) => {
    console.log('LMSGetValue', arg)
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
        case 'cmi.student_data._children' :
        case 'cmi.student_data.mastery_score' :
        case 'cmi.student_data.max_time_allowed' :
        case 'cmi.student_data.time_limit_action' :
        case 'cmi.progress_measure':
          this.apiLastError = '0'

          return this.scoData[arg] ? this.scoData[arg] : ''
        case 'cmi.core.exit' :
        case 'cmi.core.session_time' :
          this.apiLastError = '404' // write only

          return ''
        default :
          this.apiLastError = '401'

          return ''
      }
    } else {
      // not initialized error
      this.apiLastError = '301'

      return ''
    }
  }

  this.LMSSetValue = (argName, argValue) => {
    console.log('LMSSetValue', argName, argValue)
    if (this.apiInitialized) {
      let upperCaseLessonStatus = ''
      let upperCaseExit = ''
      // regex to check format
      // hhhh:mm:ss.ss
      const re = /^[0-9]{2,4}:[0-9]{2}:[0-9]{2}(.[0-9]{1,2})?$/
      let timeArray = []

      switch (argName) {
        case 'cmi.core._children' :
        case 'cmi.core.score._children' :
        case 'cmi.student_data._children' :
          this.apiLastError = '402' // invalid set value, element is a keyword

          return 'false'
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
          this.apiLastError = '403' // read only

          return 'false'
        case 'cmi.core.lesson_location' :

          if (argValue.length > 255) {
            this.apiLastError = '405'

            return 'false'
          }
          this.scoData[argName] = argValue
          this.apiLastError = '0'

          return 'true'
        case 'cmi.core.lesson_status' :
          upperCaseLessonStatus = argValue.toUpperCase()

          if ('PASSED' !== upperCaseLessonStatus &&
            'FAILED' !== upperCaseLessonStatus &&
            'COMPLETED' !== upperCaseLessonStatus &&
            'INCOMPLETE' !== upperCaseLessonStatus &&
            'BROWSED' !== upperCaseLessonStatus &&
            'NOT ATTEMPTED' !== upperCaseLessonStatus
          ) {
            this.apiLastError = '405'

            return 'false'
          }
          this.scoData[argName] = argValue
          this.apiLastError = '0'

          return 'true'
        case 'cmi.core.score.raw' :
        case 'cmi.core.score.min' :
        case 'cmi.core.score.max' :
          if (isNaN(parseInt(argValue)) || (0 > argValue) || (100 < argValue)) {
            this.apiLastError = '405'

            return 'false'
          }
          this.scoData[argName] = argValue
          this.apiLastError = '0'

          return 'true'
        case 'cmi.progress_measure' :
          this.scoData[argName] = argValue

          return 'true'
        case 'cmi.core.exit' :
          upperCaseExit = argValue.toUpperCase()

          if ('TIME-OUT' !== upperCaseExit &&
            'SUSPEND' !== upperCaseExit &&
            'LOGOUT' !== upperCaseExit &&
            '' !== upperCaseExit
          ) {
            this.apiLastError = '405'

            return 'false'
          }
          this.scoData[argName] = argValue
          this.apiLastError = '0'

          return 'true'
        case 'cmi.core.session_time' :
          if (!re.test(argValue)) {
            this.apiLastError = '405'

            return 'false'
          }
          // check that minute and second are 0 <= x < 60
          timeArray = argValue.split(':')

          if (timeArray[1] < 0 || timeArray[1] >= 60 || timeArray[2] < 0 || timeArray[2] >= 60) {
            this.apiLastError = '405'

            return 'false'
          }

          this.scoData[argName] = argValue
          this.apiLastError = '0'

          return 'true'
        case 'cmi.suspend_data' :
          if (argValue.length > 4096) {
            this.apiLastError = '405'

            return 'false'
          }
          this.scoData[argName] = argValue
          this.apiLastError = '0'

          return 'true'
        default :
          this.apiLastError = '401'

          return 'false'
      }
    } else {
      // not initialized error
      this.apiLastError = '301'

      return 'false'
    }
  }

  this.LMSCommit = (arg) => {
    console.log('LMSCommit', arg)
    if (this.apiInitialized) {
      if ('' !== arg) {
        this.apiLastError = '201'

        return 'false'
      } else {
        this.apiLastError = '0'
        commitResult(sco.id, 'persist', this.scoData, dispatch, currentUser)

        return 'true'
      }
    } else {
      this.apiLastError = '301'

      return 'false'
    }
  }

  this.LMSGetLastError = () => {
    console.log('LMSGetLastError')

    return this.apiLastError
  }

  this.LMSGetErrorString = (errorCode) => {
    console.log('LMSGetErrorString', errorCode)

    let error = 'Unknown Error'

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
        error = scorm12Errors[errorCode]
        break
    }

    return error
  }

  this.LMSGetDiagnostic = (errorCode) => {
    console.log('LMSGetDiagnostic', errorCode)

    let error = 'Unknown Error'
    let index = errorCode

    if ('' === index) {
      index = this.apiLastError
    }
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
        error = scorm12Errors[errorCode]
        break
    }

    return error
  }

  /******************
   *   SCORM 2004   *
   ******************/
  this.Initialize = (arg) => {
    console.log('Initialize', arg)

    if ('' !== arg) {
      // General Argument Error
      this.apiLastError = '201'

      return 'false'
    } else if (this.apiInitialized) {
      // Already Initialized
      this.apiLastError = '103'

      return 'false'
    } else if (this.apiTerminated) {
      // Content Instance Terminated
      this.apiLastError = '104'

      return 'false'
    }
    this.apiLastError = '0'
    this.apiInitialized = true

    return 'true'
  }

  this.Terminate = (arg) => {
    console.log('Terminate', arg)

    if ('' !== arg) {
      // General Argument Error
      this.apiLastError = '201'

      return 'false'
    } else if (!this.apiInitialized) {
      // Termination Before Initialization
      this.apiLastError = '112'

      return 'false'
    } else if (this.apiTerminated) {
      // Termination After Termination
      this.apiLastError = '113'

      return 'false'
    }
    this.apiLastError = '0'
    this.apiTerminated = true
    this.apiInitialized = false
    // Set value for 'cmi.entry' depending on 'cmi.exit'
    if ('SUSPEND' === this.scoData['cmi.exit'].toUpperCase() || 'LOGOUT' === this.scoData['cmi.exit'].toUpperCase()) {
      this.scoData['cmi.entry'] = 'resume'
    } else {
      this.scoData['cmi.entry'] = ''
    }
    commitResult(sco.id, 'log', this.scoData, dispatch, currentUser)

    return 'true'
  }

  this.GetValue = (arg) => {
    console.log('GetValue', arg)

    if (!this.apiInitialized) {
      // Retrieve Data Before Initialization
      this.apiLastError = '122'

      return ''
    } else if (this.apiTerminated) {
      // Retrieve Data After Termination
      this.apiLastError = '123'

      return ''
    }

    let splitted = []

    switch (arg) {
      case 'cmi.comments_from_learner':
      case 'cmi.comments_from_lms':
      case 'cmi.interactions':
      case 'cmi.learner_preference':
      case 'cmi.objectives':
      case 'cmi.score':
        // General Get Failure
        this.apiLastError = '301'

        return ''

      case 'cmi.exit':
      case 'cmi.session_time':
        // Data Model Element Is Write Only
        this.apiLastError = '405'

        return ''

      case 'cmi.comments_from_learner._count':
        this.apiLastError = '0'

        return this.scoData['cmi.comments_from_learner'].length

      case 'cmi.comments_from_lms._count':
        this.apiLastError = '0'

        return this.scoData['cmi.comments_from_lms'].length

      case 'cmi.interactions._count':
        this.apiLastError = '0'

        return this.scoData['cmi.interactions'].length

      case 'cmi.objectives._count':
        this.apiLastError = '0'

        return this.scoData['cmi.objectives'].length

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
      case 'cmi_progress_measure':
        this.apiLastError = '0'

        return this.scoData[arg]

      case 'cmi.learner_preference.audio_level':
      case 'cmi.learner_preference.language':
      case 'cmi.learner_preference.delivery_speed':
      case 'cmi.learner_preference.audio_captioning':
      case 'cmi.score.scaled':
      case 'cmi.score.raw':
      case 'cmi.score.max':
      case 'cmi.score.min':
      case 'cmi.success_status':
      case 'cmi.suspend_data':
        if (undefined === this.scoData[arg]) {
          // Data Model Element Value Not Initialized
          this.apiLastError = '403'

          return ''
        }
        this.apiLastError = '0'

        return this.scoData[arg]

      case 'cmi.progress_measure':
        return this.scoData[arg] ? this.scoData[arg] / 100 : this.scoData[arg]

      default:
        splitted = arg.split('.')

        if ('cmi' === splitted[0] && splitted[2].trim()) {
          const cmiIndex = 'cmi.' + splitted[1]
          const nIndex = splitted[2]

          switch (splitted[1]) {
            case 'comments_from_learner' :
            case 'comments_from_lms' :
              if (undefined !== this.scoData[cmiIndex][nIndex]) {
                switch (splitted[3]) {
                  case 'comment':
                  case 'location':
                  case 'timestamp':
                    if (undefined !== this.scoData[cmiIndex][nIndex][splitted[3]]) {
                      this.apiLastError = '0'

                      return this.scoData[cmiIndex][nIndex][splitted[3]]
                    } else {
                      // Data Model Element Value Not Initialized
                      this.apiLastError = '403'

                      return ''
                    }

                  default:
                    // Undefined Data Model Element
                    this.apiLastError = '401'

                    return ''
                }
              } else {
                switch (splitted[3]) {
                  case 'comment':
                  case 'location':
                  case 'timestamp':
                    // Data Model Element Value Not Initialized
                    this.apiLastError = '403'
                    break

                  default:
                    // Undefined Data Model Element
                    this.apiLastError = '401'
                }

                return ''
              }

            case 'interactions' :
              if (undefined !== this.scoData[cmiIndex][nIndex]) {
                switch (splitted[3]) {
                  case 'id':
                  case 'type':
                  case 'timestamp':
                  case 'weighting':
                  case 'learner_response':
                  case 'result':
                  case 'latency':
                  case 'description':
                    if (undefined !== this.scoData[cmiIndex][nIndex][splitted[3]]) {
                      this.apiLastError = '0'

                      return this.scoData[cmiIndex][nIndex][splitted[3]]
                    } else {
                      // Data Model Element Value Not Initialized
                      this.apiLastError = '403'

                      return ''
                    }

                  case 'objectives':
                    if ('_count' === splitted[4]) {
                      this.apiLastError = '0'

                      return this.scoData[cmiIndex][nIndex]['objectives'].length
                    } else if (splitted[4].trim() && undefined !== this.scoData[cmiIndex][nIndex]['objectives'][splitted[4]]) {
                      const objectiveIndex = splitted[4]

                      if ('id' === splitted[5]) {
                        if (undefined !== this.scoData[cmiIndex][nIndex]['objectives'][objectiveIndex]['id']) {
                          this.apiLastError = '0'

                          return this.scoData[cmiIndex][nIndex]['objectives'][objectiveIndex]['id']
                        } else {
                          // Data Model Element Value Not Initialized
                          this.apiLastError = '403'

                          return ''
                        }
                      } else {
                        // Undefined Data Model Element
                        this.apiLastError = '401'

                        return ''
                      }
                    } else {
                      if ('id' === splitted[5]) {
                        // Data Model Element Value Not Initialized
                        this.apiLastError = '403'
                      } else {
                        // Undefined Data Model Element
                        this.apiLastError = '401'
                      }

                      return ''
                    }

                  case 'correct_responses':
                    if ('_count' === splitted[4]) {
                      this.apiLastError = '0'

                      return this.scoData[cmiIndex][nIndex]['correct_responses'].length
                    } else if (splitted[4].trim() && undefined !== this.scoData[cmiIndex][nIndex]['correct_responses'][splitted[4]]) {
                      const responseIndex = splitted[4]

                      if ('pattern' === splitted[5]) {
                        if (undefined !== this.scoData[cmiIndex][nIndex]['correct_responses'][responseIndex]['pattern']) {
                          this.apiLastError = '0'

                          return this.scoData[cmiIndex][nIndex]['correct_responses'][responseIndex]['pattern']
                        } else {
                          // Undefined Data Model Element
                          this.apiLastError = '401'

                          return ''
                        }
                      } else {
                        // Undefined Data Model Element
                        this.apiLastError = '401'

                        return ''
                      }
                    } else {
                      if ('pattern' === splitted[5]) {
                        // Data Model Element Value Not Initialized
                        this.apiLastError = '403'
                      } else {
                        // Undefined Data Model Element
                        this.apiLastError = '401'
                      }

                      return ''
                    }

                  default:
                    // Undefined Data Model Element
                    this.apiLastError = '401'

                    return ''
                }
              } else {
                // Undefined Data Model Element
                this.apiLastError = '401'

                return ''
              }

            case 'objectives' :
              if (undefined !== this.scoData[cmiIndex][nIndex]) {
                let scoreIndex = ''

                switch (splitted[3]) {
                  case 'id':
                  case 'success_status':
                  case 'completion_status':
                  case 'progress_measure':
                  case 'description':
                    if (undefined !== this.scoData[cmiIndex][nIndex][splitted[3]]) {
                      this.apiLastError = '0'

                      return this.scoData[cmiIndex][nIndex][splitted[3]]
                    } else {
                      // Data Model Element Value Not Initialized
                      this.apiLastError = '403'

                      return ''
                    }

                  case 'score':
                    switch (splitted[4]) {
                      case '_children':
                      case 'scaled':
                      case 'raw':
                      case 'min':
                      case 'max':
                        scoreIndex = 'score.' + splitted[4]

                        if (undefined !== this.scoData[cmiIndex][nIndex][scoreIndex]) {
                          this.apiLastError = '0'

                          return this.scoData[cmiIndex][nIndex][scoreIndex]
                        } else {
                          // Data Model Element Value Not Initialized
                          this.apiLastError = '403'

                          return ''
                        }

                      default:
                        // Undefined Data Model Element
                        this.apiLastError = '401'

                        return ''
                    }

                  default:
                    // Undefined Data Model Element
                    this.apiLastError = '401'

                    return ''
                }
              } else {
                switch (splitted[3]) {
                  case 'id':
                  case 'success_status':
                  case 'completion_status':
                  case 'progress_measure':
                  case 'description':
                    // Data Model Element Value Not Initialized
                    this.apiLastError = '403'
                    break

                  case 'score':
                    switch (splitted[4]) {
                      case '_children':
                      case 'scaled':
                      case 'raw':
                      case 'min':
                      case 'max':
                        // Data Model Element Value Not Initialized
                        this.apiLastError = '403'
                        break

                      default:
                        // Undefined Data Model Element
                        this.apiLastError = '401'
                    }
                    break

                  default:
                    // Undefined Data Model Element
                    this.apiLastError = '401'
                }

                return ''
              }

            default:
              // Undefined Data Model Element
              this.apiLastError = '401'

              return ''
          }
        } else {
          // Undefined Data Model Element
          this.apiLastError = '401'

          return ''
        }
    }
  }

  this.SetValue = (argName, argValue) => {
    console.log('SetValue', argName, argValue)

    if (!this.apiInitialized) {
      // Store Data Before Initialization
      this.apiLastError = '132'

      return ''
    } else if (this.apiTerminated) {
      // Store Data After Termination
      this.apiLastError = '133'

      return ''
    }

    let argStringValue
    let argFloatValue
    let upperCaseLessonStatus = ''
    let upperCaseExit = ''
    let upperCaseSuccessStatus = ''
    let splitted = []

    switch (argName) {
      case 'cmi.comments_from_learner':
      case 'cmi.comments_from_lms':
      case 'cmi.interactions':
      case 'cmi.learner_preference':
      case 'cmi.objectives':
      case 'cmi.score':
        // General Get Failure
        this.apiLastError = '301'

        return 'false'

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
        this.apiLastError = '404'

        return 'false'

      case 'cmi.completion_status':
        upperCaseLessonStatus = argValue.toUpperCase()

        if ('COMPLETED' !== upperCaseLessonStatus &&
          'INCOMPLETE' !== upperCaseLessonStatus &&
          'NOT_ATTEMPTED' !== upperCaseLessonStatus &&
          'UNKNOWN' !== upperCaseLessonStatus
        ) {
          // Data Model Element Type Mismatch
          this.apiLastError = '406'

          return 'false'
        }
        this.scoData[argName] = argValue
        this.apiLastError = '0'

        return 'true'

      case 'cmi.exit':
        upperCaseExit = argValue.toUpperCase()

        if ('TIME-OUT' !== upperCaseExit &&
          'SUSPEND' !== upperCaseExit &&
          'LOGOUT' !== upperCaseExit &&
          'NORMAL' !== upperCaseExit &&
          '' !== upperCaseExit
        ) {
          // Data Model Element Type Mismatch
          this.apiLastError = '406'

          return 'false'
        }
        this.scoData[argName] = argValue
        this.apiLastError = '0'

        return 'true'

      case 'cmi.learner_preference.audio_level':
      case 'cmi.learner_preference.delivery_speed':
        argStringValue = '' + argValue

        if (this.regex.test(argStringValue)) {
          argFloatValue = parseFloat(argValue)

          if (0 <= argFloatValue) {
            this.scoData[argName] = argFloatValue
            this.apiLastError = '0'

            return 'true'
          } else {
            // Data Model Element Value Out Of Range
            this.apiLastError = '407'

            return 'false'
          }
        } else {
          // Data Model Element Type Mismatch
          this.apiLastError = '406'

          return 'false'
        }

      case 'cmi.learner_preference.audio_captioning':
        if ('-1' !== argValue && '0' !== argValue && '1' !== argValue) {
          // Data Model Element Type Mismatch
          this.apiLastError = '406'

          return 'false'
        }
        this.scoData[argName] = argValue
        this.apiLastError = '0'

        return 'true'

      case 'cmi.progress_measure':
        argStringValue = '' + argValue

        if (this.regex.test(argStringValue)) {
          argFloatValue = parseFloat(argValue)

          if (0 <= argFloatValue && 1 >= argFloatValue) {
            this.scoData[argName] = argFloatValue * 100
            this.apiLastError = '0'

            return 'true'
          } else {
            // Data Model Element Value Out Of Range
            this.apiLastError = '407'

            return 'false'
          }
        } else {
          // Data Model Element Type Mismatch
          this.apiLastError = '406'

          return 'false'
        }

      case 'cmi.score.scaled':
        argStringValue = '' + argValue

        if (this.regex.test(argStringValue)) {
          argFloatValue = parseFloat(argValue)

          if (-1 <= argFloatValue && 1 >= argFloatValue) {
            this.scoData[argName] = argFloatValue
            this.apiLastError = '0'

            return 'true'
          } else {
            // Data Model Element Value Out Of Range
            this.apiLastError = '407'

            return 'false'
          }
        } else {
          // Data Model Element Type Mismatch
          this.apiLastError = '406'

          return 'false'
        }

      case 'cmi.score.raw':
      case 'cmi.score.max':
      case 'cmi.score.min':
        argStringValue = '' + argValue

        if (this.regex.test(argStringValue)) {
          this.scoData[argName] = parseFloat(argValue)
          this.apiLastError = '0'

          return 'true'
        } else {
          // Data Model Element Type Mismatch
          this.apiLastError = '406'

          return 'false'
        }

      case 'cmi.success_status':
        upperCaseSuccessStatus = argValue.toUpperCase()

        if ('PASSED' !== upperCaseSuccessStatus && 'FAILED' !== upperCaseSuccessStatus && 'UNKNOWN' !== upperCaseSuccessStatus) {
          // Data Model Element Type Mismatch
          this.apiLastError = '406'

          return 'false'
        }
        this.scoData[argName] = argValue
        this.apiLastError = '0'

        return 'true'

      case 'cmi.learner_preference.language':
      case 'cmi.location':
      case 'cmi.session_time':
      case 'cmi.suspend_data':
        this.scoData[argName] = argValue
        this.apiLastError = '0'

        return 'true'

      default:
        splitted = argName.split('.')

        if ('cmi' === splitted[0] && splitted[2].trim()) {
          const cmiIndex = 'cmi.' + splitted[1]
          const nIndex = splitted[2]

          if (!this.scoData[cmiIndex]) {
            this.scoData[cmiIndex] = {}
          }
          if (!this.scoData[cmiIndex][nIndex]) {
            this.scoData[cmiIndex][nIndex] = {}
          }
          let upperCaseType = ''
          let upperCaseResult = ''
          let upperCaseObjSuccessStatus = ''
          let upperCaseObjCompletionStatus = ''

          switch (splitted[1]) {
            case 'comments_from_learner' :
            case 'comments_from_lms' :
              switch (splitted[3]) {
                case 'comment':
                case 'location':
                case 'timestamp':
                  if (undefined === this.scoData[cmiIndex][nIndex]) {
                    this.scoData[cmiIndex][nIndex] = {}
                  }
                  this.scoData[cmiIndex][nIndex][splitted[3]] = argValue
                  this.apiLastError = '0'

                  return 'true'

                default:
                  // Undefined Data Model Element
                  this.apiLastError = '401'

                  return 'false'
              }

            case 'interactions' :
              switch (splitted[3]) {
                case 'type':
                  upperCaseType = argValue.toUpperCase()

                  if ('TRUE-FALSE' !== upperCaseType &&
                    'CHOICE' !== upperCaseType &&
                    'FILL-IN' !== upperCaseType &&
                    'LONG-FILL-IN' !== upperCaseType &&
                    'LIKERT' !== upperCaseType &&
                    'MATCHING' !== upperCaseType &&
                    'PERFORMANCE' !== upperCaseType &&
                    'SEQUENCING' !== upperCaseType &&
                    'NUMERIC' !== upperCaseType &&
                    'OTHER' !== upperCaseType
                  ) {
                    // Data Model Element Type Mismatch
                    this.apiLastError = '406'

                    return 'false'
                  } else {
                    if (undefined === this.scoData[cmiIndex][nIndex]) {
                      this.scoData[cmiIndex][nIndex] = {}
                    }
                    this.scoData[cmiIndex][nIndex]['type'] = argValue
                    this.apiLastError = '0'

                    return 'true'
                  }

                case 'weighting':
                  argStringValue = '' + argValue

                  if (this.regex.test(argStringValue)) {
                    if (undefined === this.scoData[cmiIndex][nIndex]) {
                      this.scoData[cmiIndex][nIndex] = {}
                    }
                    this.scoData[cmiIndex][nIndex]['weighting'] = parseFloat(argValue)
                    this.apiLastError = '0'

                    return 'true'
                  } else {
                    // Data Model Element Type Mismatch
                    this.apiLastError = '406'

                    return 'false'
                  }

                case 'result':
                  if (typeof argValue === 'string') {
                    upperCaseResult = argValue.toUpperCase()
                  }

                  if ('CORRECT' === upperCaseResult ||
                    'INCORRECT' === upperCaseResult ||
                    'UNANTICIPATED' === upperCaseResult ||
                    'NEUTRAL' === upperCaseResult
                  ) {
                    if (undefined === this.scoData[cmiIndex][nIndex]) {
                      this.scoData[cmiIndex][nIndex] = {}
                    }
                    this.scoData[cmiIndex][nIndex]['result'] = argValue
                    this.apiLastError = '0'

                    return 'true'
                  } else if (this.regex.test('' + argValue)) {
                    if (undefined === this.scoData[cmiIndex][nIndex]) {
                      this.scoData[cmiIndex][nIndex] = {}
                    }
                    this.scoData[cmiIndex][nIndex]['weighting'] = parseFloat(argValue)
                    this.apiLastError = '0'

                    return 'true'
                  } else {
                    // Data Model Element Type Mismatch
                    this.apiLastError = '406'

                    return 'false'
                  }

                case 'id':
                case 'timestamp':
                case 'learner_response':
                case 'latency':
                case 'description':
                  if (undefined === this.scoData[cmiIndex][nIndex]) {
                    this.scoData[cmiIndex][nIndex] = {}
                  }
                  this.scoData[cmiIndex][nIndex][splitted[3]] = argValue
                  this.apiLastError = '0'

                  return 'true'

                case 'objectives':
                  if ('_count' === splitted[4]) {
                    // Data Model Element Is Read Only
                    this.apiLastError = '404'

                    return 'false'
                  } else {
                    if (splitted[4].trim()) {
                      const objectiveIndex = splitted[4]

                      if ('id' === splitted[5]) {
                        if (undefined === this.scoData[cmiIndex][nIndex]['objectives']) {
                          this.scoData[cmiIndex][nIndex]['objectives'] = {}
                        }
                        if (undefined === this.scoData[cmiIndex][nIndex]['objectives'][objectiveIndex]) {
                          this.scoData[cmiIndex][nIndex]['objectives'][objectiveIndex] = {}
                        }
                        this.scoData[cmiIndex][nIndex]['objectives'][objectiveIndex]['id'] = argValue
                        this.apiLastError = '0'

                        return 'true'
                      } else {
                        // Undefined Data Model Element
                        this.apiLastError = '401'

                        return 'false'
                      }
                    } else {
                      // Undefined Data Model Element
                      this.apiLastError = '401'

                      return 'false'
                    }
                  }

                case 'correct_responses':
                  if ('_count' === splitted[4]) {
                    // Data Model Element Is Read Only
                    this.apiLastError = '404'

                    return 'false'
                  } else {
                    if (splitted[4].trim()) {
                      const responseIndex = splitted[4]

                      if ('pattern' === splitted[5]) {
                        if (undefined === this.scoData[cmiIndex][nIndex]['correct_responses']) {
                          this.scoData[cmiIndex][nIndex]['correct_responses'] = {}
                        }
                        if (undefined === this.scoData[cmiIndex][nIndex]['correct_responses'][responseIndex]) {
                          this.scoData[cmiIndex][nIndex]['correct_responses'][responseIndex] = {}
                        }
                        this.scoData[cmiIndex][nIndex]['correct_responses'][responseIndex]['pattern'] = argValue
                        this.apiLastError = '0'

                        return 'true'
                      } else {
                        // Undefined Data Model Element
                        this.apiLastError = '401'

                        return 'false'
                      }
                    } else {
                      // Undefined Data Model Element
                      this.apiLastError = '401'

                      return 'false'
                    }
                  }

                default:
                  // Undefined Data Model Element
                  this.apiLastError = '401'

                  return 'false'
              }

            case 'objectives' :
              switch (splitted[3]) {
                case 'success_status':
                  upperCaseObjSuccessStatus = argValue.toUpperCase()

                  if ('PASSED' !== upperCaseObjSuccessStatus &&
                    'FAILED' !== upperCaseObjSuccessStatus &&
                    'UNKNOWN' !== upperCaseObjSuccessStatus
                  ) {
                    // Data Model Element Type Mismatch
                    this.apiLastError = '406'

                    return 'false'
                  } else {
                    this.scoData[cmiIndex][nIndex]['success_status'] = argValue
                    this.apiLastError = '0'

                    return 'true'
                  }

                case 'completion_status':
                  upperCaseObjCompletionStatus = argValue.toUpperCase()

                  if ('COMPLETED' !== upperCaseObjCompletionStatus &&
                    'INCOMPLETE' !== upperCaseObjCompletionStatus &&
                    'NOT_ATTEMPTED' !== upperCaseObjCompletionStatus &&
                    'UNKNOWN' !== upperCaseObjCompletionStatus
                  ) {
                    // Data Model Element Type Mismatch
                    this.apiLastError = '406'

                    return 'false'
                  } else {
                    this.scoData[cmiIndex][nIndex]['completion_status'] = argValue
                    this.apiLastError = '0'

                    return 'true'
                  }

                case 'progress_measure':
                  argStringValue = '' + argValue

                  if (this.regex.test(argStringValue)) {
                    argFloatValue = parseFloat(argValue)

                    if (0 <= argFloatValue && 1 >= argFloatValue) {
                      this.scoData[cmiIndex][nIndex]['progress_measure'] = argFloatValue
                      this.apiLastError = '0'

                      return 'true'
                    } else {
                      // Data Model Element Value Out Of Range
                      this.apiLastError = '407'

                      return 'false'
                    }
                  } else {
                    // Data Model Element Type Mismatch
                    this.apiLastError = '406'

                    return 'false'
                  }

                case 'id':
                case 'description':
                  this.scoData[cmiIndex][nIndex][splitted[3]] = argValue
                  this.apiLastError = '0'

                  return 'true'

                case 'score':
                  switch (splitted[4]) {
                    case '_children':
                      // Data Model Element Is Read Only
                      this.apiLastError = '404'

                      return 'false'

                    case 'scaled':
                      if (undefined === this.scoData[cmiIndex][nIndex]['score._children']) {
                        this.scoData[cmiIndex][nIndex]['score._children'] = 'scaled,raw,min,max'
                      }
                      argStringValue = '' + argValue

                      if (this.regex.test(argStringValue)) {
                        argFloatValue = parseFloat(argValue)

                        if (-1 <= argFloatValue && 1 >= argFloatValue) {
                          this.scoData[cmiIndex][nIndex]['score.scaled'] = argFloatValue
                          this.apiLastError = '0'

                          return 'true'
                        } else {
                          // Data Model Element Value Out Of Range
                          this.apiLastError = '407'

                          return 'false'
                        }
                      } else {
                        // Data Model Element Type Mismatch
                        this.apiLastError = '406'

                        return 'false'
                      }

                    case 'raw':
                    case 'min':
                    case 'max':
                      if (undefined === this.scoData[cmiIndex][nIndex]['score._children']) {
                        this.scoData[cmiIndex][nIndex]['score._children'] = 'scaled,raw,min,max'
                      }
                      argStringValue = '' + argValue

                      if (this.regex.test(argStringValue)) {
                        argFloatValue = parseFloat(argValue)
                        this.scoData[cmiIndex][nIndex]['score.' + splitted[4]] = argFloatValue
                        this.apiLastError = '0'

                        return 'true'
                      } else {
                        // Data Model Element Type Mismatch
                        this.apiLastError = '406'

                        return 'false'
                      }

                    default:
                      // Undefined Data Model Element
                      this.apiLastError = '401'

                      return 'false'
                  }

                default:
                  // Undefined Data Model Element
                  this.apiLastError = '401'

                  return 'false'
              }

            default:
              // Undefined Data Model Element
              this.apiLastError = '401'

              return ''
          }
        } else {
          // Undefined Data Model Element
          this.apiLastError = '401'

          return ''
        }
    }
  }

  this.Commit = (arg) => {
    console.log('Commit', arg)

    if ('' !== arg) {
      // General Argument Error
      this.apiLastError = '201'

      return 'false'
    } else if (!this.apiInitialized) {
      // Commit Before Initialization
      this.apiLastError = '142'

      return 'false'
    } else if (this.apiTerminated) {
      // Commit After Termination
      this.apiLastError = '143'

      return 'false'
    }
    this.apiLastError = '0'
    commitResult(sco.id, 'persist', this.scoData, dispatch, currentUser)

    return 'true'
  }

  this.GetLastError = () => {
    console.log('GetLastError')

    return this.apiLastError
  }

  this.GetErrorString = (errorCode) => {
    console.log('GetErrorString', errorCode)

    let error = ''

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
        error = scorm2004Errors[errorCode]
        break
    }

    return error
  }

  this.GetDiagnostic = (errorCode) => {
    console.log('GetDiagnostic', errorCode)

    let error = ''
    let index = errorCode

    if (index === '') {
      index = this.apiLastError
    }
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
        error =  scorm2004Errors[index]
    }

    return error
  }
}

export {
  APIClass
}
