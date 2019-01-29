import React from 'react'

import {FormGroup} from '#/main/app/content/form/components/group'

/**
 * Overrides default form group to let each item in the
 * collection display its own errors.
 */
const CollectionGroup = props =>
  <FormGroup
    {...props}
    error={typeof props.error === 'string' ? props.error : undefined}
  >
    {props.children}
  </FormGroup>

CollectionGroup.propTypes = FormGroup.propTypes

export {
  CollectionGroup
}
