import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {FormStatus} from '#/main/app/content/form/components/status'

/**
 * Renders the available form actions.
 */
const FormActions = props => {
  const saveAction = props.save ? merge({}, props.save, {
    disabled: props.disabled || props.save.disabled || !(props.pendingChanges && (!props.validating || !props.errors))
  }) : undefined

  return (
    <div className={classes('sticky-bottom ms-auto mt-auto mb-3 btn-toolbar form-toolbar gap-1', props.className)}>
      {props.errors &&
        <span className="badge position-absolute top-0 start-0 translate-middle p-0">
          <FormStatus className="fs-4" validating={props.validating} id="form-errors-tip" tooltip="left" />
        </span>
      }

      <Button
        icon="fa fa-fw fa-save"
        label={trans('save', {}, 'actions')}
        {...saveAction}
        variant="btn"
        className="form-btn-save rounded-pill"
        tooltip="top"
        primary={true}
        htmlType="submit"
      />

      {props.cancel &&
        <Button
          {...props.cancel}
          className="form-btn-cancel rounded-pill"
          tooltip="top"
          icon="fa fa-fw fa-times"
          label={trans('exit', {}, 'actions')}
          variant="btn"
        />
      }
    </div>
  )
}

FormActions.propTypes = {
  className: T.string,
  disabled: T.bool,
  errors: T.bool,
  validating: T.bool,
  pendingChanges: T.bool,
  save: T.shape(
    ActionTypes.propTypes
  ).isRequired,
  cancel: T.shape(
    ActionTypes.propTypes
  )
}

FormActions.defaultProps = {
  validating: false,
  disabled: false,
  errors: false
}

export {
  FormActions
}
