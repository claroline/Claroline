import {trans} from '#/main/app/intl/translation'

const SEARCH_FULL    = 'full'
const SEARCH_UNIFIED = 'unified'

const DEFAULT_SEARCH_TYPE = SEARCH_UNIFIED

const SEARCH_TYPES = {
  [SEARCH_FULL]   : trans('search_full'),
  [SEARCH_UNIFIED]: trans('search_unified')
}

export const constants = {
  DEFAULT_SEARCH_TYPE,
  SEARCH_FULL,
  SEARCH_UNIFIED,
  SEARCH_TYPES
}
