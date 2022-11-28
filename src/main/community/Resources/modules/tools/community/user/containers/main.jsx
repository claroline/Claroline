import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as communitySelectors} from '#/main/community/tools/community/store'
import {UserMain as UserMainComponent} from '#/main/community/tools/community/user/components/main'
import {actions, selectors} from '#/main/community/tools/community/user/store'

const UserMain = connect(
  state => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    canRegister: communitySelectors.canCreate(state),
    limitReached: selectors.limitReached(state)
  }),
  dispatch => ({
    open(id) {
      dispatch(actions.open(id))
    },
    new() {
      dispatch(actions.new())
    }
  })
)(UserMainComponent)

export {
  UserMain
}
