import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {IpInput} from '#/main/app/data/ip/components/input'

const IpGroup = props =>
  <FormGroup {...props}>
    <IpInput {...props} />
  </FormGroup>

implementPropTypes(IpGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  placeholder: T.string,
  size: T.string
}, {
  value: '',
  placeholder: '127.0.0.1'
})

export {
  IpGroup
}
