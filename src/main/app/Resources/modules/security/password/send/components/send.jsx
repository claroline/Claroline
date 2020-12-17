import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/app/security/password/send/store/selectors'

const SendPasswordForm = (props) =>
  <div className="login-container">
    <div className="authentication-column account-authentication-column">
      <p className="authentication-help">{trans('send_password_help')}</p>

      <FormData
        name={selectors.FORM_NAME}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'email',
                label: trans('email'),
                placeholder: trans('email'),
                type: 'email',
                required: true
              }
            ]
          }
        ]}
      >
        <Button
          className="btn btn-block btn-emphasis"
          type={CALLBACK_BUTTON}
          label={trans('send_password')}
          callback={() => props.reset(props.form.data.email, () => {
            props.history.push('/login')
          })}
          primary={true}
        />

        <Button
          className="btn btn-block"
          type={LINK_BUTTON}
          label={trans('login', {}, 'actions')}
          target="/login"
        />
      </FormData>
    </div>
  </div>

SendPasswordForm.propTypes = {
  reset: T.func.isRequired,
  form: T.object,
  history: T.object
}

export {
  SendPasswordForm
}
