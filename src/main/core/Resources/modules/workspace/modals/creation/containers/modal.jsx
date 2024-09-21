import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {actions as formActions} from '#/main/app/content/form'

import {CreationModal as BaseCreationModal} from '#/main/core/workspace/modals/creation/components/modal'
import {reducer, selectors} from '#/main/core/workspace/modals/creation/store'

const CreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      startCreation(baseData, creationType) {
        dispatch(formActions.load(selectors.STORE_NAME, baseData))
      },
      create() {
        return dispatch(formActions.save(selectors.STORE_NAME, ['apiv2_workspace_create']))
      },
      reset() {
        dispatch(formActions.reset(selectors.STORE_NAME, {}, true))
      }
    })
  )(BaseCreationModal)
)

export {
  CreationModal
}
