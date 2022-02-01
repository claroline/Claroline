import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'
import {Alert} from '#/main/app/alert/components/alert'

import resourcesSource from '#/main/core/data/sources/resources'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
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
  if (embedded && LINK_BUTTON === action.type && -1 === resourceNodes.findIndex(node => 'directory' === node.meta.type)) {
    // make the action an URL button to escape the embedded router
    return merge({}, action, {
      type: URL_BUTTON,
      target: url(['claro_index'])+'#'+action.target
    })
  }

  return action
}

const PlayerMain = props =>
  <Fragment>
    {props.storageLock &&
      <Alert type="warning" style={{marginTop: 20}}>{trans('storage_limit_reached_resources')}</Alert>
    }

    <ListSource
      name={props.listName}
      fetch={{
        url: ['apiv2_resource_list', {parent: get(props.currentNode, 'id', null), all: props.all}],
        autoload: true
      }}
      customActions={[
        {
          name: 'back',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-arrow-left',
          label: get(props.currentNode, 'parent') ?
            trans('back_to', {target: get(props.currentNode, 'parent.name')}) :
            trans('back'),
          disabled: !isEmpty(props.rootNode) && props.currentNode.slug === props.rootNode.slug,
          target: `${props.path}/${get(props.currentNode, 'parent.slug', '')}`,
          exact: true
        }
      ]}
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
          }, props.path, props.currentUser).then((actions) => actions
            .filter(action => !props.storageLock || 'copy' !== action.name)
            .map(action => transformAction(action, resourceNodes, props.embedded)))
        }
      })}
      parameters={props.listConfiguration}
    />
  </Fragment>

PlayerMain.propTypes = {
  path: T.string,
  all: T.string,
  embedded: T.bool.isRequired,
  currentUser: T.object,
  rootNode: T.shape(
    ResourceNodeTypes.propTypes
  ),
  currentNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  listName: T.string.isRequired,
  listConfiguration: T.shape(
    ListParametersTypes.propTypes
  ),
  storageLock: T.bool.isRequired,

  updateNodes: T.func.isRequired,
  deleteNodes: T.func.isRequired
}

export {
  PlayerMain
}
