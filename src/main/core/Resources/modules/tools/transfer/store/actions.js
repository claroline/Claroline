import {actions as formActions} from '#/main/app/content/form/store/actions'

export const actions = {}

actions.open = (formName, params = {format: 'csv'}) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {action: params.entity + '_' + params.action, format: params.format}, true))
}
