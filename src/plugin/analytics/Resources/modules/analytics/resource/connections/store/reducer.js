import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/analytics/resource/dashboard/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME + '.connections', {
  sortBy: {property: 'connectionDate', direction: -1}
})

export {
  reducer
}
