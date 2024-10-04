import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {Tool, constants as toolConstants} from '#/main/core/tool'
import {ToolPage} from '#/main/core/tool'

import {WorkspaceList} from '#/main/core/workspace/components/list'
import {PageListSection} from '#/main/app/page/components/list-section'
import {WorkspacesEditor} from '#/main/core/tools/workspaces/editor/containers/main'
import {MODAL_WORKSPACE_CREATION} from '#/main/core/workspace/modals/creation'
import {constants as listConst} from '#/main/app/content/list/constants'

const WorkspacesPage = (props) =>
  <ToolPage title={props.title}>
    {props.children}
  </ToolPage>

WorkspacesPage.propTypes = {
  path: T.string,
  title: T.string.isRequired,
  canCreate: T.bool.isRequired,
  children: T.any
}

const WorkspacesTool = (props) => {
  // we invalidate all the workspaces list when we execute an action on one or many workspaces
  // because actions can make the ws appear/disappear from multiple mounted lists (e.g. archive or unarchive)
  // and this is not predictable.
  const refresher = {
    add: () => {
      props.invalidateList('workspaces.registered')
      props.invalidateList('workspaces.public')
    },
    update: () => {
      props.invalidateList('workspaces.registered')
      props.invalidateList('workspaces.public')
    },
    delete: () => {
      props.invalidateList('workspaces.registered')
      props.invalidateList('workspaces.public')
    }
  }

  return (
    <Tool
      {...props}
      redirect={[
        {from: '/', exact: true, to: '/registered', disabled: props.contextType === toolConstants.TOOL_PUBLIC}
      ]}
      menu={[
        {
          name: 'registered',
          type: LINK_BUTTON,
          label: trans('my_workspaces_menu', {}, 'workspace'),
          target: props.path+'/registered',
          displayed: props.contextType !== toolConstants.TOOL_PUBLIC
        }, {
          name: 'all',
          type: LINK_BUTTON,
          label: trans('all_workspaces', {}, 'workspace'),
          target: props.path,
          exact: true
        }
      ]}
      editor={WorkspacesEditor}
      pages={[
        {
          path: '/registered',
          disabled: props.contextType !== toolConstants.TOOL_DESKTOP,
          render: () => (
            <ToolPage path={props.path} title={trans('my_workspaces', {}, 'workspace')}>
              <PageListSection>
                <WorkspaceList
                  flush={true}
                  url={['apiv2_workspace_list_registered']}
                  name="workspaces.registered"
                  refresher={refresher}
                  display={{
                    current: listConst.DISPLAY_TILES_SM
                  }}
                  addAction={{
                    name: 'add',
                    type: MODAL_BUTTON,
                    // icon: 'fa fa-fw fa-plus',
                    label: trans('add_workspace'),
                    displayed: props.canCreate,
                    modal: [MODAL_WORKSPACE_CREATION]
                  }}
                />
              </PageListSection>
            </ToolPage>
          )
        }, {
          path: '/',
          exact: true,
          render: () => (
            <ToolPage title={trans('all_workspaces', {}, 'workspace')}>
              <PageListSection>
                <WorkspaceList
                  flush={true}
                  url={props.contextType === toolConstants.TOOL_PUBLIC ? ['apiv2_workspace_list_public'] : ['apiv2_workspace_list']}
                  name="workspaces.public"
                  refresher={refresher}
                  display={{
                    current: listConst.DISPLAY_TILES_SM
                  }}
                  addAction={{
                    name: 'add',
                    type: MODAL_BUTTON,
                    // icon: 'fa fa-fw fa-plus',
                    label: trans('add_workspace'),
                    displayed: props.canCreate,
                    modal: [MODAL_WORKSPACE_CREATION]
                  }}
                />
              </PageListSection>
            </ToolPage>
          )
        }
      ]}
    />
  )
}

WorkspacesTool.propTypes = {
  path: T.string.isRequired,
  canCreate: T.bool.isRequired,
  contextType: T.string,
  invalidateList: T.func.isRequired
}

export {
  WorkspacesTool
}
