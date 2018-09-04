import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// action names
export const UPLOAD_DESTINATIONS_LOAD = 'UPLOAD_DESTINATIONS_LOAD'

// action creators
export const actions = {}

actions.loadUploadDestinations = makeActionCreator(UPLOAD_DESTINATIONS_LOAD, 'directories')

actions.fetchUploadDestinations = () => ({
  [API_REQUEST]: {
    url: ['claro_tinymce_file_destinations'],
    success: (response, dispatch) => dispatch(actions.loadUploadDestinations(response))
  }
})
