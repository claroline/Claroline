import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_USER_ABOUT} from '#/main/community/user/modals/about/store/actions'

const reducer = makeReducer(null, {
  [LOAD_USER_ABOUT]: (state, action) => action.user
})

export {
  reducer
}
