import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const FormSave = (props) => {
  if (props.pendingChanges) {
    const saveAction = props.save ? merge({}, props.save, {
      disabled: props.disabled || props.save.disabled || !props.pendingChanges || !isEmpty(props.errors)
    }) : undefined

    return (
      <div className="form-pending-changes sticky-bottom d-flex align-items-center mt-auto mb-3 py-2 px-3 gap-1">
        <span className="flex-fill">
          Attention, il reste des modifications non enregistrées !
        </span>

        <Button
          {...saveAction}
          className="btn btn-link"
          label={trans('Enregistrer', {}, 'actions')}
          type={CALLBACK_BUTTON}
          size="sm"
          data-bs-theme="dark"
        />

        <Button
          {...saveAction}
          className="form-btn-save btn btn-primary btn-wave"
          label={trans('Enregistrer & Quitter', {}, 'actions')}
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
  save: T.shape({

  })
}

FormSave.defaultProps = {
  validating: false,
  errors: false
}

export {
  FormSave
}
