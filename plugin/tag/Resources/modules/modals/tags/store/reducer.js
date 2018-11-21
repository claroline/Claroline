import {makeReducer} from '#/main/app/store/reducer'

import {TAGS_LOAD} from '#/plugin/tag/modals/tags/store/actions'

const reducer = makeReducer([], {
  [TAGS_LOAD]: (state, action) => action.tags || []
})

export {
  reducer
}