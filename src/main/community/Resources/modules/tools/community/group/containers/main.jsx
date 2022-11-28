import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as communitySelectors} from '#/main/community/tools/community/store'
import {GroupMain as GroupMainComponent} from '#/main/community/tools/community/group/components/main'
import {actions} from '#/main/community/tools/community/group/store'

const GroupMain = connect(
  state => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    canRegister: communitySelectors.canCreate(state)
  }),
  dispatch => ({
    open(id) {
      dispatch(actions.open(id))
    },
    new() {
      dispatch(actions.new())
    }
  })
)(GroupMainComponent)

export {
  GroupMain
}
