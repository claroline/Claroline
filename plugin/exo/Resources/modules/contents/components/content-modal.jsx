import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'
import {getContentDefinition} from './../content-types'

export const MODAL_CONTENT = 'MODAL_CONTENT'

export class ContentModal  extends Component {
  render() {
    return (
      <Modal
        className="content-modal"
        show={this.props.show}
        onHide={this.props.fadeModal}
        onExited={this.props.hideModal}
        ref={el => this.contentModal = el}
      >
        <span className="content-modal-controls">
          <span
            className="content-modal-close-btn fa fa-times"
            onClick={this.props.hideModal}
          >
          </span>
        </span>
        {this.props.data &&
          React.createElement(getContentDefinition(this.props.type).modal, {data: this.props.data, type: this.props.type})
        }
      </Modal>
    )
  }

  componentDidMount() {
    if (document.body.classList.contains('modal-open')) {
      document.body.classList.add('content-modal-open')
    }
  }

  componentWillUnmount() {
    if (document.body.classList.contains('content-modal-open')) {
      document.body.classList.remove('content-modal-open')
    }
  }
}

ContentModal.propTypes = {
  show: T.bool.isRequired,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  data: T.string,
  type: T.string.isRequired
}
