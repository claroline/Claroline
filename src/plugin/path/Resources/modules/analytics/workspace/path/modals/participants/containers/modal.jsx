import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/plugin/path/analytics/workspace/path/modals/participants/store'
import {ParticipantsModal as ParticipantsModalComponent} from '#/plugin/path/analytics/workspace/path/modals/participants/components/modal'

const ParticipantsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      reset() {
        dispatch(listActions.resetSelect(selectors.STORE_NAME))
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(ParticipantsModalComponent)
)

export {
  ParticipantsModal
}
