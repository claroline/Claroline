import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ContentCreationModal as ContentCreationModalComponent} from '#/main/core/widget/content/modals/creation/components/modal'
import {actions, reducer, selectors} from '#/main/core/widget/content/modals/creation/store'

const ContentCreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      availableTypes: selectors.availableWidgets(state),
      saveEnabled: selectors.saveEnabled(state),
      instance: selectors.instance(state)
    }),
    (dispatch) => ({
      fetchContents(context) {
        dispatch(actions.fetchContents(context.type))
      },

      startCreation(widgetType) {
        dispatch(actions.startCreation(widgetType.name))
      }
    })
  )(ContentCreationModalComponent)
)

export {
  ContentCreationModal
}