import {constants as listConstants} from '#/main/app/content/list/constants'

import {trans} from '#/main/app/intl/translation'

const FILE_TYPES = {
  'audio/*': trans('audio', {}, 'clacoform'),
  'image/*': trans('image', {}, 'clacoform'),
  'video/*': trans('video', {}, 'clacoform'),
  'application/pdf': 'PDF'
}

const ENTRY_STATUS_PENDING = 0
const ENTRY_STATUS_PUBLISHED = 1
const ENTRY_STATUS_UNPUBLISHED = 2

const CHOICE_MENU = 'menu'
const CHOICE_RANDOM = 'random'
const CHOICE_SEARCH = 'search'
const CHOICE_ADD = 'add'

const CHOICE_ALL = 'all'
const CHOICE_NONE = 'none'

const CHOICE_DOWN = 'down'
const CHOICE_UP = 'up'
const CHOICE_BOTH = 'both'

const CHOICE_MANAGER = 'manager'
const CHOICE_USER = 'user'
const CHOICE_ANONYMOUS = 'anonymous'

const DEFAULT_HOME_CHOICES = {
  [CHOICE_MENU]: trans('menu', {}, 'clacoform'),
  [CHOICE_RANDOM]: trans('random_mode', {}, 'clacoform'),
  [CHOICE_SEARCH]: trans('search_mode', {}, 'clacoform'),
  [CHOICE_ADD]: trans('entry_addition', {}, 'clacoform')
}

const MENU_POSITION_CHOICES = {
  [CHOICE_DOWN]: trans('choice_menu_position_down', {}, 'clacoform'),
  [CHOICE_UP]: trans('choice_menu_position_up', {}, 'clacoform'),
  [CHOICE_BOTH]: trans('both', {}, 'clacoform'),
  [CHOICE_NONE]: trans('none')
}

const DISPLAY_METADATA_CHOICES = {
  [CHOICE_ALL]: trans('yes'),
  [CHOICE_NONE]: trans('no'),
  [CHOICE_MANAGER]: trans('choice_manager_only', {}, 'clacoform')
}

const LOCKED_FIELDS_FOR_CHOICES = {
  [CHOICE_USER]: trans('choice_user_only', {}, 'clacoform'),
  [CHOICE_MANAGER]: trans('choice_manager_only', {}, 'clacoform'),
  [CHOICE_ALL]: trans('both', {}, 'clacoform')
}

const MODERATE_COMMENTS_CHOICES = {
  [CHOICE_ALL]: trans('yes'),
  [CHOICE_NONE]: trans('no'),
  [CHOICE_ANONYMOUS]: trans('choice_anonymous_comments_only', {}, 'clacoform')
}

const DISPLAY_MODES_CHOICES = {}
Object.keys(listConstants.DISPLAY_MODES).forEach(key => DISPLAY_MODES_CHOICES[key] = listConstants.DISPLAY_MODES[key].label)

export const constants = {
  FILE_TYPES,
  ENTRY_STATUS_PENDING,
  ENTRY_STATUS_PUBLISHED,
  ENTRY_STATUS_UNPUBLISHED,
  DEFAULT_HOME_CHOICES,
  MENU_POSITION_CHOICES,
  DISPLAY_METADATA_CHOICES,
  LOCKED_FIELDS_FOR_CHOICES,
  MODERATE_COMMENTS_CHOICES,
  DISPLAY_MODES_CHOICES
}