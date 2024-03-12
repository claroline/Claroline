import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const FormSave = (props) => {
  if (props.pendingChanges) {
    const saveAction = props.save ? merge({}, props.save, {
      disabled: props.disabled || props.save.disabled || !(props.pendingChanges && (!props.validating || !props.errors))
    }) : undefined

    return (
      <div className="form-pending-changes sticky-bottom d-flex align-items-center mb-3 py-2 px-3 mx-n3 gap-1">
        <span className="flex-fill">
          Attention, il reste des modifications non enregistrées !
        </span>

        <Button
          {...props.cancel}
          className="btn btn-link"
          label={trans('Réinitialiser', {}, 'actions')}
          type={CALLBACK_BUTTON}
          size="sm"
        />

        <Button
          {...saveAction}
          className="form-btn-save btn btn-primary btn-wave"
          label={trans('Enregistrer les modifications', {}, 'actions')}
          size="sm"
          htmlType="submit"
        />
      </div>
    )
  }

  return null
}

FormSave.propTypes = {
  errors: T.bool,
  validating: T.bool,
  pendingChanges: T.bool.isRequired,
  //cancel: T.func.isRequired
}

FormSave.defaultProps = {
  validating: false,
  errors: false
}

export {
  FormSave
}
