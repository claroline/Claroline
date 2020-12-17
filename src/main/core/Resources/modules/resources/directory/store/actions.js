import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'
import {selectors} from '#/main/core/resources/directory/store/selectors'

// action names
export const DIRECTORIES_LOAD = 'DIRECTORIES_LOAD'
export const DIRECTORY_TOGGLE_OPEN = 'DIRECTORY_TOGGLE_OPEN'

// action creators
export const actions = {}

actions.loadDirectories = makeActionCreator(DIRECTORIES_LOAD, 'parentId', 'directories')
actions.fetchDirectories = (parentId = null) => (dispatch, getState) => {
  const currentId = resourceSelectors.id(getState())

  return dispatch({
    [API_REQUEST]: {
      silent: true,
      url: url(['apiv2_resource_list', {parent: parentId}], {
        filters: {
          resourceType: 'directory'
        },
        sortBy: 'name',
        // todo: lazy load instead
        limit: 20
      }),
      success: (response, dispatch) => dispatch(actions.loadDirectories(currentId !== parentId ? parentId : null, response.data || []))
    }
  })
}

actions.setDirectoryOpen = makeActionCreator(DIRECTORY_TOGGLE_OPEN, 'directoryId', 'opened')

actions.toggleDirectoryOpen = (directoryId, opened) => (dispatch, getState) => {
  if (opened) {
    // get the current directory to know if we need to load its children or not for summary
    const directory = selectors.directory(selectors.directories(getState()), directoryId)
    if (directory && !directory._loaded)  {
      dispatch(actions.fetchDirectories(directoryId))
    }
  }

  dispatch(actions.setDirectoryOpen(directoryId, opened))
}
