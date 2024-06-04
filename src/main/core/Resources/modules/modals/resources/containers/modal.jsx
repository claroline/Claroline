import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions} from '#/main/app/content/list/store'

import {ResourcesModal as ResourcesModalComponent} from '#/main/core/modals/resources/components/modal'
import {actions, reducer, selectors} from '#/main/core/modals/resources/store'

const ResourcesModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentDirectory: selectors.current(state),
      selected: selectors.selectedFull(state)
    }),
    (dispatch) => ({
      setCurrent(directoryNode = null, filters = []) {
        dispatch(actions.setCurrent(directoryNode, filters))
      },
      reset() {
        dispatch(listActions.resetSelect(selectors.LIST_NAME))
        dispatch(listActions.invalidateData(selectors.LIST_NAME))
      }
    })
  )(ResourcesModalComponent)
)

export {
  ResourcesModal
}
