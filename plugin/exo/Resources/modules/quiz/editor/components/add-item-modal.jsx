import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Modal from 'react-bootstrap/lib/Modal'

import {trans} from '#/main/core/translation'
import {listItemMimeTypes, getDefinition} from './../../../items/item-types'
import {Icon as ItemIcon} from './../../../items/components/icon.jsx'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'

export const MODAL_ADD_ITEM = 'MODAL_ADD_ITEM'

class AddItemModal extends Component {
  constructor(props) {
    super(props)
    const itemMimeTypes = listItemMimeTypes()

    this.state = {
      itemMimeTypes: itemMimeTypes,
      currentType: itemMimeTypes[0],
      currentName: trans(getDefinition(itemMimeTypes[0]).name, {}, 'question_types'),
      currentDesc: trans(`${getDefinition(itemMimeTypes[0]).name}_desc`, {}, 'question_types')
    }
  }

  handleItemMouseOver(type) {
    const name = trans(getDefinition(type).name, {}, 'question_types')
    const desc = trans(`${getDefinition(type).name}_desc`, {}, 'question_types')
    this.setState({
      currentType: type,
      currentName: name,
      currentDesc: desc
    })
  }

  render() {
    return (
      <BaseModal {...this.props} className="add-item-modal">
        <Modal.Body>
          <div className="modal-item-list" role="listbox">
            {this.state.itemMimeTypes.map(type =>
              <div
                key={type}
                className={classes('modal-item-entry', {'selected': this.state.currentType === type})}
                role="option"
                onMouseOver={() => this.handleItemMouseOver(type)}
                onClick={() => this.props.handleSelect(type)}
              >
                <ItemIcon name={getDefinition(type).name} size="lg"/>
              </div>
            )}
          </div>

          <div className="modal-item-desc">
            <span className="modal-item-name">
              {this.state.currentName}
            </span>
            <p>
              {this.state.currentDesc}
            </p>
          </div>
        </Modal.Body>
      </BaseModal>
    )
  }
}

AddItemModal.propTypes = {
  handleSelect: T.func.isRequired
}

export {AddItemModal}
