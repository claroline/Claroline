import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_TEAM_ABOUT} from '#/main/community/team/modals/about/store/actions'

const reducer = makeReducer(null, {
  [LOAD_TEAM_ABOUT]: (state, action) => action.team
})

export {
  reducer
}
