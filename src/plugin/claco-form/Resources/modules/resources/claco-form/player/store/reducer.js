import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {constants as paginationConst} from '#/main/app/content/pagination/constants'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store/selectors'
import {
  ENTRIES_UPDATE,
  ENTRY_CREATED,
  CURRENT_ENTRY_LOAD,
  ENTRY_COMMENT_ADD,
  ENTRY_COMMENT_UPDATE,
  ENTRY_COMMENT_REMOVE,
  ENTRY_USER_UPDATE,
  ENTRY_USER_UPDATE_PROP,
  ENTRY_USER_RESET,
  ENTRY_CATEGORY_ADD,
  ENTRY_CATEGORY_REMOVE,
  ENTRY_KEYWORD_ADD,
  ENTRY_KEYWORD_REMOVE,
  USED_COUNTRIES_LOAD
} from '#/plugin/claco-form/resources/claco-form/player/store/actions'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME+'.entries.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.entries.current']: () => true,
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true,
      [ENTRIES_UPDATE]: () => true
    }),
    filters: makeReducer([], {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => get(action.resourceData, 'clacoForm.list.filters', [])
    }),
    pagination: makeReducer({page: 0, pageSize: paginationConst.DEFAULT_PAGE_SIZE}, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => ({
        page: state.page,
        pageSize: get(action.resourceData, 'clacoForm.list.pageSize') || state.pageSize
      }),
      [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.clacoFormForm']: (state, action) => ({
        page: state.page,
        pageSize: get(action.updatedData, 'list.pageSize') || state.pageSize
      })
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME+'.entries.current', {}, {
    data: makeReducer({}, {
      [CURRENT_ENTRY_LOAD]: (state, action) => {
        return action.entry
      },
      [ENTRY_CATEGORY_ADD]: (state, action) => {
        const newState = cloneDeep(state)
        const category = newState['categories'].find(c => c.id === action.category.id)

        if (!category) {
          newState['categories'].push(action.category)
        }

        return newState
      },
      [ENTRY_CATEGORY_REMOVE]: (state, action) => {
        const newState = cloneDeep(state)
        const index = newState['categories'].findIndex(c => c.id === action.categoryId)

        if (index > -1) {
          newState['categories'].splice(index, 1)
        }

        return newState
      },
      [ENTRY_KEYWORD_ADD]: (state, action) => {
        const newState = cloneDeep(state)
        const keyword = newState['keywords'].find(k => k.name.toUpperCase() === action.keyword.name.toUpperCase())

        if (!keyword) {
          newState['keywords'].push(action.keyword)
        }

        return newState
      },
      [ENTRY_KEYWORD_REMOVE]: (state, action) => {
        const newState = cloneDeep(state)
        const index = newState['keywords'].findIndex(k => k.id === action.keywordId)

        if (index > -1) {
          newState['keywords'].splice(index, 1)
        }

        return newState
      },
      [ENTRY_COMMENT_ADD]: (state, action) => {
        const newState = cloneDeep(state)
        const comment = newState['comments'].find(c => c.id === action.comment.id)

        if (!comment) {
          newState['comments'].unshift(action.comment)
        }

        return newState
      },
      [ENTRY_COMMENT_UPDATE]: (state, action) => {
        const newState = cloneDeep(state)
        const index = newState['comments'].findIndex(c => c.id === action.comment.id)

        if (index > -1) {
          newState['comments'][index] = action.comment
        }

        return newState
      },
      [ENTRY_COMMENT_REMOVE]: (state, action) => {
        const newState = cloneDeep(state)
        const index = newState['comments'].findIndex(c => c.id === action.commentId)

        if (index > -1) {
          newState['comments'].splice(index, 1)
        }

        return newState
      }
    }),
    pendingChanges: makeReducer(false, {
      [ENTRY_CATEGORY_ADD]: () => true,
      [ENTRY_CATEGORY_REMOVE]: () => true,
      [ENTRY_KEYWORD_ADD]: () => true,
      [ENTRY_KEYWORD_REMOVE]: () => true
    })
  }),
  entryUser: makeReducer({}, {
    [ENTRY_USER_UPDATE]: (state, action) => action.entryUser,
    [ENTRY_USER_RESET]: () => ({}),
    [ENTRY_USER_UPDATE_PROP]: (state, action) => {
      const newEntryUser = cloneDeep(state)
      newEntryUser[action.property] = action.value

      return newEntryUser
    }
  }),
  myEntriesCount: makeReducer(0, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.myEntriesCount || state,
    [ENTRY_CREATED]: (state) => {
      return state + 1
    }
  }),
  countries: makeReducer([], {
    [USED_COUNTRIES_LOAD]: (state, action) => action.countries
  }),
  sharedUsers: makeListReducer(selectors.STORE_NAME+'.entries.sharedUsers', {}, {
    invalidated: makeReducer(false, {
      //[CURRENT_ENTRY_LOAD]: (state, action) => true
    })
  })
})

export {
  reducer
}
