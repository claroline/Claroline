import {makeReducer} from '#/main/core/scaffolding/reducer'

import {reducer as resourceReducer} from '#/plugin/reservation/administration/resource/reducer'
import {reducer as resourceTypeReducer} from '#/plugin/reservation/administration/resource-type/reducer'

const reducer = {
  isAdmin: makeReducer({}, {}),
  resources: resourceReducer.resources,
  resourceForm: resourceReducer.resourceForm,
  organizationsPicker: resourceReducer.organizationsPicker,
  rolesPicker: resourceReducer.rolesPicker,
  resourceTypes: resourceTypeReducer.resourceTypes
}

export {
  reducer
}
