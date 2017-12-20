import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form.jsx'

const MODAL_SEND_MESSAGE = 'MODAL_SEND_MESSAGE'

const SendMessageModal = props =>
  <DataFormModal
    {...props}
    icon="fa fa-fw fa-paper-plane-o"
    title={t('send_message')}
    saveButtonText={t('send')}
    save={(message) => props.send(props.to, message)}
    sections={[
      {
        id: 'general',
        title: t('general'),
        primary: true,
        fields: [
          {
            name: 'object',
            type: 'string',
            label: t('message_form_object')
          }, {
            name: 'content',
            type: 'html',
            label: t('message_form_content'),
            required: true,
            options: {
              minRows: 5
            }
          }
        ]
      }
    ]}
  />

SendMessageModal.propTypes = {
  to: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired,
    picture: T.string
  })).isRequired,
  send: T.func.isRequired
}

export {
  MODAL_SEND_MESSAGE,
  SendMessageModal
}