import React from 'react'

import {FormGroup} from '#/main/app/content/form/components/group'

/**
 * Overrides default form group to let each input in the
 * address display its own errors.
 */
const AddressGroup = props =>
  <FormGroup
    {...props}
    error={typeof props.error === 'string' ? props.error : undefined}
  >
    {props.children}
  </FormGroup>

AddressGroup.propTypes = FormGroup.propTypes

export {
  AddressGroup
}
