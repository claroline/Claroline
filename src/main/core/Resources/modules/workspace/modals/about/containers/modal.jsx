import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {AboutModal as AboutModalComponent} from '#/main/core/workspace/modals/about/components/modal'
import {actions, reducer, selectors} from '#/main/core/workspace/modals/about/store'

const AboutModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      workspace: selectors.workspace(state)
    }),
    (dispatch) => ({
      get(workspaceId) {
        dispatch(actions.get(workspaceId))
      },
      reset() {
        dispatch(actions.load(null))
      }
    })
  )(AboutModalComponent)
)

export {
  AboutModal
}
