import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans, transChoice} from '#/main/core/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {currentUser} from '#/main/core/user/current'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {actions} from '#/plugin/message/actions'

const authenticatedUser = currentUser()

const MessagesParametersComponent = (props) =>
  <div>
    <h2>{trans('preferences')}</h2>
    <FormData
      level={3}
      displayLevel={2}
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        callback: () => props.setMailNotification(authenticatedUser, props.messagesParameters.mailNotified)
      }}
      name="messagesParameters"
      className="content-container"
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'mailNotified',
              type: 'boolean',
              label: transChoice('get_mail_notifications', authenticatedUser.email, {adress: authenticatedUser.email}),
              required: true
            }
          ]
        }
      ]}
    />
  </div>

MessagesParametersComponent.propTypes = {
  setMailNotification: T.func.isRequired,
  messagesParameters: T.shape({
    mailNotified: T.bool
  })
}

const MessagesParameters = connect(
  state => ({
    messagesParameters: formSelectors.data(formSelectors.form(state, 'messagesParameters'))
  }),
  dispatch => ({
    setMailNotification(user, mailNotified) {
      dispatch(actions.setMailNotification(user, mailNotified))
    }
  })
)(MessagesParametersComponent)

export {
  MessagesParameters
}
