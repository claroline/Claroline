import {trans} from '#/main/app/intl/translation'

export const SUBMISSION_TYPE_FILE = 'file'
export const SUBMISSION_TYPE_RICH_TEXT = 'rich_text'
export const SUBMISSION_TYPE_URL = 'url'
export const SUBMISSION_TYPE_NONE = 'none'

export const SUBMISSION_TYPES = {
  [SUBMISSION_TYPE_FILE]: trans('file'),
  [SUBMISSION_TYPE_RICH_TEXT]: trans('rich_text'),
  [SUBMISSION_TYPE_URL]: trans('url'),
  [SUBMISSION_TYPE_NONE]: trans('none')
}

export const GRADING_MODE_RAW_SCORE = 'raw_score'
export const GRADING_MODE_RUBRIC = 'rubric'

export const GRADING_MODES = {
  [GRADING_MODE_RAW_SCORE]: trans('raw_score', {}, 'project'),
  [GRADING_MODE_RUBRIC]: trans('rubric', {}, 'project')
}

export const DEADLINE_TYPE_FIXED = 'fixed'
export const DEADLINE_TYPE_RELATIVE = 'relative'
export const DEADLINE_TYPE_NONE = 'none'

export const DEADLINE_TYPES = {
  [DEADLINE_TYPE_FIXED]: trans('fixed_date', {}, 'project'),
  [DEADLINE_TYPE_RELATIVE]: trans('relative_deadline', {}, 'project'),
  [DEADLINE_TYPE_NONE]: trans('none')
}

export const CORRECTION_STATUS_IN_PROGRESS = 'in_progress'
export const CORRECTION_STATUS_SUBMITTED = 'submitted'

export const CORRECTION_STATUSES = {
  [CORRECTION_STATUS_IN_PROGRESS]: trans('in_progress'),
  [CORRECTION_STATUS_SUBMITTED]: trans('submitted')
}
