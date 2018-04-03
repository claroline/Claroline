import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {Message as MessageTypes} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

const MessageForm = props =>
  <FormContainer
    level={3}
    name="messages.current"
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
            disabled: !props.canEdit,
            required: true
          }, {
            name: 'content',
            type: 'html',
            label: trans('content'),
            disabled: !props.canEdit,
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