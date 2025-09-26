import {connect} from 'react-redux'

import {param} from '#/main/app/config'
import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions} from '#/main/app/content/list/store/actions'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool'

import {SendingModal as SendingModalComponent} from '#/plugin/announcement/tools/announcement/modals/sending/components/modal'
import {reducer, selectors} from '#/plugin/announcement/tools/announcement/modals/sending/store'

const SendingModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      schedulerEnabled: param('schedulerEnabled'),
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME+'.form')),
      workspace: toolSelectors.contextData(state)
    }),
    (dispatch) => ({
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME+'.form', prop, value))
      },
      updateReceivers(roles) {
        dispatch(listActions.addFilter(selectors.STORE_NAME+'.receivers', 'roles', roles.map(role => role.id), true))
        dispatch(listActions.invalidateData(selectors.STORE_NAME+'.receivers'))
      }
    })
  )(SendingModalComponent)
)

export {
  SendingModal
}
