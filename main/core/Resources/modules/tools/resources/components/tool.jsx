import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Page} from '#/main/app/page/components/page'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceExplorer} from '#/main/core/resource/components/explorer'
import {getActions} from '#/main/core/resource/utils'
import {hasPermission} from '#/main/core/resource/permissions'

import {actions} from '#/main/core/tools/resources/store'

const Tool = props =>
  <Page
    title={trans('resources', {}, 'tools')}
    subtitle={props.current && props.current.name}
    toolbar="edit rights publish unpublish | more"
    actions={props.current && props.getActions([props.current])}
  >
    <ResourceExplorer
      root={props.root}
      current={props.current}
      primaryAction={(resourceNode) => ({
        type: 'url',
        label: trans('open', {}, 'actions'),
        disabled: !hasPermission('open', resourceNode),
        target: [ 'claro_resource_open', {
          node: resourceNode.autoId,
          resourceType: resourceNode.meta.type
        }]
      })}
      actions={(resourceNodes) => props.getActions(resourceNodes)}
      changeDirectory={props.changeDirectory}
    />
  </Page>

Tool.propTypes = {
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  getActions: T.func.isRequired,
  changeDirectory: T.func.isRequired
}

const ResourcesTool = connect(
  state => ({
    root: state.root,
    current: state.current
  }),
  dispatch => ({
    getActions(resourceNodes) {
      return getActions(resourceNodes, dispatch)
    },

    changeDirectory(directoryNode) {
      dispatch(actions.changeDirectory(directoryNode))
    }
  })
)(Tool)

export {
  ResourcesTool
}
