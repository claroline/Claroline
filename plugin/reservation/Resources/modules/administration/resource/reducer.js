import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/core/scaffolding/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

import {RESOURCE_RIGHTS_ADD, RESOURCE_RIGHTS_UPDATE} from '#/plugin/reservation/administration/resource/actions'

const defaultReducer = makeReducer(null, {})

const resourceRightsReducer = makeReducer([], {
  [RESOURCE_RIGHTS_ADD]: (state, action) => {
    const resourceRights = cloneDeep(state)
    resourceRights.push(action.resourceRights)

    return resourceRights
  },
  [RESOURCE_RIGHTS_UPDATE]: (state, action) => {
    const resourceRights = cloneDeep(state)
    const index = resourceRights.findIndex(rr => rr.id === action.id)

    if (index > -1) {
      resourceRights[index]['mask'] =  action.value
    }

    return resourceRights
  }
})

const reducer = {
  resources: makeListReducer('resources', {}, {}),
  resourceForm: makeFormReducer('resourceForm', {}, {
    // data: combineReducers({
    //   resourceRights: resourceRightsReducer
    // }),
    data: combineReducers({
      id: defaultReducer,
      name: defaultReducer,
      resourceType: defaultReducer,
      maxTimeReservation: defaultReducer,
      description: defaultReducer,
      localization: defaultReducer,
      quantity: defaultReducer,
      color: defaultReducer,
      resourceRights: resourceRightsReducer
    }),
    organizations: makeListReducer('resourceForm.organizations')
  }),
  organizationsPicker: makeListReducer('organizationsPicker'),
  rolesPicker: makeListReducer('rolesPicker')
}

export {
  reducer
}
