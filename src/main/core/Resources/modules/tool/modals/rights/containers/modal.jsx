import {connect} from 'react-redux'
import get from 'lodash/get'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

import {RightsModal as RightsModalComponent} from '#/main/core/tool/modals/rights/components/modal'
import {actions, reducer, selectors} from '#/main/core/tool/modals/rights/store'

const RightsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME)),
      rights: formSelect.data(formSelect.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadRights(toolName, context) {
        dispatch(actions.fetchRights(toolName, context))
      },
      updateRights(perms) {
        dispatch(formActions.update(selectors.STORE_NAME, perms))
      },
      save(toolName, context) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, ['apiv2_tool_update_rights', {
          name: toolName,
          context: context.type,
          contextId: get(context, 'data.id', null)
        }]))
      }
    })
  )(RightsModalComponent)
)

export {
  RightsModal
}
