import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/events/store/selectors'

export const reducer = makeListReducer(selectors.LIST_NAME, {
  sortBy: {property: 'startDate', direction: 1}
})
