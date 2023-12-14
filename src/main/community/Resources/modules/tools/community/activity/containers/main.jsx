import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ActivityMain as ActivityMainComponent} from '#/main/community/tools/community/activity/components/main'
import {actions, selectors} from '#/main/community/tools/community/activity/store'

const ActivityMain = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextId: toolSelectors.contextId(state),
    count: selectors.count(state)
  }),
  (dispatch) => ({
    fetch(contextId) {
      return dispatch(actions.fetch(contextId))
    }
  })
)(ActivityMainComponent)

export {
  ActivityMain
}
