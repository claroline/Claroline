import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {AboutModal as AboutModalComponent} from '#/main/app/layout/sections/administration/modals/about/components/modal'
import {actions, reducer, selectors} from '#/main/app/layout/sections/administration/modals/about/store'

const AboutModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      version: selectors.version(state),
      changelogs: selectors.changelogs(state)
    }),
    (dispatch) => ({
      get(workspaceId) {
        dispatch(actions.get(workspaceId))
      }
    })
  )(AboutModalComponent)
)

export {
  AboutModal
}
