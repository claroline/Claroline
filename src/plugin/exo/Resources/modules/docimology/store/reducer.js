import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_DOCIMOLOGY} from '#/plugin/exo/docimology/store/actions'

const reducer = makeReducer({}, {
  [LOAD_DOCIMOLOGY]: (state, action) => action.stats
})

export {
  reducer
}
