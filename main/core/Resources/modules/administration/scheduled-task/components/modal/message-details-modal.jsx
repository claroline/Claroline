import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import moment from 'moment'
import Modal from 'react-bootstrap/lib/Modal'

import {t} from '#/main/core/translation'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'

export const MODAL_DETAILS_TASK_MESSAGE = 'MODAL_DETAILS_TASK_MESSAGE'

export class MessageDetailsModal  extends Component {
  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Body>
          <div className="row">
            <div className="col-md-3">
              <label>{t('scheduled_date')}</label>
            </div>
            <div className="col-md-9">
              {moment(this.props.task.scheduledDate).format('DD/MM/YYYY HH:mm')}
            </div>
          </div>
          <hr/>
          <div className="row">
            <div className="col-md-3">
              <label>{t('receivers')}</label>
            </div>
            <div className="col-md-9">
              {this.props.task.users.map(u => `${u.firstName} ${u.lastName}`).join(', ')}
            </div>
          </div>
          <hr/>
          <div className="row">
            <div className="col-md-3">
              <label>{t('object')}</label>
            </div>
            <div className="col-md-9">
              {this.props.task.data.object}
            </div>
          </div>
          <hr/>
          <div className="row">
            <div className="col-md-3">
              <label>{t('content')}</label>
            </div>
            <div className="col-md-9">
              <div dangerouslySetInnerHTML={{__html: this.props.task.data.content}}></div>
            </div>
          </div>

        </Modal.Body>
      </BaseModal>
    )
  }
}

MessageDetailsModal.propTypes = {
  task: T.shape({
    id: T.number.isRequired,
    name: T.string,
    type: T.string.isRequired,
    scheduledDate: T.string.isRequired,
    users: T.array.isRequired,
    data:  T.shape({
      object: T.string.isRequired,
      content: T.string.isRequired
    }).isRequired
  }).isRequired
}
