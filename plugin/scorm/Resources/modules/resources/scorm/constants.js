import {trans} from '#/main/core/translation'

const SCORM_12 = 'scorm_12'
const SCORM_2004 = 'scorm_2004'

const STATUS_NOT_ATTEMPTED = 'not_attempted'
const STATUS_UNKNOWN = 'unknown'
const STATUS_BROWSED = 'browsed'
const STATUS_INCOMPLETE = 'incomplete'
const STATUS_COMPLETED = 'completed'
const STATUS_FAILED = 'failed'
const STATUS_PASSED = 'passed'

const LESSON_STATUS_LIST_12 = {
  [STATUS_NOT_ATTEMPTED]: trans(STATUS_NOT_ATTEMPTED, {}, 'scorm'),
  [STATUS_BROWSED]: trans(STATUS_BROWSED, {}, 'scorm'),
  [STATUS_INCOMPLETE]: trans(STATUS_INCOMPLETE, {}, 'scorm'),
  [STATUS_COMPLETED]: trans(STATUS_COMPLETED, {}, 'scorm'),
  [STATUS_FAILED]: trans(STATUS_FAILED, {}, 'scorm'),
  [STATUS_PASSED]: trans(STATUS_PASSED, {}, 'scorm')
}

const LESSON_STATUS_LIST_2004 = {
  [STATUS_UNKNOWN]: trans(STATUS_UNKNOWN, {}, 'scorm'),
  [STATUS_FAILED]: trans(STATUS_FAILED, {}, 'scorm'),
  [STATUS_PASSED]: trans(STATUS_PASSED, {}, 'scorm')
}

const COMPLETION_STATUS_LIST_2004 = {
  [STATUS_NOT_ATTEMPTED]: trans(STATUS_NOT_ATTEMPTED, {}, 'scorm'),
  [STATUS_UNKNOWN]: trans(STATUS_UNKNOWN, {}, 'scorm'),
  [STATUS_INCOMPLETE]: trans(STATUS_INCOMPLETE, {}, 'scorm'),
  [STATUS_COMPLETED]: trans(STATUS_COMPLETED, {}, 'scorm')
}

const DISPLAY_RATIO_LIST = {
  '56.25': '16:9',
  '75': '4:3',
  '100': '1:1'
}

export const constants = {
  SCORM_12,
  SCORM_2004,
  LESSON_STATUS_LIST_12,
  LESSON_STATUS_LIST_2004,
  COMPLETION_STATUS_LIST_2004,
  DISPLAY_RATIO_LIST
}
