import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {constants} from '#/plugin/cursus/constants'

const StatusModal = props =>
  <Modal
    {...omit(props, 'ticket', 'changeStatus', 'onSave')}
    icon="fa fa-fw fa-check-double"
    title={trans('status', {}, 'cursus')}
  >
    <div className="list-group">
      {props.status.map(status =>
        <CallbackButton
          key={status}
          className="list-group-item"
          callback={() => {
            props.changeStatus(status)
            props.fadeModal()
          }}
        >
          <h1 className="h2" style={{margin: 0}}>
            <span className={`label label-${constants.SUBSCRIPTION_STATUS_COLORS[status]}`} style={{display: 'inline-block'}}>{constants.SUBSCRIPTION_STATUSES[status]}</span>
            <small style={{display: 'block', marginTop: '5px'}}>{trans('status_'+constants.SUBSCRIPTION_STATUSES[status]+'_help', {}, 'cursus')}</small>
          </h1>
        </CallbackButton>
      )}
    </div>
  </Modal>

StatusModal.propTypes = {
  status: T.arrayOf(T.number).isRequired,
  changeStatus: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  StatusModal
}
