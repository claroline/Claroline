import {connect} from 'react-redux'
import get from 'lodash/get'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/main/core/tool/modals/parameters/components/modal'
import {reducer, selectors} from '#/main/core/tool/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      reset(data) {
        dispatch(formActions.reset(selectors.STORE_NAME, data, false))
      },

      save(toolName, context, onSave = () => true) {
        dispatch(formActions.save(selectors.STORE_NAME, ['apiv2_tool_configure', {
          name: toolName,
          context: context.type,
          contextId: get(context, 'data.id', null)
        }])).then(response => onSave(response))
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
