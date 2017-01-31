import React, {PropTypes as T} from 'react'
import Modal from 'react-bootstrap/lib/Modal'
import {trans} from './../../../utils/translate'
import {listItemMimeTypes, getDefinition} from './../../../items/item-types'
import {Icon as ItemIcon} from './../../../items/components/icon.jsx'
import {BaseModal} from './../../../modal/components/base.jsx'

export const MODAL_ADD_ITEM = 'MODAL_ADD_ITEM'

export const AddItemModal = props =>
  <BaseModal {...props} className="add-item-modal">
    <Modal.Body>
      <div role="listbox">
        {listItemMimeTypes().map(type =>
          <div
            key={type}
            className="modal-item-entry"
            role="option"
            onClick={() => props.handleSelect(type)}
          >
            <ItemIcon name={getDefinition(type).name} size="lg"/>
            <div className="modal-item-desc">
              <span className="modal-item-name">
                {trans(getDefinition(type).name, {}, 'question_types')}
              </span>
              <p>
                {trans(`${getDefinition(type).name}_desc`, {}, 'question_types')}
              </p>
            </div>
          </div>
        )}
      </div>
    </Modal.Body>
  </BaseModal>

AddItemModal.propTypes = {
  handleSelect: T.func.isRequired
}
