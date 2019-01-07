import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {Scale as ScaleType} from '#/plugin/competency/administration/competency/prop-types'
import {ScaleInput} from '#/plugin/competency/data/types/scale/components/input'

const ScaleGroup = props => {
  return(<FormGroup {...props}>
    <ScaleInput {...props} />
  </FormGroup>)
}

implementPropTypes(ScaleGroup, FormGroupWithFieldTypes, {
  value: T.shape(ScaleType.propTypes)
})

export {
  ScaleGroup
}
