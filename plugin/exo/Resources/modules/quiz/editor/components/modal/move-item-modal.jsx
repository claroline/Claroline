import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {tex} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import select from '../../selectors'

export const MODAL_MOVE_ITEM = 'MODAL_MOVE_ITEM'

const MoveItemModal = props =>
  <Modal
    {...props}
    className="step-move-item-modal"
  >
    <div className="modal-body">
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
            <span className='step-label'>{tex('step') + ' ' + (index + 1)}</span>
          </span>
        )}
      </div>
    </div>
  </Modal>

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
