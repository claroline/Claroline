import cloneDeep from 'lodash/cloneDeep'

import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeReducer} from '#/main/core/scaffolding/reducer'

import {UPDATE_DELETED_TABS} from '#/main/core/tools/home/editor/actions'

const reducer = makeFormReducer('editor', {
  data: makeReducer([] ,{
    [UPDATE_DELETED_TABS]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState.findIndex(c => c.id === action.tabId)

      if (index > -1) {
        newState.splice(index, 1)
      }

      return newState
    }
  })
})

export {
  reducer
}
