import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {constants} from '#/plugin/cursus/constants'

const PresenceModal = props =>
  <Modal
    {...omit(props, 'ticket', 'changeStatus', 'onSave')}
    icon="fa fa-fw fa-check-double"
    title={trans('presences', {}, 'cursus')}
  >
    <div className="list-group">
      {Object.keys(constants.PRESENCE_STATUSES).map(status =>
        <CallbackButton
          key={status}
          className="list-group-item"
          callback={() => {
            props.changeStatus(status)
            props.fadeModal()
          }}
        >
          <h1 className="h2" style={{margin: 0}}>
            <span className={`label label-${constants.PRESENCE_STATUS_COLORS[status]}`} style={{display: 'inline-block'}}>{trans(constants.PRESENCE_STATUSES[status], {}, 'cursus')}</span>
            <small style={{display: 'block', marginTop: '5px'}}>{trans('presence_'+status+'_help', {}, 'cursus')}</small>
          </h1>
        </CallbackButton>
      )}
    </div>
  </Modal>

PresenceModal.propTypes = {
  changeStatus: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  PresenceModal
}
