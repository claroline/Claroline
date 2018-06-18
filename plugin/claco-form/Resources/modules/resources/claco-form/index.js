import {registerType} from '#/main/core/data'
import {FIELDS_TYPE, fieldsDefinition} from '#/main/core/data/types/fields'

registerType(FIELDS_TYPE,  fieldsDefinition)

import {ClacoFormResource} from '#/plugin/claco-form/resources/claco-form/components/resource'
import {reducer} from '#/plugin/claco-form/resources/claco-form/reducer'

/**
 * ClacoForm resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: ClacoFormResource,
  store: reducer,
  styles: 'claroline-distribution-plugin-claco-form-resource',
  initialData: initialData => ({
    clacoForm: initialData.clacoForm,
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    },
    canGeneratePdf: initialData.canGeneratePdf === 1,
    entries: {
      myEntriesCount: initialData.myEntriesCount
    },
    cascadeLevelMax: initialData.cascadeLevelMax,
    roles: initialData.roles,
    myRoles: initialData.myRoles
  })
})
