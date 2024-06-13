import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {CreationModal as BaseCreationModal} from '#/main/app/contexts/workspace/modals/creation/components/modal'
import {reducer, selectors} from '#/main/app/contexts/workspace/modals/creation/store'

const CreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      create() {

      }
    })
  )(BaseCreationModal)
)

export {
  CreationModal
}
