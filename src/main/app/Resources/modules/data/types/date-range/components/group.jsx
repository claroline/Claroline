import React from 'react'
import classes from 'classnames'

import {FormGroup} from '#/main/app/content/form/components/group'

/**
 * Overrides default form group to let each range part render its own errors
 */
const DateRangeGroup = props =>
  <FormGroup
    {...props}
    className={classes('data-range-group', props.className)}
    error={typeof props.error === 'string' ? props.error : undefined}
  >
    {props.children}
  </FormGroup>

DateRangeGroup.propTypes = FormGroup.propTypes

export {
  DateRangeGroup
}
