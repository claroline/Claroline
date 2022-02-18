import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {actions as formActions} from '#/main/app/content/form/store'

export const EXPORT_FILE_LOAD = 'EXPORT_FILE_LOAD'

export const actions = {}

actions.load = makeActionCreator(EXPORT_FILE_LOAD, 'file')

actions.open = (formName, params = {format: 'csv'}) => (dispatch) => {
  // I do an update (not a reset) because action is the only prop for now
  // and we cannot save the form as there will be no change detected
  dispatch(formActions.update(formName, {
    action: params.entity + '_' + params.action,
    format: params.format,
    workspace: params.workspace
  }))
}

actions.fetch = (exportFileId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_transfer_export_get', {id: exportFileId}],
    silent: true,
    before: () => dispatch(actions.load(null)),
    success: (response) => dispatch(actions.load(response))
  }
})

actions.execute = (exportFileId) => ({
  [API_REQUEST]: {
    url: ['apiv2_transfer_export_execute', {id: exportFileId}],
    request: {
      method: 'POST'
    }
  }
})
