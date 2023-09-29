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
  <div className={classes('sticky-bottom mt-auto mb-3 btn-toolbar form-toolbar gap-1', props.className)}>
    <Button
      icon="fa fa-fw fa-save"
      label={trans('save', {}, 'actions')}
      {...props.save}
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