import {t} from '#/main/core/translation'

export const LIST_DISPLAY_LIST_VALUE     = 'list'
export const LIST_DISPLAY_TILES_SM_VALUE = 'tiles-sm'
export const LIST_DISPLAY_TILES_LG_VALUE = 'tiles-lg'

export const LIST_DISPLAY_LIST     = [LIST_DISPLAY_LIST_VALUE,     t('list_format_list'),    'fa fa-fw fa-list']
export const LIST_DISPLAY_TILES_SM = [LIST_DISPLAY_TILES_SM_VALUE, t('list_format_tiles_sm'), 'fa fa-fw fa-th']
export const LIST_DISPLAY_TILES_LG = [LIST_DISPLAY_TILES_LG_VALUE, t('list_format_tiles_lg'), 'fa fa-fw fa-th-large']

export const DEFAULT_LIST_DISPLAYS = [
  LIST_DISPLAY_LIST,
  LIST_DISPLAY_TILES_SM,
  LIST_DISPLAY_TILES_LG
]

export const DEFAULT_LIST_DISPLAY = 'list'
