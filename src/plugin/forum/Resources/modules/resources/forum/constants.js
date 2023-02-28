import {trans} from '#/main/app/intl/translation'

const SORT_DISPLAY_OLDER_TO_NEWER = 1
const SORT_DISPLAY_NEWER_TO_OLDER= -1

const MESSAGE_SORT_DISPLAY = {
  [SORT_DISPLAY_OLDER_TO_NEWER]: trans('from_older_to_newer', {}, 'forum'),
  [SORT_DISPLAY_NEWER_TO_OLDER]: trans('from_newer_to_older', {}, 'forum')
}

const VALIDATE_NONE = 'NONE'
const VALIDATE_PRIOR_ONCE = 'PRIOR_ONCE'
const VALIDATE_PRIOR_ALL = 'PRIOR_ALL'

const MODERATION_MODES = {
  [VALIDATE_NONE]: trans('no_moderation', {}, 'forum'),
  [VALIDATE_PRIOR_ONCE]: trans('first_message_moderated', {}, 'forum'),
  [VALIDATE_PRIOR_ALL]: trans('all_message_moderated', {}, 'forum')
}

export const constants = {
  MESSAGE_SORT_DISPLAY,
  VALIDATE_NONE,
  VALIDATE_PRIOR_ONCE,
  VALIDATE_PRIOR_ALL,
  MODERATION_MODES
}
