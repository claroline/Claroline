import {connect} from 'react-redux'
import React, {Component, PropTypes as T} from 'react'
import Modal from 'react-bootstrap/lib/Modal'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {t} from '#/main/core/translation'

export const MODAL_EVENT_COMMENTS = 'MODAL_EVENT_COMMENTS'

class EventCommentsModal  extends Component {
  constructor(props) {
    super(props)
    this.state = {
      comment: null
    }
  }

  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Body>
        </Modal.Body>
        <Modal.Footer>
          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {t('close')}
          </button>
        </Modal.Footer>
      </BaseModal>
    )
  }
}

EventCommentsModal.propTypes = {
  event: T.shape({
    id: T.number,
    name: T.string,
    description: T.string,
    startDate: T.string,
    endDate: T.string,
    registrationType: T.number.isRequired,
    maxUsers: T.number
  }).isRequired,
  eventComments: T.array.isRequired,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    eventComments: state.eventComments
  }
}

function mapDispatchToProps() {
  return {}
}

const ConnectedEventCommentsModal = connect(mapStateToProps, mapDispatchToProps)(EventCommentsModal)

export {ConnectedEventCommentsModal as EventCommentsModal}
