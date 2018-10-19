import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'

/**
 * Renders the available form actions.
 *
 * @param props
 * @constructor
 */
const FormActions = props =>
  <div className={classes('form-toolbar', props.className)}>
    <Button
      icon="fa fa-fw fa-save"
      label={trans('save', {}, 'actions')}
      {...props.save}
      className="btn btn-save"
      tooltip="top"
      primary={true}
    />

    {props.cancel &&
      <Button
        {...props.cancel}
        className="btn btn-exit"
        tooltip="top"
        icon="fa fa-fw fa-sign-out-alt"
        label={trans('exit', {}, 'actions')}
      />
    }
  </div>

FormActions.propTypes = {
  className: T.string,
  save: T.shape({
    type: T.string.isRequired
    // todo find a way to document custom action type props
  }).isRequired,
  cancel: T.shape({
    type: T.string.isRequired
    // todo find a way to document custom action type props
  })
}

export {
  FormActions
}