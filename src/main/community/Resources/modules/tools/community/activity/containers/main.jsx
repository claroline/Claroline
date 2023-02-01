import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ActivityMain as ActivityMainComponent} from '#/main/community/tools/community/activity/components/main'
import {actions, selectors} from '#/main/community/tools/community/activity/store'

const ActivityMain = connect(
  (state) => {
    return ({
      path: toolSelectors.path(state),
      contextId: toolSelectors.contextId(state),
      count: selectors.count(state),
      actionTypes: selectors.actionTypes(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state))
    })
  },
  (dispatch) => ({
    fetch(contextId) {
      return dispatch(actions.fetch(contextId))
    }
  })
)(ActivityMainComponent)

export {
  ActivityMain
}
