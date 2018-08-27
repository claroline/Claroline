import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/widget/types/list/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {}, {}, {selectable: false})

export {
  reducer
}
