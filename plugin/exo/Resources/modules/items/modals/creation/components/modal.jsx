import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {GridSelection} from '#/main/app/content/grid/components/selection'

import {Icon} from '#/plugin/exo/items/components/icon'

import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import {getItems} from '#/plugin/exo/items'

class CreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      types: []
    }

    this.loadAvailableTypes = this.loadAvailableTypes.bind(this)
  }

  loadAvailableTypes() {
    getItems().then(types => this.setState({types: types}))
  }

  render() {
    return (
      <Modal
        icon="fa fa-fw fa-plus"
        {...omit(this.props, 'create')}
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
            tags: type.tags
          }))}
          handleSelect={(type) => {
            const newItem = merge({id: makeId(), type: type.id}, ItemTypes.defaultProps)

            // TODO : set meta (creator, updated, created)
            // TODO : call a type def setDefault method (for example to allow choice type to pre create choices)
            this.props.fadeModal()
            this.props.create(newItem)
          }}
        />
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
