import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/tools/community/pending/store/selectors'

const reducer = makeListReducer(selectors.LIST_NAME)

export {
  reducer
}
