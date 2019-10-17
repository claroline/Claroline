import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'

import {ContentHelp} from '#/main/app/content/components/help'
import {DataError} from '#/main/app/data/components/error'

import {FormGroup as FormGroupTypes} from '#/main/core/layout/form/prop-types'

// TODO : move in Data module and rename (this is not only used in form)

/**
 * Renders an agnostic form group.
 * It is used to wrap inputs in order to render the associated meta (label, errors, etc.).
 *
 * @param props
 * @constructor
 */
const FormGroup = props =>
  <div className={classes('form-group', props.className, {
    'has-error': props.error && !props.warnOnly,
    'has-warning': props.error && props.warnOnly
  })}>
    {props.label &&
      <label
        className={classes('control-label', {
          'sr-only': props.hideLabel
        })}
        htmlFor={props.id}
      >
        {props.label}

        {props.optional &&
          <small>({trans('optional')})</small>
        }
      </label>
    }

    {props.children}

    {!isEmpty(props.error) &&
      <DataError error={props.error} warnOnly={props.warnOnly} />
    }

    {!isEmpty(props.help) &&
      <ContentHelp help={props.help} />
    }
  </div>

implementPropTypes(FormGroup, FormGroupTypes, {
  children: T.node.isRequired
})

export {
  FormGroup
}
