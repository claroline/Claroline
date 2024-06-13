import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'

import {DataError} from '#/main/app/data/components/error'
import {DataGroup as DataGroupTypes} from '#/main/app/data/types/prop-types'

import {getValidationClassName} from '#/main/app/content/form/validator'

/**
 * Renders an agnostic form group.
 * It is used to wrap inputs in order to render the associated meta (label, errors, etc.).
 *
 * @param props
 * @constructor
 */
const FormGroup = props =>
  <div className={classes('form-group mb-4', props.className, getValidationClassName(props.error, props.validating))}>
    {props.label &&
      <label
        className={classes('form-label', {
          'sr-only': props.hideLabel
        })}
        htmlFor={props.id}
      >
        {props.label}

        {props.optional &&
          <small className="ms-2 text-secondary fw-normal text-lowercase">({trans('optional')})</small>
        }
      </label>
    }

    {props.children}

    {!isEmpty(props.error) &&
      <DataError error={props.error} warnOnly={!props.validating} />
    }

    {!isEmpty(props.help) &&
      <>
        {Array.isArray(props.help) ?
          props.help.map(help => <div key={toKey(help)} className="form-text">{help}</div>) :
          <div className="form-text">{props.help}</div>
        }
      </>
    }
  </div>

implementPropTypes(FormGroup, DataGroupTypes, {
  children: T.node.isRequired
})

export {
  FormGroup
}
