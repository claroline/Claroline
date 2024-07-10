import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/evaluation/tools/evaluation/editor/skill/store/selectors'

const reducer = makeListReducer(selectors.LIST_NAME)

export {
  reducer
}
