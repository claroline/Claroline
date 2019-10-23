import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormDataModal} from '#/main/app/modals/form/components/data'

const MessageModal = props =>
  <FormDataModal
    {...omit(props, 'to', 'send')}
    icon="fa fa-fw fa-paper-plane"
    title={trans('new_message', {}, 'message')}
    saveButtonText={trans('send', {}, 'actions')}
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
            label: trans('object')
          }, {
            name: 'content',
            type: 'html',
            label: trans('content'),
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