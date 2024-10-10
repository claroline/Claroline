import React from 'react'
import classes from 'classnames'

import {FormGroup} from '#/main/app/content/form/components/group'
import isEmpty from 'lodash/isEmpty'
import {FormError} from '#/main/app/content/form/components/error'
import {FormHelp} from '#/main/app/content/form/components/help'
import {trans} from '#/main/app/intl'
import {getValidationClassName} from '#/main/app/content/form/validator'

/**
 * Overrides default form group to let each range part render its own errors
 */
const DateRangeGroup = props => {
  const error = typeof props.error === 'string' ? props.error : undefined

  return (
    <fieldset className={classes('data-range-group form-group mb-4', props.className, getValidationClassName(props.error, props.validating))}>
      {props.label &&
        <legend
          className={classes('form-label', {
            'visually-hidden': props.hideLabel
          })}
        >
          {props.label}

          {props.optional &&
            <small className="ms-2 text-secondary fw-normal text-lowercase">({trans('optional')})</small>
          }
        </legend>
      }

      {props.children}

      {!isEmpty(error) &&
        <FormError error={error} warnOnly={!props.validating} />
      }

      {!isEmpty(props.help) &&
        <FormHelp help={props.help} />
      }
    </fieldset>
  )
}

DateRangeGroup.propTypes = FormGroup.propTypes

export {
  DateRangeGroup
}
