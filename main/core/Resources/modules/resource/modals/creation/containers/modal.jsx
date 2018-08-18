import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ResourceCreationModal as ResourceCreationModalComponent} from '#/main/core/resource/modals/creation/components/modal'
import {actions, reducer, selectors} from '#/main/core/resource/modals/creation/store'

const ResourceCreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      newNode: selectors.newNode(state),
      saveEnabled: selectors.saveEnabled(state)
    }),
    (dispatch) => ({
      startCreation(parent, resourceType) {
        dispatch(actions.startCreation(parent, resourceType))
      },

      updateRights(perms) {
        dispatch(actions.updateNode('rights', perms))
      },
      save(parent, close) {
        dispatch(actions.create(parent)).then(close)
      },
      reset() {
        dispatch(actions.reset())
      }
    })
  )(ResourceCreationModalComponent)
)

export {
  ResourceCreationModal
}
