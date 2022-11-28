import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_ORGANIZATION_ABOUT} from '#/main/community/organization/modals/about/store/actions'

const reducer = makeReducer(null, {
  [LOAD_ORGANIZATION_ABOUT]: (state, action) => action.organization
})

export {
  reducer
}
