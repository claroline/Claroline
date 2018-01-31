import {registerType} from '#/main/core/data'

import {CRITERIA_TYPE, criteriaDefinition} from '#/plugin/drop-zone/data/types/criteria'

function registerDropzoneTypes() {
  registerType(CRITERIA_TYPE,  criteriaDefinition)
}

export {
  registerDropzoneTypes
}
