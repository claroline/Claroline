import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/analytics/tools/dashboard/progression/modals/resource-evaluations/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
