import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'

import {Ability as AbilityType} from '#/plugin/competency/administration/competency/prop-types'
import {AbilityInput} from '#/plugin/competency/data/types/ability/components/input'

const AbilityGroup = props => {
  return(<FormGroup {...props}>
    <AbilityInput {...props} />
  </FormGroup>)
}

implementPropTypes(AbilityGroup, FormGroupWithFieldTypes, {
  value: T.shape(AbilityType.propTypes)
})

export {
  AbilityGroup
}
