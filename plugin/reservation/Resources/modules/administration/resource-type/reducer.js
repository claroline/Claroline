import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/core/scaffolding/reducer'

import {
  RESOURCE_TYPE_ADD,
  RESOURCE_TYPE_UPDATE,
  RESOURCE_TYPE_REMOVE
} from '#/plugin/reservation/administration/resource-type/actions'

const reducer = {
  resourceTypes: makeReducer({}, {
    [RESOURCE_TYPE_ADD]: (state, action) => {
      const resourceTypes = cloneDeep(state)
      resourceTypes.push(action.resourceType)

      return resourceTypes
    },
    [RESOURCE_TYPE_UPDATE]: (state, action) => {
      const resourceTypes = cloneDeep(state)
      const index = resourceTypes.findIndex(rt => rt.id === action.resourceType.id)

      if (index > -1) {
        resourceTypes[index] =  action.resourceType
      }

      return resourceTypes
    },
    [RESOURCE_TYPE_REMOVE]: (state, action) => {
      const resourceTypes = cloneDeep(state)
      const index = resourceTypes.findIndex(rt => rt.id === action.id)

      if (index > -1) {
        resourceTypes.splice(index, 1)
      }

      return resourceTypes
    }
  })
}

export {
  reducer
}
