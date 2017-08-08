import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'
import {connect} from 'react-redux'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import select from '../../selectors'
import {tex} from '#/main/core/translation'

export const MODAL_MOVE_QUESTION = 'MODAL_MOVE_QUESTION'

class MoveQuestionModal extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <BaseModal {...this.props} className="step-move-item-modal">
        <Modal.Body>
          <div className="step-list-box">
            {Object.keys(this.props.steps).map((key, index) =>
              <span
                key={'thumbnail-' + key}
                className='thumbnail'
                onClick={() => {
                  this.props.handleClick(this.props.itemId, key)
                  this.props.fadeModal()
                }}
              >
                <span className='step-title'>{tex('step') + ' ' + (index + 1)}</span>
              </span>
            )}
          </div>
        </Modal.Body>
      </BaseModal>
    )
  }
}

MoveQuestionModal.propTypes = {
  handleClick: T.func.isRequired,
  steps: T.object.isRequired,
  itemId: T.string.isRequired,
  fadeModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    steps: select.steps(state)
  }
}

const ConnectedMoveQuestionModal = connect(mapStateToProps, {})(MoveQuestionModal)

export {ConnectedMoveQuestionModal as MoveQuestionModal}
