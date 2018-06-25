import {trans} from '#/main/core/translation'

const WIKI_MODE_NORMAL = 0
const WIKI_MODE_MODERATE = 1
const WIKI_MODE_READ_ONLY = 2

const WIKI_MODES = {
  [WIKI_MODE_NORMAL]: 'normal',
  [WIKI_MODE_MODERATE]: 'moderate',
  [WIKI_MODE_READ_ONLY]: 'read_only'
}

const WIKI_MODE_CHOICES = {
  [WIKI_MODE_NORMAL]: trans('normal', {}, 'icap_wiki'),
  [WIKI_MODE_MODERATE]: trans('moderate', {}, 'icap_wiki'),
  [WIKI_MODE_READ_ONLY]: trans('read_only', {}, 'icap_wiki')
}

export {
  WIKI_MODE_NORMAL,
  WIKI_MODE_MODERATE,
  WIKI_MODE_READ_ONLY,
  WIKI_MODES,
  WIKI_MODE_CHOICES
}