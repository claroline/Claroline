import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeReducer} from '#/main/core/scaffolding/reducer'
import cloneDeep from 'lodash/cloneDeep'
import {UPDATE_RESOURCE} from '#/main/core/workspace/parameters/actions'

const reducer = makeFormReducer('parameters', {}, {
  pendingChanges: makeReducer(false, {
    [UPDATE_RESOURCE]: () => true
  }),
  data: makeReducer({}, {
    [UPDATE_RESOURCE]: (state, action) => {
      const newState = cloneDeep(state)
      newState.options.workspace_opening_resource = action.resource.autoId
      newState.options.opened_resource = action.resource

      return newState
    }
  })
})

export {
  reducer
}
