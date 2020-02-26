import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {ContentHtml} from '#/main/app/content/components/html'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Alert} from '#/main/app/alert/components/alert'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/app/modals/maintenance/store/selectors'

const MaintenanceModal = props =>
  <Modal
    {...omit(props, 'updateProp', 'handleConfirm')}
    icon="fa fa-fw fa-hard-hat"
    title={trans('maintenance_mode', {}, 'administration')}
    subtitle={trans('activation')}
  >
    <div className="modal-body">
      <p className="text-justify">
        {trans('maintenance_mode_activation', {}, 'administration')}
      </p>

      <Alert type="info" style={{marginBottom: 0}}>
        <ContentHtml>{trans('maintenance_allowed_users', {role: '<b>ROLE_ADMIN</b>'}, 'administration')}</ContentHtml>
      </Alert>
    </div>

    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          fields: [
            {
              name: 'customizeMessage',
              type: 'boolean',
              label: trans('customize_maintenance_message', {}, 'administration'),
              linked: [
                {
                  name: 'message',
                  label: trans('message'),
                  type: 'html',
                  options: {
                    minRows: 7
                  },
                  displayed: (data) => data.customizeMessage || data.message,
                  onChange: activated => {
                    if (!activated) {
                      props.updateProp('message', null)
                    }
                  }
                }
              ]
            }
          ]
        }
      ]}
    >
      <CallbackButton
        htmlType="submit"
        className="modal-btn btn"
        callback={() => props.handleConfirm(props.message).then(() =>
          props.fadeModal()
        )}
        dangerous={true}
      >
        {trans('enable', {}, 'actions')}
      </CallbackButton>
    </FormData>
  </Modal>

MaintenanceModal.propTypes = {
  message: T.string,
  fadeModal: T.func.isRequired,
  updateProp: T.func.isRequired,
  handleConfirm: T.func.isRequired
}

export {
  MaintenanceModal
}
