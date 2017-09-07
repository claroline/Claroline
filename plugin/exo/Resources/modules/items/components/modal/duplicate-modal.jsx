import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'

import {t, tex} from '#/main/core/translation'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

export const MODAL_DUPLICATE_ITEM = 'MODAL_DUPLICATE_ITEM'

class DuplicateItemModal extends Component {
  constructor(props) {
    super(props)

    this.state = {value: 1}
  }

  handleChange(value) {
    this.setState({value})
  }

  duplicate() {
    this.props.handleSubmit(this.state.value)
    this.props.fadeModal()
  }

  render() {
    return (
      <BaseModal
        {...this.props}
      >
        <Modal.Body>
          <FormGroup
            controlId="item-duplicate-amount"
            label={tex('amount')}
          >
            <input
              id="item-duplicate-amount"
              type="number"
              min={1}
              autoFocus={true}
              className="form-control"
              value={this.state.value}
              onChange={e => this.handleChange(parseInt(e.target.value))}
            />
          </FormGroup>
        </Modal.Body>

        <button
          className="modal-btn btn btn-primary"
          onClick={() => this.duplicate()}
          type="submit"
        >
          {t('duplicate')}
        </button>
      </BaseModal>
    )
  }
}

DuplicateItemModal.propTypes = {
  handleSubmit: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  DuplicateItemModal
}
