import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'
import {selectors} from '#/main/community/tools/community/pending/store/selectors'

const reducer = makeListReducer(selectors.LIST_NAME, {}, {
  invalidated: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true
  })
})

export {
  reducer
}
