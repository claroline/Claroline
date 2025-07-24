import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ContentCreationModal as ContentCreationModalComponent} from '#/main/core/widget/content/modals/creation/components/modal'
import {actions, reducer, selectors} from '#/main/core/widget/content/modals/creation/store'

const ContentCreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      availableTypes: selectors.availableWidgets(state),
      availableSources: selectors.availableSources(state),
      instance: selectors.instance(state)
    }),
    (dispatch) => ({
      fetchContents(contextType, contextId) {
        dispatch(actions.fetchContents(contextType, contextId))
      },

      update(prop, value) {
        dispatch(actions.update(prop, value))
      },

      reset() {
        dispatch(actions.reset())
      }
    })
  )(ContentCreationModalComponent)
)

export {
  ContentCreationModal
}
