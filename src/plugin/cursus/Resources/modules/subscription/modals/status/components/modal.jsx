import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {constants} from '#/plugin/cursus/constants'

const StatusModal = props => {

  const [ selectedStatus, setSelectedStatus ] = useState(-1)
  const remark = React.createRef()

  return <Modal
    {...omit(props, 'ticket', 'changeStatus', 'onSave')}
    icon="fa fa-fw fa-check-double"
    title={trans('status')}
  >
    <div>
      <div className="list-group">
        {props.status.map(status =>
          <CallbackButton
            key={status}
            className={`list-group-item ${status==selectedStatus ? 'active' : ''}`}
            callback={() => setSelectedStatus(status)}
          >
            <h1 className="h2" style={{margin: 0}}>
              <span className={`label label-${constants.SUBSCRIPTION_STATUS_COLORS[status]}`} style={{display: 'inline-block'}}>{constants.SUBSCRIPTION_STATUSES[status]}</span>
              <small style={{color: status==selectedStatus ? '#fff' : '#000', display: 'block', marginTop: '5px'}}>{trans(constants.SUBSCRIPTION_STRINGS[status]+'_help', {}, 'cursus')}</small>
            </h1>
          </CallbackButton>
        )}
      </div>
      {selectedStatus >= 0 &&
        <form style={{padding:'0 2rem 2rem 2rem'}}>
          <div className="form-group">
            <label>{trans('remark', {}, 'cursus')}</label>
            <textarea ref={remark} className="form-control"></textarea>
          </div>
          <CallbackButton
            primary
            key={status}
            className="btn btn-primary"
            callback={() => {
              props.changeStatus(selectedStatus, remark.current.value)
              props.fadeModal()
            }}
          >
            {trans('confirm', {}, 'cursus')}
          </CallbackButton>
        </form>
      }
    </div>
  </Modal>
}

StatusModal.propTypes = {
  status: T.arrayOf(T.number).isRequired,
  changeStatus: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  StatusModal
}
