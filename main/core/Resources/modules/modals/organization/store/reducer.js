import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/modals/organization/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
