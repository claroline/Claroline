import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {TOOL_OPEN} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/community/tools/community/pending/store/selectors'

const reducer = makeListReducer(selectors.LIST_NAME, {}, {
  invalidated: makeReducer(false, {
    [TOOL_OPEN]: () => true
  })
})

export {
  reducer
}
