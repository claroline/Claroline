import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays'
import {ContentMenu} from '#/main/app/content/components/menu'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_OAUTH_FORM} from '#/main/authentication/oauth/modals/form'

const OauthCreationModal = (props) => {
  return (
    <Modal
      {...omit(props, 'providers', 'onCreate')}
      title={trans('new_oauth', {}, 'security')}
      subtitle={trans('new_oauth_desc', {}, 'security')}
      centered={true}
    >
      <div className="modal-body" role="presentation">
        <ContentMenu
          className="mb-3"
          items={props.providers.map(provider => ({
            id: provider.name,
            icon: ' ' + provider.icon,
            label: trans(provider.name+'_oauth', {}, 'security'),
            description: trans(provider.name+'_oauth_desc', {}, 'security'),
            action: {
              type: MODAL_BUTTON,
              modal: [MODAL_OAUTH_FORM, {
                isNew: true,
                provider: provider,
                onSave: (created) => {
                  if (props.onCreate) {
                    props.onCreate(created)
                  }
                  props.fadeModal()
                }
              }]
            }
          }))}
        />
      </div>
    </Modal>
  )
}

OauthCreationModal.propTypes = {
  providers: T.arrayOf(T.shape({
    icon: T.string,
    name: T.string.isRequired
  })).isRequired,
  onCreate: T.func,
  // from modal
  fadeModal: T.func.isRequired
}

export {
  OauthCreationModal
}
