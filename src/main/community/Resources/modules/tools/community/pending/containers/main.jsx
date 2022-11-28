import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {PendingMain as PendingMainComponent} from '#/main/community/tools/community/pending/components/main'
import {actions} from '#/main/community/tools/community/pending/store'

const PendingMain = connect(
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
)(PendingMainComponent)

export {
  PendingMain
}
