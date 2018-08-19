import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

import {
  SECTION_PERMANENTLY_REMOVED,
  SECTION_RESTORED
} from '#/plugin/wiki/resources/wiki/deleted/store/actions'

import {
  SECTION_DELETED
} from '#/plugin/wiki/resources/wiki/player/store/actions'

export const reducer = makeListReducer(selectors.STORE_NAME +'.deletedSections', {sortBy: { property: 'deletionDate', direction: -1 }}, {
  invalidated: makeReducer(false, {
    [SECTION_PERMANENTLY_REMOVED]: () => true,
    [SECTION_RESTORED]: () => true,
    [SECTION_DELETED]: () => true
  })
})
