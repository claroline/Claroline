import get from 'lodash/get'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {constants as listConst} from '#/main/app/content/list/constants'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as editorSelectors} from '#/main/core/resources/directory/editor/store/selectors'
import {reducer as editorReducer} from '#/main/core/resources/directory/editor/store/reducer'

import {selectors as playerSelectors} from '#/main/core/resources/directory/player/store/selectors'

const reducer = combineReducers({
  directoryForm: editorReducer.directoryForm,
  directory: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => action.resourceData.directory,
    // replaces directory data after success updates
    [makeInstanceAction(FORM_SUBMIT_SUCCESS, editorSelectors.FORM_NAME)]: (state, action) => action.updatedData
  }),

  // TODO : move in player
  resources: makeListReducer(playerSelectors.LIST_NAME, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: () => true
    }),
    selected: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: () => []
    }),
    filters: makeReducer([], {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => get(action.resourceData.directory, 'list.filters') || []
    }),
    page: makeReducer([], {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: () => 0
    }),
    pageSize: makeReducer([], {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => get(action.resourceData.directory, 'list.pageSize') || listConst.DEFAULT_PAGE_SIZE
    }),
    sortBy: makeReducer([], {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => {
        const sorting = get(action.resourceData.directory, 'list.sorting')

        let sortBy = {property: null, direction: 0}
        if (sorting) {
          if (0 === sorting.indexOf('-')) {
            sortBy.property = sorting.replace('-', '') // replace first -
            sortBy.direction = -1
          } else {
            sortBy.property = sorting
            sortBy.direction = 1
          }
        }

        return sortBy
      }
    })
  })
})

export {
  reducer
}