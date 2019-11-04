import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/modals/connection-messages/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
