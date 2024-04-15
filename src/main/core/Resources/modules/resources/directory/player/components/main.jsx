import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeAbsolute} from '#/main/app/action/utils'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'
import {Alert} from '#/main/app/components/alert'

import resourcesSource from '#/main/core/data/sources/resources'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {getActions, getDefaultAction} from '#/main/core/resource/utils'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {FileDrop} from '#/main/app/overlays/dnd/components/file-drop'
import {ResourcePage} from '#/main/core/resource'

/**
 * Transform resource node actions.
 * When a directory is embedded, we want all other resources to open their actions in the full app
 * while we want the directory to be browsed directly in the embedded app.
 *
 * NB. Not sure if this is the best way to handle it. But this allows to avoid a hard dependency to directories.
 *
 * @param {object}  action
 * @param {Array}   resourceNodes
 * @param {boolean} embedded
 *
 * @return {object}
 */
function transformAction(action, resourceNodes, embedded = false) {
  if (embedded && -1 === resourceNodes.findIndex(node => 'directory' === node.meta.type)) {
    return makeAbsolute(action)
  }

  return action
}

const PlayerMain = props =>
  <ResourcePage>
    {props.storageLock &&
      <Alert type="warning" className="mt-3">{trans('storage_limit_reached_resources')}</Alert>
    }

    <ContentSizing size="full">
      <FileDrop
        size="lg"
        disabled={props.storageLock || !(get(props.currentNode, 'permissions.create') || []).includes('file')}
        onDrop={(files) => props.createFiles(props.currentNode, files).then(props.updateNodes)}
        help={trans('file_drop_help', {}, 'resource')}
      >
        <ListSource
          flush={true}
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
          source={merge({}, resourcesSource('workspace', get(props.currentNode, 'workspace'), {
            update: props.updateNodes,
            delete: props.deleteNodes
          }, props.currentUser), {
            // adds actions to source
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
          })}
          parameters={props.listConfiguration}
        />
      </FileDrop>
    </ContentSizing>
  </ResourcePage>

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

  createFiles: T.func.isRequired,
  updateNodes: T.func.isRequired,
  deleteNodes: T.func.isRequired
}

export {
  PlayerMain
}
