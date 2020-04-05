import React from 'react'

import {FormData} from '#/main/app/content/form/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/app/security/password/reset/store/selectors'
import {Button} from '#/main/app/action/components/button'
import {PropTypes as T} from 'prop-types'

const ResetPasswordForm = (props) => {
  return (
    <div className="login-container">
      <div className="authentication-column account-authentication-column">
        <FormData
          name={selectors.FORM_NAME}
          sections={[
            {
              title: trans('general'),
              primary: true,
              fields: [
                {
                  name: 'password',
                  label: trans('password'),
                  placeholder: trans('password'),
                  hideLabel: false,
                  type: 'password',
                  required: true
                },
                {
                  name: 'confirm',
                  label: trans('confirm'),
                  placeholder: trans('confirm'),
                  hideLabel: false,
                  type: 'password',
                  required: true
                }
              ]
            }
          ]}
        >
          <Button
            className="btn btn-block btn-emphasis"
            type={CALLBACK_BUTTON}
            label={trans('reset_password')}
            callback={() => props.reset({
              password: props.form.data.password, confirm: props.form.data.confirm, hash: props.match.params.hash
            }, () => {
              props.history.push('/login')
            })}
            primary={true}
          />
        </FormData>
      </div>
    </div>
  )
}



ResetPasswordForm.propTypes = {
  reset: T.func.isRequired,
  form: T.object,
  history: T.object,
  match: T.object
}

export {
  ResetPasswordForm
}
