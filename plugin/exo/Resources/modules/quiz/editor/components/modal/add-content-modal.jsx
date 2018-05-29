import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {listContentTypes, getContentDefinition} from './../../../../contents/content-types'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ContentInput} from './../content-input.jsx'

export const MODAL_ADD_CONTENT = 'MODAL_ADD_CONTENT'

class AddContentModal extends Component {
  constructor(props) {
    super(props)
    const contentTypes = listContentTypes()

    this.state = {
      contentTypes: contentTypes,
      currentType: contentTypes[0],
      currentName: trans(getContentDefinition(contentTypes[0]).type, {}, 'question_types'),
      input: {}
    }
  }

  handleItemMouseOver(type) {
    const name = trans(getContentDefinition(type).type, {}, 'question_types')
    this.setState({
      currentType: type,
      currentName: name
    })
  }

  render() {
    return (
      <Modal {...this.props} className="add-item-modal">
        <div className="modal-body">
          <div className="modal-item-list" role="listbox">
            {this.state.contentTypes.map(type =>
              <ContentInput
                key={type}
                type={type}
                selected={this.state.currentType === type}
                handleSelect={type => this.props.handleSelect(type)}
                handleItemMouseOver={type => this.handleItemMouseOver(type)}
                handleFileUpload={(itemId, file) => this.props.handleFileUpload(itemId, file)}
              />
            )}
          </div>
          <div className="modal-item-desc">
            <span className="modal-item-name">
              {this.state.currentName}
            </span>
          </div>
        </div>
      </Modal>
    )
  }
}

AddContentModal.propTypes = {
  handleSelect: T.func.isRequired,
  handleFileUpload: T.func
}

export {AddContentModal}
