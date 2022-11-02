import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {PendingTab as PendingTabComponent} from '#/main/community/tools/community/pending/components/tab'
import {actions} from '#/main/community/tools/community/pending/store'

const PendingTab = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    register(users, workspace) {
      dispatch(actions.register(users, workspace))
    },
    remove(users, workspace) {
      dispatch(actions.remove(users, workspace))
    }
  })
)(PendingTabComponent)

export {
  PendingTab
}
