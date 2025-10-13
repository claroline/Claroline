import React from 'react'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Badge} from '#/main/app/components/badge'

import {FormGroup} from '#/main/app/content/form/components/group'
import {FormError} from '#/main/app/content/form/components/error'
import {FormHelp} from '#/main/app/content/form/components/help'
import {getValidationClassName} from '#/main/app/content/form/validator'

/**
 * Overrides the default form group to let each range part render its own errors
 */
const DateRangeGroup = props => {
  const error = typeof props.error === 'string' ? props.error : undefined

  return (
    <fieldset className={classes('data-range-group form-group', props.className, getValidationClassName(props.error))}>
      {props.label &&
        <legend
          className={classes('form-label d-flex align-items-baseline gap-2', {
            'visually-hidden': props.hideLabel
          })}
        >
          {props.icon &&
            <span className={classes('', props.icon)} aria-hidden={true} />
          }

          {props.label}

          {!props.required &&
            <Badge variant={props.recommended ? 'primary' : 'secondary'} subtle={true} className="fw-normal text-lowercase fs-sm">
              {props.recommended ? trans('recommended') : trans('optional')}
            </Badge>
          }
        </legend>
      }

      {!isEmpty(props.help) &&
        <FormHelp help={props.help} className={classes('mb-2', {'mt-n1': !!props.label && !props.hideLabel})} />
      }

      {props.children}

      {!isEmpty(error) &&
        <FormError error={error} />
      }
    </fieldset>
  )
}

DateRangeGroup.propTypes = FormGroup.propTypes

export {
  DateRangeGroup
}
