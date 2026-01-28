import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {makeAbsolute} from '#/main/app/action/utils'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

import resourcesSource from '#/main/core/data/sources/resources'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {getActions, getDefaultAction} from '#/main/core/resource/utils'
import {FileDrop} from '#/main/app/overlays/dnd/components/file-drop'
import {ResourcePage} from '#/main/core/resource'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_RESOURCE_CREATION} from '#/main/core/resource/modals/creation'
import {PageContent} from '#/main/app/page'
import {ButtonSticky} from '#/main/app/button'

import {selectors} from '#/main/core/resources/directory/store'

/**
 * Transform resource node actions.
 * When a directory is embedded, we want all other resources to open their actions in the full app
 * while we want the directory to be browsed directly in the embedded app.
 *
 * NB. Not sure if this is the best way to handle it. But this allows avoiding a hard dependency on directories.
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

const DirectoryPlayer = (props) => {
  return (
    <ResourcePage
      breadcrumb={props.isRoot ? [
        {
          label: trans('resources', {}, 'tools'),
          target: props.path
        }
      ] : undefined}
    >
      <PageContent className="d-flex flex-column">
        <FileDrop
          className="flex-fill"
          size="lg"
          disabled={isEmpty(get(props.currentNode, 'permissions.create'))}
          onDrop={(files) => props.uploadFiles(props.currentNode, files).then(props.updateNodes)}
          help={trans('file_drop_help', {}, 'resource')}
        >
          <ListSource
            className="mb-5"
            flush={!props.embedded}
            name={selectors.LIST_NAME}
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
                  return transformAction(action, [resourceNode], props.path, props.embedded)
                }

                return null
              }),
              actions: (resourceNodes) => getActions(resourceNodes, {
                update: props.updateNodes,
                delete: props.deleteNodes
              }, props.path, props.currentUser).then((actions) => actions
                .map(action => transformAction(action, resourceNodes, props.path, props.embedded)))
            })}
            parameters={props.listConfiguration}
          />

          {get(props.currentNode, 'permissions.create', []).length > 0 &&
            <ButtonSticky
              {...{
                name: 'add',
                type: MODAL_BUTTON,
                label: trans('add_resource', {}, 'actions'),
                modal: [MODAL_RESOURCE_CREATION, {
                  parent: props.currentNode,
                  add: props.updateNodes
                }],
                displayed: get(props.currentNode, 'permissions.create', []).length > 0
              }}
              className="me-4"
            />
          }
        </FileDrop>
      </PageContent>
    </ResourcePage>
  )
}

DirectoryPlayer.propTypes = {
  path: T.string,
  all: T.string,
  embedded: T.bool.isRequired,
  currentUser: T.object,
  currentNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  listConfiguration: T.shape(
    ListParametersTypes.propTypes
  ),
  isRoot: T.bool,

  uploadFiles: T.func.isRequired,
  updateNodes: T.func.isRequired,
  deleteNodes: T.func.isRequired
}

export {
  DirectoryPlayer
}
