import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'

// temp
// todo : replace by a better code later if we keep iFrame compatibility
const AVAILABLE_EMBEDDED_RESOURCES = [
  'claroline_announcement_aggregate',
  'claroline_dropzone',
  'claroline_forum',
  'claroline_scorm',
  'claroline_web_resource',
  'innova_path',
  'icap_bibliography',
  'icap_blog',
  'icap_lesson',
  'icap_wiki',
  'text',
  'ujm_exercise'
]

const NUMBERING_NONE    = 'none'
const NUMBERING_NUMERIC = 'numeric'
const NUMBERING_LITERAL = 'literal'
const NUMBERING_CUSTOM  = 'custom'

const PATH_NUMBERINGS = {
  [NUMBERING_NONE]: trans('path_numbering_none', {}, 'path'),
  [NUMBERING_NUMERIC]: trans('path_numbering_numeric', {}, 'path'),
  [NUMBERING_LITERAL]: trans('path_numbering_literal', {}, 'path'),
  [NUMBERING_CUSTOM]: trans('path_numbering_custom', {}, 'path')
}

const STATUS_UNSEEN = 'unseen'
const STATUS_SEEN = 'seen'
const STATUS_TO_DO = 'to_do'
const STATUS_DONE = 'done'
const STATUS_TO_REVIEW = 'to_review'

const STEP_MANUAL_STATUS = {
  [STATUS_TO_DO]: trans('user_progression_step_to_do_label', {}, 'path'),
  [STATUS_DONE]: trans('user_progression_step_done_label', {}, 'path'),
  [STATUS_TO_REVIEW]: trans('user_progression_step_to_review_label', {}, 'path')
}

const STEP_STATUS = merge({}, {
  [STATUS_UNSEEN]: trans('user_progression_step_unseen_label', {}, 'path'),
  [STATUS_SEEN]: trans('user_progression_step_seen_label', {}, 'path')
}, STEP_MANUAL_STATUS)

export const constants = {
  AVAILABLE_EMBEDDED_RESOURCES,
  NUMBERING_NONE,
  NUMBERING_NUMERIC,
  NUMBERING_LITERAL,
  NUMBERING_CUSTOM,
  PATH_NUMBERINGS,
  STATUS_UNSEEN,
  STATUS_SEEN,
  STATUS_TO_DO,
  STATUS_DONE,
  STATUS_TO_REVIEW,
  STEP_STATUS,
  STEP_MANUAL_STATUS
}
