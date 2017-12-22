import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import Modal from 'react-bootstrap/lib/Modal'

import {tex} from '#/main/core/translation'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import select from '../../selectors'

export const MODAL_MOVE_ITEM = 'MODAL_MOVE_ITEM'

const MoveItemModal = props =>
  <BaseModal
    {...props}
    className="step-move-item-modal"
  >
    <Modal.Body>
      <div className="step-list-box">
        {Object.keys(props.steps).map((stepId, index) =>
          <span
            key={'thumbnail-' + stepId}
            className='thumbnail'
            onClick={() => {
              props.handleClick(stepId)
              props.fadeModal()
            }}
          >
            <span className='step-title'>{tex('step') + ' ' + (index + 1)}</span>
          </span>
        )}
      </div>
    </Modal.Body>
  </BaseModal>

MoveItemModal.propTypes = {
  handleClick: T.func.isRequired,
  steps: T.object.isRequired,
  fadeModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    steps: select.steps(state)
  }
}

const ConnectedMoveItemModal = connect(mapStateToProps, {})(MoveItemModal)

export {
  ConnectedMoveItemModal as MoveItemModal
}
