import {trans} from '#/main/core/translation'

const SORT_DISPLAY_OLDER_TO_NEWER = 1
const SORT_DISPLAY_NEWER_TO_OLDER= -1
const DISPLAY_TABLE_SM= 'table-sm'
const DISPLAY_TABLE= 'table'
const DISPLAY_LIST_SM= 'list-sm'
const DISPLAY_LIST= 'list'
const DISPLAY_TILES    = 'tiles'
const DISPLAY_TILES_SM = 'tiles-sm'
const VALIDATE_NONE = 'NONE'
const VALIDATE_PRIOR_ONCE = 'PRIOR_ONCE'
const VALIDATE_PRIOR_ALL = 'PRIOR_ALL'

const MESSAGE_SORT_DISPLAY = {
  [SORT_DISPLAY_OLDER_TO_NEWER]: trans('from_older_to_newer', {}, 'forum'),
  [SORT_DISPLAY_NEWER_TO_OLDER]: trans('from_newer_to_older', {}, 'forum')
}

const MODERATION_MODES = {
  [VALIDATE_NONE]: trans('no_moderation', {}, 'forum'),
  [VALIDATE_PRIOR_ONCE]: trans('first_message_moderated', {}, 'forum'),
  [VALIDATE_PRIOR_ALL]: trans('all_message_moderated', {}, 'forum')
}

const LIST_DISPLAY_MODES = {
  [DISPLAY_TABLE_SM]: trans('list_display_table_sm'),
  [DISPLAY_TABLE]: trans('list_display_table'),
  [DISPLAY_LIST_SM]: trans('list_display_list_sm'),
  [DISPLAY_LIST]: trans('list_display_list'),
  [DISPLAY_TILES]: trans('list_display_tiles'),
  [DISPLAY_TILES_SM]: trans('list_display_tiles_sm')
}

export const constants = {
  MESSAGE_SORT_DISPLAY,
  LIST_DISPLAY_MODES,
  MODERATION_MODES
}
