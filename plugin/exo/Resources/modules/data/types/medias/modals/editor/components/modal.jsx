import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {GridSelection} from '#/main/app/content/grid/components/selection'

import {makeId} from '#/main/core/scaffolding/id'

import {getItems} from '#/plugin/exo/items'
import {Icon} from '#/plugin/exo/items/components/icon'

class AddMediaModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      types: []
    }
    this.loadAvailableTypes = this.loadAvailableTypes.bind(this)
  }

  loadAvailableTypes() {
    getItems().then(types => this.setState({types: types.filter(t => t.content)}))
  }

  render() {
    return (
      <Modal
        icon="fa fa-fw fa-plus"
        {...omit(this.props, 'saveFile', 'handleSelect')}
        onEntering={this.loadAvailableTypes}
      >
        <GridSelection
          items={this.state.types.map(type => ({
            id: type.type,
            name: type.name,
            icon: React.createElement(Icon, {
              name: type.name,
              size: 'lg'
            }),
            label: trans(type.name, {}, 'question_types'),
            description: trans(`${type.name}_desc`, {}, 'question_types'),
            fileUpload: type.fileUpload
          }))}

          handleSelect={(type) => {
            if (type.fileUpload) {
              this.input.accept = type.id
              this.input.click()
            } else {
              this.props.handleSelect({
                id: makeId(),
                type: type.id,
                data: ''
              })
              this.props.fadeModal()
            }
          }}
        />
        <input
          type="file"
          style={{display: 'none'}}
          ref={input => this.input = input}
          onChange={() => {
            if (this.input.files[0]) {
              const file = this.input.files[0]
              this.props.saveFile(file).then(url => {
                this.props.handleSelect({
                  id: makeId(),
                  type: file.type,
                  url: url
                })
                this.props.fadeModal()
              })

            }
          }}
        />
      </Modal>
    )
  }
}

AddMediaModal.propTypes = {
  fadeModal: T.func.isRequired,
  saveFile: T.func.isRequired,
  handleSelect: T.func.isRequired
}

export {
  AddMediaModal
}
