import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import omit from 'lodash/omit'

import CloseButton from 'react-bootstrap/CloseButton'
import BaseModal from 'react-bootstrap/Modal'

import {Router, Routes} from '#/main/app/router'
import {trans} from '#/main/app/intl'
import {selectors} from '#/main/app/platform/store'
import {selectors as configSelectors} from '#/main/app/config/store'
import {LoginMain} from '#/main/app/security/login/containers/main'
import {RegistrationMain} from '#/main/app/security/registration/containers/main'
import {SendPasswordForm} from '#/main/app/security/password/send/containers/send'
import {SecurityLayout} from '#/main/app/security/components/layout'

const SecurityModal = props => {
  const [aborted, setAborted] = useState(true)

  const name = useSelector((state) => configSelectors.param(state, 'name'))

  const selfRegistration = useSelector(selectors.selfRegistration)
  const changePassword = useSelector((state) => configSelectors.param(state, 'authentication.login.changePassword'))

  return (
    <BaseModal
      {...omit(props, 'page', 'onRegister', 'onLogin', 'onAbort', 'hideModal', 'fadeModal')}
      autoFocus={true}
      enforceFocus={true}
      centered={true}
      onHide={props.fadeModal}
      onExited={() => {
        if (props.onAbort && aborted) {
          props.onAbort()
        }

        props.hideModal()
      }}
      size="xl"
    >
      <div className="modal-body d-flex flex-column flex-lg-row p-0 position-relative vh-lg-90">
        <CloseButton className="position-absolute top-0 end-0 m-4" onClick={props.fadeModal} aria-label={trans('close', {}, 'actions')} />
        <SecurityLayout className="rounded-start-3">
          <Router embedded={true}>
            <Routes
              redirect={[
                {from: '/', exact: true, to: props.page || '/login'}
              ]}
              routes={[
                {
                  path: '/login',
                  render: () =>
                    <div className="content-md px-4 py-5 my-auto" role="presentation">
                      <h2 className="text-center">{trans('login')}</h2>
                      <p className="lead text-center text-body-secondary mb-5">{trans('login_auth_account', {platform: name})}</p>
                      <LoginMain
                        onLogin={(response) => {
                          setAborted(false)

                          if (props.onLogin) {
                            props.onLogin(response)
                          }

                          props.fadeModal()
                        }}
                      />
                    </div>
                }, {
                  path: '/registration',
                  disabled: !selfRegistration,
                  render: () =>
                    <div className="content-md px-4 py-5 my-auto" role="presentation">
                      <h2 className="text-center">{trans('registration')}</h2>
                      <p className="lead text-center text-body-secondary mb-5">{trans('registration_help', {platform: name})}</p>

                      <RegistrationMain
                        path="/"
                        onRegister={(user) => {
                          if (props.onRegister) {
                            props.onRegister(user)
                          }

                          props.fadeModal()
                        }}
                      />
                    </div>
                }, {
                  path: '/reset_password',
                  disabled: !changePassword,
                  render: () =>
                    <div className="content-md px-4 py-5 my-auto" role="presentation">
                      <h2 className="text-center">{trans('forgot_password')}</h2>
                      <p className="lead text-center text-body-secondary mb-5">{trans('send_password_help')}</p>

                      <SendPasswordForm />
                    </div>
                }
              ]}
            />
          </Router>
        </SecurityLayout>
      </div>
    </BaseModal>
  )
}

SecurityModal.propTypes = {
  page: T.oneOf(['login', 'registration', 'reset_password']),
  onLogin: T.func,
  onRegister: T.func,
  onAbort: T.func,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

export {
  SecurityModal
}
