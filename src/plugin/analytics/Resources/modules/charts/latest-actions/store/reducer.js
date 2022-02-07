import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {selectors} from '#/plugin/analytics/charts/latest-actions/store/selectors'

export const reducer = makeListReducer(selectors.STORE_NAME, {
  filters: [],
  data: [],
  sortBy: {property: 'dateLog', direction: -1},
  pagination: {page: 0, pageSize: 20}
})
