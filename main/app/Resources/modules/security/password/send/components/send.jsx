import React from 'react'

import {FormData} from '#/main/app/content/form/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/app/security/password/send/store/selectors'
import {Button} from '#/main/app/action/components/button'
import {PropTypes as T} from 'prop-types'

const SendPasswordForm = (props) => {
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
              props.history.push('/')
            })}
            primary={true}
          />
        </FormData>
      </div>
    </div>
  )
}



SendPasswordForm.propTypes = {
  reset: T.func.isRequired,
  form: T.object,
  history: T.object
}

export {
  SendPasswordForm
}
