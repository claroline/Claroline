import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {selectors} from '#/integration/big-blue-button/resources/bbb/records/store/selectors'

const reducer = makeListReducer(selectors.LIST_NAME, {sortBy: {property: 'startTime', direction: -1}})

export {
  reducer
}
