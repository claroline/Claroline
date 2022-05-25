import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/open-badge/modals/badges/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
