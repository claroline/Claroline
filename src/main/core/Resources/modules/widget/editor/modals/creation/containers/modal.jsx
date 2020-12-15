import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {WidgetCreationModal as WidgetCreationModalComponent} from '#/main/core/widget/editor/modals/creation/components/modal'
import {actions, reducer, selectors} from '#/main/core/widget/editor/modals/creation/store'

const WidgetCreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      widget: selectors.widget(state),
      saveEnabled: selectors.saveEnabled(state)
    }),
    (dispatch) => ({
      startCreation(layout) {
        dispatch(actions.startCreation(layout))
      },
      reset() {
        dispatch(actions.reset())
      }
    })
  )(WidgetCreationModalComponent)
)

export {
  WidgetCreationModal
}
