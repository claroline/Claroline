import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {FormDataModal} from '#/main/app/modals/form/components/data'

const MessageModal = props =>
  <FormDataModal
    {...props}
    icon="fa fa-fw fa-paper-plane-o"
    title={trans('send_message')}
    saveButtonText={trans('send')}
    save={(message) => props.send(props.to, message)}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'object',
            type: 'string',
            label: trans('message_form_object')
          }, {
            name: 'content',
            type: 'html',
            label: trans('message_form_content'),
            required: true,
            options: {
              minRows: 5
            }
          }
        ]
      }
    ]}
  />

MessageModal.propTypes = {
  to: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired,
    picture: T.string
  })).isRequired,
  send: T.func.isRequired
}

export {
  MessageModal
}