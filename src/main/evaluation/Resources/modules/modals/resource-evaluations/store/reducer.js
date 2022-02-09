import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/evaluation/modals/resource-evaluations/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
