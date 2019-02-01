import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Organization as OrganizationType} from '#/main/core/user/prop-types'
import {OrganizationInput} from '#/main/core/data/types/organization/components/input'

const OrganizationGroup = props =>
  <FormGroup {...props}>
    <OrganizationInput {...props} />
  </FormGroup>

implementPropTypes(OrganizationGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape(OrganizationType.propTypes))
})

export {
  OrganizationGroup
}
