import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/authentication/integration/tokens/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
