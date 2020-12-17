import React from 'react'

import {FormGroup} from '#/main/app/content/form/components/group'

/**
 * Overrides default form group because in this case
 * the label is added on the checkbox. So we don't need it twice.
 */
const BooleanGroup = props =>
  <FormGroup
    id={props.id}
    className={props.className}
    help={props.help}
    error={props.error}
    warnOnly={props.warnOnly}
  >
    {props.children}
  </FormGroup>

BooleanGroup.propTypes = FormGroup.propTypes

export {
  BooleanGroup
}
