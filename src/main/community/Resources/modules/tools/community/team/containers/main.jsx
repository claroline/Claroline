import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TeamMain as TeamMainComponent} from '#/main/community/tools/community/team/components/main'
import {actions} from '#/main/community/tools/community/team/store'

const TeamMain = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    canCreate: hasPermission('edit', toolSelectors.toolData(state))
  }),
  dispatch => ({
    open(id) {
      dispatch(actions.open(id))
    },
    new(contextData) {
      dispatch(actions.new({
        workspace: contextData
      }))
    }
  })
)(TeamMainComponent)

export {
  TeamMain
}
