import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {Message as MessageTypes} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

const MessageForm = props =>
  <FormData
    level={3}
    name="messages.current"
    disabled={!props.canEdit}
    buttons={true}
    target={(message, isNew) => isNew ?
      ['apiv2_plannednotificationmessage_create'] :
      ['apiv2_plannednotificationmessage_update', {id: message.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: '/messages',
      exact: true
    }}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            required: true
          }, {
            name: 'content',
            type: 'html',
            label: trans('content'),
            required: true
          }
        ]
      }
    ]}
  />

MessageForm.propTypes = {
  canEdit: T.bool.isRequired,
  new: T.bool.isRequired,
  message: T.shape(MessageTypes.propTypes).isRequired
}

const Message = connect(
  state => ({
    canEdit: select.canEdit(state),
    new: formSelect.isNew(formSelect.form(state, 'messages.current')),
    message: formSelect.data(formSelect.form(state, 'messages.current'))
  })
)(MessageForm)

export {
  Message
}