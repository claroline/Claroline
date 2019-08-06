import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/message/tools/messaging/modals/parameters/store'

// target={(parameters) => ['apiv2_contact_options_update', {id: parameters.id}]}

const ParametersModal = props =>
  <Modal
    {...omit(props, 'mailNotified', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={trans('messaging', {}, 'tools')}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'mailNotified',
              type: 'boolean',
              label: transChoice('get_mail_notifications', props.currentUser.email, {address: props.currentUser.email})
            }
          ]
        }
      ]}
    />

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.currentUser, props.mailNotified)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModal.propTypes = {
  currentUser: T.shape({
    // TODO
  }).isRequired,
  mailNotified: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
