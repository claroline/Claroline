import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/path/analytics/workspace/path/modals/participants/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
