import {actions as formActions} from '#/main/core/data/form/actions'

export const actions = {}

actions.open = (formName, params = {}) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {action: params.entity + '_' + params.action}, true))
}
