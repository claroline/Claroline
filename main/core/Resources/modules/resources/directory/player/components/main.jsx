import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {url} from '#/main/app/api'
import {URL_BUTTON} from '#/main/app/buttons'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

import resourcesSource from '#/main/core/data/sources/resources'
import {getActions, getDefaultAction} from '#/main/core/resource/utils'

/**
 * Transform resource node actions.
 * When a directory is embedded, we want all other resources to open their actions in the full app
 * while we want the directory to be browsed directly in the embedded app.
 *
 * NB. Not sure this is the best way to handle it. But this allows to avoid a hard dependency to directories.
 *
 * @param {object}  action
 * @param {Array}   resourceNodes
 * @param {boolean} embedded
 *
 * @return {object}
 */
function transformAction(action, resourceNodes, embedded = false) {
  if (embedded && -1 === resourceNodes.findIndex(node => 'directory' === node.meta.type)) {
    // make the action an URL button to escape the embedded router
    return merge({}, action, {
      type: URL_BUTTON,
      target: url(['claro_index'])+'#'+action.target
    })
  }

  return action
}

const PlayerMain = props =>
  <ListSource
    name={props.listName}
    fetch={{
      url: ['apiv2_resource_list', {parent: props.id, all: props.all}],
      autoload: true
    }}
    source={merge({}, resourcesSource, {
      // adds actions to source
      parameters: {
        primaryAction: (resourceNode) => getDefaultAction(resourceNode, {
          update: props.updateNodes,
          delete: props.deleteNodes
        }, props.path, props.currentUser).then((action) => {
          if (action) {
            return transformAction(action, [resourceNode], props.embedded)
          }

          return null
        }),
        actions: (resourceNodes) => getActions(resourceNodes, {
          update: props.updateNodes,
          delete: props.deleteNodes
        }, props.path, props.currentUser).then((actions) => actions.map(action => transformAction(action, resourceNodes, props.embedded)))
      }
    })}
    parameters={props.listConfiguration}
  />

PlayerMain.propTypes = {
  path: T.string,
  all: T.string,
  embedded: T.bool.isRequired,
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
