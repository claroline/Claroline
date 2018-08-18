import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {withRouter} from '#/main/app/router'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {currentUser} from '#/main/core/user/current'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/message/selectors'

const NewMessageFormWrapper = (props) =>
  <div className='user-message-container user-message-form-container user-message-left'>
    <UserAvatar picture={props.user.picture} />

    <div className="user-message">
      <div className="user-message-meta">
        <div className="user-message-info">
          {props.user.name}
        </div>
      </div>
      <div className="user-message-content embedded-form-section">
        {props.children}
      </div>
      <Button
        className="btn btn-block btn-save btn-emphasis"
        label={trans('send', {}, 'actions')}
        type={CALLBACK_BUTTON}
        callback={props.callback}
        primary={true}
      />
    </div>
  </div>


NewMessageFormWrapper.propTypes = {
  user: T.shape(UserTypes.propTypes),
  callback: T.func.isRequired,
  children: T.node.isRequired
}

const NewMessageComponent = (props) =>
  <div>
    {!props.reply &&
      <h2>{trans('new_message')}</h2>
    }
    <NewMessageFormWrapper
      user={currentUser()}
      callback={() =>  props.saveForm(props.history.push)}
    >
      <FormData
        level={3}
        displayLevel={2}
        name="messageForm"
        className="content-container"
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'to',
                type: 'username',
                label: trans('message_form_to'),
                required: true
              },
              {
                name: 'object',
                type: 'string',
                label: trans('message_form_object')
              },
              {
                name: 'content',
                type: 'html',
                label: trans('message_form_content'),
                required: true
              }
            ]
          }
        ]}
      />
    </NewMessageFormWrapper>
  </div>

NewMessageComponent.propTypes = {
  deleteMessages: T.func.isRequired,
  restoreMessages: T.func.isRequired,
  saveForm: T.func.isRequired,
  reply: T.bool.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

const NewMessage = withRouter(connect(
  state => ({
    reply: selectors.reply(state)
  }),
  (dispatch) => ({
    saveForm(push) {
      dispatch(formActions.saveForm('messageForm', ['apiv2_message_create'])).then(() => push('/received'))
    }
  })
)(NewMessageComponent))

export {
  NewMessage
}
