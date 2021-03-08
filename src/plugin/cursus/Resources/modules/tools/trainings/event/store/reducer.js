import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/event/store/selectors'

export const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'startDate', direction: 1}
})
