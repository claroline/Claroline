import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {SelectionModal} from '#/main/app/modals/selection/components/selection'
import {MODAL_RESOURCE_CREATION_PARAMETERS} from '#/main/core/resource/modals/creation/components/parameters'

import {getType} from '#/main/core/resource/utils'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {actions} from '#/main/core/resource/modals/creation/store'

const ResourceTypeModalComponent = props =>
  <SelectionModal
    {...omit(props, 'parent', 'configure')}
    icon="fa fa-fw fa-plus"
    title={trans('new_resource', {}, 'resource')}
    subtitle="1. Choisir le type de ressource à créer"
    items={props.parent.permissions.create.map(name => {
      const tags = getType({meta: {type: name}}).tags || []

      return ({ // todo maybe filter disabled types
        name: name,
        icon: React.createElement(ResourceIcon, {
          mimeType: `custom/${name}`
        }),
        label: trans(name, {}, 'resource'),
        description: trans(`${name}_desc`, {}, 'resource'),
        tags: tags.map(tag => trans(tag))
      })
    })}
    handleSelect={(resourceType) => props.configure(props.parent, resourceType)}
  />

ResourceTypeModalComponent.propTypes = {
  parent: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  configure: T.func.isRequired
}

const ResourceTypeModal = connect(
  null,
  (dispatch) => ({
    configure(parent, resourceType) {
      dispatch(actions.startCreation(parent, resourceType))

      // display the second creation modal
      dispatch(modalActions.showModal(MODAL_RESOURCE_CREATION_PARAMETERS, {}))
    }
  })
)(ResourceTypeModalComponent)

export {
  ResourceTypeModal
}
