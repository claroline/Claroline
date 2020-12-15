import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/session/store/selectors'

export const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'startDate', direction: 1}
})
