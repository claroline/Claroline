import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {Page} from '#/main/app/page/components/page'
import {PageContent} from '#/main/core/layout/page'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceExplorer} from '#/main/core/resource/components/explorer'

import {getActions, getDefaultAction} from '#/main/core/resource/utils'
import {actions} from '#/main/core/tools/resources/store'

const Tool = props =>
  <Page
    title={trans('resources', {}, 'tools')}
    subtitle={props.current && props.current.name}
    toolbar="edit rights publish unpublish | fullscreen more"
    actions={props.current && getActions(props.current, 'object')}
  >
    <PageContent>
      <ResourceExplorer
        root={props.root}
        current={props.current}
        primaryAction={(resourceNode) => {
          if ('directory' !== resourceNode.meta.type) {
            return getDefaultAction(resourceNode, 'object') // todo use action constant
          } else {
            // do not open directory, just change the target of the explorer
            return {
              label: trans('open', {}, 'actions'),
              type: 'callback',
              callback: () => props.changeDirectory(resourceNode)
            }
          }
        }}
      />
    </PageContent>
  </Page>

Tool.propTypes = {
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  changeDirectory: T.func.isRequired
}

const ResourcesTool = connect(
  state => ({
    root: state.root,
    current: state.current
  }),
  dispatch => ({
    changeDirectory(directoryNode) {
      dispatch(actions.changeDirectory(directoryNode))
    }
  })
)(Tool)

export {
  ResourcesTool
}
