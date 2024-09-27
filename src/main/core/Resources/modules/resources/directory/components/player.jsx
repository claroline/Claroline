import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeAbsolute} from '#/main/app/action/utils'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'
import {Alert} from '#/main/app/components/alert'

import resourcesSource from '#/main/core/data/sources/resources'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {getActions, getDefaultAction} from '#/main/core/resource/utils'
import {FileDrop} from '#/main/app/overlays/dnd/components/file-drop'
import {ResourcePage} from '#/main/core/resource'
import {PageListSection} from '#/main/app/page/components/list-section'

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

const DirectoryPlayer = props =>
  <ResourcePage
    root={true}
    title={props.isRoot ? trans('resources', {}, 'tools') : get(props.currentNode, 'name', null)}
  >
    {props.storageLock &&
      <Alert type="warning" className="mt-3">{trans('storage_limit_reached_resources')}</Alert>
    }

    <PageListSection>
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
            url: ['apiv2_resource_list', {contextId: get(props.currentNode, 'workspace.id', null), parent: get(props.currentNode, 'id', null)}],
            autoload: true
          }}
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
    </PageListSection>
  </ResourcePage>

DirectoryPlayer.propTypes = {
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
  DirectoryPlayer
}
