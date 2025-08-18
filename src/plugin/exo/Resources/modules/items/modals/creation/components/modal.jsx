import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/app/utils/id'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {ItemIcon} from '#/plugin/exo/items/components/icon'

import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import {getItems} from '#/plugin/exo/items'
import {ContentMenu} from '#/main/app/content/components/menu'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_ITEM_FORM} from '#/plugin/exo/items/modals/form'

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
        {...omit(this.props, 'create', 'enableScores')}
        onEntering={this.loadAvailableTypes}
      >
        <div className="modal-body" role="presentation">
          <ContentMenu
            className="mb-3"
            search={true}
            items={this.state.types.map(type => {
              let newItem = merge({
                id: makeId(),
                type: type.type
              }, ItemTypes.defaultProps)

              if (!this.props.enableScores) {
                newItem.hasExpectedAnswers = false
                newItem.score = {type: 'none'}
              }

              // check if the current item type implements a callback for creation
              // (to append some custom defaults, for example)
              const itemDefinition = this.state.types.find(t => t.name === type.name)
              if (itemDefinition && typeof itemDefinition.create === 'function') {
                newItem = itemDefinition.create(newItem)
              }

              return ({
                id: type.type,
                icon: createElement(ItemIcon, {
                  name: type.name,
                  size: 'sm'
                }),
                label: trans(type.name, {}, 'question_types'),
                description: trans(`${type.name}_desc`, {}, 'question_types'),
                group: trans(type.answerable ? 'question' : 'content', {}, 'quiz'),
                action: {
                  type: MODAL_BUTTON,
                  modal: [MODAL_ITEM_FORM, {
                    enableScores: this.props.enableScores,
                    item: newItem,
                    isNew: true,
                    onSave: (itemData) => {
                      this.props.fadeModal()
                      this.props.create(itemData)
                    }
                  }]
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
  enableScores: T.bool,
  fadeModal: T.func.isRequired,
  create: T.func.isRequired
}

export {
  CreationModal
}
