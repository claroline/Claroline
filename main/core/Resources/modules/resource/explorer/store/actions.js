import {API_REQUEST, url} from '#/main/app/api'
import {makeInstanceActionCreator} from '#/main/app/store/actions'

// actions
export const EXPLORER_INITIALIZE = 'EXPLORER_INITIALIZE'
export const DIRECTORY_CHANGE = 'DIRECTORY_CHANGE'
export const DIRECTORY_TOGGLE_OPEN = 'DIRECTORY_TOGGLE_OPEN'
export const DIRECTORIES_LOAD = 'DIRECTORIES_LOAD'

// actions creators
export const actions = {}

actions.initialize = (explorerName, root = null, current = null) => ({
  type: EXPLORER_INITIALIZE+'/'+explorerName,
  root: root,
  current: current || root
})

actions.changeDirectory = makeInstanceActionCreator(DIRECTORY_CHANGE, 'directory')
actions.setDirectoryOpen = makeInstanceActionCreator(DIRECTORY_TOGGLE_OPEN, 'directory', 'opened')

actions.loadDirectories = makeInstanceActionCreator(DIRECTORIES_LOAD, 'parent', 'directories')
actions.fetchDirectories = (explorerName, parent = null) => ({
  [API_REQUEST]: {
    url: url(['apiv2_resource_list', {parent: parent ? parent.id : null}], {
      filters: {
        resourceType: 'directory'
      },
      sortBy: '-name'
    }),
    success: (response, dispatch) => dispatch(actions.loadDirectories(explorerName, parent, response.data || []))
  }
})

actions.toggleDirectoryOpen = (explorerName, directory, opened) => (dispatch) => {
  if (opened && !directory._loaded)  {
    dispatch(actions.fetchDirectories(explorerName, directory))
  }

  dispatch(actions.setDirectoryOpen(explorerName, directory, opened))
}
