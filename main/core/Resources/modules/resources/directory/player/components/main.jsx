import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

import resourcesSource from '#/main/core/data/sources/resources'
import {getActions, getDefaultAction} from '#/main/core/resource/utils'

const PlayerMain = props =>
  <ListSource
    name={props.listName}
    fetch={{
      url: ['apiv2_resource_list', {parent: props.id}],
      autoload: true
    }}
    source={merge({}, resourcesSource, {
      // adds actions to source
      parameters: {
        primaryAction: (resourceNode) => getDefaultAction(resourceNode, {
          update: props.updateNodes,
          delete: props.deleteNodes
        }, props.path, props.currentUser),
        actions: (resourceNodes) => getActions(resourceNodes, {
          update: props.updateNodes,
          delete: props.deleteNodes
        }, props.path, props.currentUser)
      }
    })}
    parameters={props.listConfiguration}
  />

PlayerMain.propTypes = {
  path: T.string,
  currentUser: T.object,
  id: T.string,
  listName: T.string.isRequired,
  listConfiguration: T.shape(
    ListParametersTypes.propTypes
  ),

  updateNodes: T.func.isRequired,
  deleteNodes: T.func.isRequired
}

export {
  PlayerMain
}
