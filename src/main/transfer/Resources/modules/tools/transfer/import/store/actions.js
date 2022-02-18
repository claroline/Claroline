import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

import {actions as logActions} from '#/main/transfer/tools/transfer/log/store'
import {actions as formActions} from '#/main/app/content/form/store'

export const IMPORT_FILE_LOAD = 'IMPORT_FILE_LOAD'

export const actions = {}

actions.load = makeActionCreator(IMPORT_FILE_LOAD, 'file')

actions.open = (formName, params = {format: 'csv'}) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {
    action: params.entity + '_' + params.action,
    format: params.format,
    workspace: params.workspace
  }, true))
}

actions.fetch = (importFileId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_transfer_import_get', {id: importFileId}],
    silent: true,
    before: () => dispatch(actions.load(null)),
    success: (response) => {
      dispatch(actions.load(response))
      dispatch(logActions.load(response.id))
    }
  }
})

actions.execute = (importFileId) => ({
  [API_REQUEST]: {
    url: ['apiv2_transfer_import_execute', {id: importFileId}],
    request: {
      method: 'POST'
    }
  }
})
