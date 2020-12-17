import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as formSelectors} from '#/main/app/content/form/store'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store'
import {Message as MessageType} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

const MessageForm = props =>
  <FormData
    level={3}
    name={selectors.STORE_NAME+'.messages.current'}
    disabled={!props.canEdit}
    buttons={true}
    target={(message, isNew) => isNew ?
      ['apiv2_plannednotificationmessage_create'] :
      ['apiv2_plannednotificationmessage_update', {id: message.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/messages',
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
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  new: T.bool.isRequired,
  message: T.shape(MessageType.propTypes).isRequired
}

const Message = connect(
  state => ({
    path: toolSelectors.path(state),
    canEdit: selectors.canEdit(state),
    new: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME+'.messages.current')),
    message: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME+'.messages.current'))
  })
)(MessageForm)

export {
  Message
}