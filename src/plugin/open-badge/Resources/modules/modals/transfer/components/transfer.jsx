import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/open-badge/modals/transfer/store'

const TransferModal = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'transfer', 'reset')}
    icon="fa fa-fw fa-right-left"
    title={trans('transfer_badges', {}, 'actions')}
    onEnter={() => props.reset()}
  >
    <FormData
      name={selectors.STORE_NAME}
      flush={true}
      sections={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'sender',
              type: 'user',
              label: trans('transfer_from', {}, 'badge')
            }, {
              name: 'receiver',
              type: 'user',
              label: trans('transfer_to', {}, 'badge')
            }
          ]
        }
      ]}
    />

    <Button
      className="modal-btn"
      variant="btn"
      size="lg"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('transfer', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.transfer(props.data)
        props.fadeModal()
      }}
    />
  </Modal>

TransferModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  reset: T.func.isRequired,
  transfer: T.func.isRequired,
  data: T.object.isRequired,
  fadeModal: T.func.isRequired
}

export {
  TransferModal
}
