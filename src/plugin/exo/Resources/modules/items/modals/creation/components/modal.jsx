import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Icon} from '#/plugin/exo/items/components/icon'

import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import {getItems} from '#/plugin/exo/items'
import {ContentCreation} from '#/main/app/content/components/creation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

class CreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      types: []
    }

    this.loadAvailableTypes = this.loadAvailableTypes.bind(this)
  }

  loadAvailableTypes() {
    getItems(true).then(types => this.setState({types: types}))
  }

  render() {
    return (
      <Modal
        title={trans('new_item', {}, 'quiz')}
        {...omit(this.props, 'create')}
        onEntering={this.loadAvailableTypes}
      >
        <div className="modal-body" role="presentation">
          <ContentCreation
            className="mb-3"
            color={false}
            types={this.state.types.map(type => {
              return ({
                id: type.type,
                icon: createElement(Icon, {
                  name: type.name,
                  size: 'sm'
                }),
                label: trans(type.name, {}, 'question_types'),
                description: trans(`${type.name}_desc`, {}, 'question_types'),
                action: {
                  type: CALLBACK_BUTTON,
                  callback: () => {
                    let newItem = merge({
                      id: makeId(),
                      type: type.type
                    }, ItemTypes.defaultProps)

                    // check if the current item type implement a callback for creation
                    // (to append some custom defaults for example)
                    const itemDefinition = this.state.types.find(t => t.name === type.name)
                    if (itemDefinition && typeof itemDefinition.create === 'function') {
                      newItem = itemDefinition.create(newItem)
                    }

                    this.props.fadeModal()
                    this.props.create(newItem)
                  }
                }
              })
            })}
          />
        </div>
      </Modal>
    )
  }
}

CreationModal.propTypes = {
  fadeModal: T.func.isRequired,
  create: T.func.isRequired
}

export {
  CreationModal
}
