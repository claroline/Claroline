import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/message/modals/message/store'

const MessageModal = props =>
  <Modal
    {...omit(props, 'message', 'receivers', 'saveEnabled', 'onSend', 'send', 'reset')}
    icon="fa fa-fw fa-paper-plane"
    title={trans('new_message', {}, 'message')}
    onEnter={() => props.reset(props.receivers)}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'receivers.users',
              type: 'users',
              label: trans('message_form_to', {}, 'message')
            }, {
              name: 'receivers.groups',
              type: 'groups',
              label: trans('message_form_to', {}, 'message'),
              options: {
                // the only readOnly group is ROLE_USER which contains all the platform users
                // we don't want someone to be able to send a message to everyone
                picker: {filters: !props.isAdmin ? [{property: 'meta.readOnly', value: false, locked: true}] : []}
              }
            }, {
              name: 'receivers.workspaces',
              type: 'workspaces',
              label: trans('message_form_to', {}, 'message')
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

    <Button
      className="modal-btn btn btn-primary"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('send', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.send(props.onSend)
        props.fadeModal()
      }}
    />
  </Modal>

MessageModal.propTypes = {
  isAdmin: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  receivers: T.shape({
    users: T.arrayOf(T.shape({
      // TODO : user types
    })),
    groups: T.arrayOf(T.shape({
      // TODO : group types
    })),
    workspaces: T.arrayOf(T.shape({
      // TODO : workspace types
    }))
  }),
  reset: T.func.isRequired,
  onSend: T.string,
  send: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  MessageModal
}