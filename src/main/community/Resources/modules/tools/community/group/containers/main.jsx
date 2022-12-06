import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {GroupMain as GroupMainComponent} from '#/main/community/tools/community/group/components/main'
import {actions} from '#/main/community/tools/community/group/store'

const GroupMain = connect(
  state => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state))
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
