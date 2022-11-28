import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_GROUP_ABOUT} from '#/main/community/group/modals/about/store/actions'

const reducer = makeReducer(null, {
  [LOAD_GROUP_ABOUT]: (state, action) => action.group
})

export {
  reducer
}
