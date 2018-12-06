import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'
import {TemplateType as TemplateTypeType} from '#/main/core/administration/template/prop-types'
import {TemplateTypeInput} from '#/main/core/data/types/template-type/components/input'

const TemplateTypeGroup = props => {
  return(<FormGroup {...props}>
    <TemplateTypeInput {...props} />
  </FormGroup>)
}

implementPropTypes(TemplateTypeGroup, FormGroupWithFieldTypes, {
  value: T.shape(TemplateTypeType.propTypes)
})

export {
  TemplateTypeGroup
}
