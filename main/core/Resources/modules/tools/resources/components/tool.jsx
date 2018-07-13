import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Page} from '#/main/app/page/components/page'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceExplorer} from '#/main/core/resource/explorer/containers/explorer'
import {getActions, getToolbar} from '#/main/core/resource/utils'
import {hasPermission} from '#/main/core/resource/permissions'

import {actions as explorerActions, selectors as explorerSelectors} from '#/main/core/resource/explorer/store'
import {selectors} from '#/main/core/tools/resources/store'

const Tool = props =>
  <Page
    title={trans('resources', {}, 'tools')}
    subtitle={props.current && props.current.name}
    toolbar={getToolbar('add')}
    actions={props.current && props.getActions([props.current], props.refresh)}
  >
    <ResourceExplorer
      name={selectors.STORE_NAME}
      primaryAction={(resourceNode) => ({ // todo : use resource default action
        type: 'url',
        label: trans('open', {}, 'actions'),
        disabled: !hasPermission('open', resourceNode),
        target: [ 'claro_resource_open', {
          node: resourceNode.autoId,
          resourceType: resourceNode.meta.type
        }]
      })}
      actions={(resourceNodes) => props.getActions(resourceNodes)}
    />
  </Page>

Tool.propTypes = {
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  refresh: T.func.isRequired,
  getActions: T.func.isRequired
}

const ResourcesTool = connect(
  state => ({
    current: explorerSelectors.current(explorerSelectors.explorer(state, selectors.STORE_NAME))
  }),
  dispatch => ({
    getActions(resourceNodes) {
      return getActions(resourceNodes, dispatch)
    },
    refresh() {
      dispatch(explorerActions.refresh(selectors.STORE_NAME))
    }
  })
)(Tool)

export {
  ResourcesTool
}
