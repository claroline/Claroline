import {makeReducer} from '#/main/app/store/reducer'

import {ICON_COLLECTION_LOAD} from '#/main/theme/icon/store/actions'

export const reducer = makeReducer({}, {
  [ICON_COLLECTION_LOAD]: (state, action) => action.icons
})
