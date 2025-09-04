import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

const MessageModal = (props) => {
  const isAdmin = useSelector(securitySelectors.isAdmin)

  return (
    <FormModal
      {...omit(props, 'receivers', 'onSend')}
      icon="fa fa-fw fa-paper-plane"
      title={trans('new_message', {}, 'message')}
      name="messageForm"
      target={['apiv2_message_create']}
      data={{
        receivers: props.receivers
      }}
      isNew={true}
      onSave={props.onSend}
      saveLabel={trans('send', {}, 'actions')}
      definition={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'receivers.users',
              type: 'user',
              label: trans('message_form_to', {}, 'message'),
              options: {multiple: true}
            }, {
              name: 'receivers.groups',
              type: 'group',
              label: trans('message_form_to', {}, 'message'),
              options: {
                multiple: true,
                // we don't want someone to be able to send a message to everyone
                picker: {filters: !isAdmin ? [{property: 'meta.everyone', value: false, locked: true, hidden: true}] : []}
              }
            }, {
              name: 'receivers.workspaces',
              type: 'workspace',
              label: trans('message_form_to', {}, 'message'),
              options: {multiple: true}
            }, {
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
  )
}

MessageModal.propTypes = {
  receivers: T.shape({
    users: T.arrayOf(T.shape({
      // user types
    })),
    groups: T.arrayOf(T.shape({
      // group types
    })),
    workspaces: T.arrayOf(T.shape({
      // workspace types
    }))
  }),
  onSend: T.func,
  fadeModal: T.func.isRequired
}

export {
  MessageModal
}