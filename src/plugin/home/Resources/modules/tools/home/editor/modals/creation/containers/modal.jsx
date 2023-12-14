import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TabCreationModal as TabCreationModalComponent} from '#/plugin/home/tools/home/editor/modals/creation/components/modal'
import {actions, reducer, selectors} from '#/plugin/home/tools/home/editor/modals/creation/store'

const TabCreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      currentContext: toolSelectors.context(state),
      tab: selectors.tab(state),
      saveEnabled: selectors.saveEnabled(state)
    }),
    (dispatch) => ({
      startCreation(tabType, position) {
        dispatch(actions.startCreation(tabType, position))
      },
      update(field, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, field, value))
      },
      setErrors(errors) {
        dispatch(formActions.setErrors(selectors.STORE_NAME, errors))
      },
      reset() {
        dispatch(actions.reset())
      }
    })
  )(TabCreationModalComponent)
)

export {
  TabCreationModal
}
