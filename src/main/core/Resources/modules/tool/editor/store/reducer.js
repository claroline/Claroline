import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/tool/editor/store/selectors'
import {makeReducer} from '#/main/app/store/reducer'
import {TOOL_OPEN} from '#/main/core/tool/store'

const reducer = makeFormReducer(selectors.STORE_NAME, {}, {
  data: makeReducer({}, {
    [TOOL_OPEN]: () => ({})
  })
})

export {
  reducer
}
