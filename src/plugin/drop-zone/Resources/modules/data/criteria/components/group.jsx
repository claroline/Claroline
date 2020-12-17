import React from 'react'

import {FormGroup} from '#/main/app/content/form/components/group'

/**
 * Overrides default form group to let each item in the
 * collection display its own errors.
 */
const CriteriaGroup = props =>
  <FormGroup
    {...props}
    error={typeof props.error === 'string' ? props.error : undefined}
  >
    {props.children}
  </FormGroup>

CriteriaGroup.propTypes = FormGroup.propTypes

export {
  CriteriaGroup
}
