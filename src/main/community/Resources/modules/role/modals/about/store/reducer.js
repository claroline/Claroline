import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_ROLE_ABOUT} from '#/main/community/role/modals/about/store/actions'

const reducer = makeReducer(null, {
  [LOAD_ROLE_ABOUT]: (state, action) => action.role
})

export {
  reducer
}
