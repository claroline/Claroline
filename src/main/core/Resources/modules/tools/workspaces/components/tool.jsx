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
  <ToolPage
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_workspace'),
        displayed: props.canCreate,
        modal: [MODAL_WORKSPACE_CREATION]
      }
    ]}
    title={props.title}
  >
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
      props.invalidateList('workspaces.managed')
      //props.invalidateList('workspaces.models')
      //props.invalidateList('workspaces.archives')
    },
    update: () => {
      props.invalidateList('workspaces.registered')
      props.invalidateList('workspaces.public')
      props.invalidateList('workspaces.managed')
      //props.invalidateList('workspaces.models')
      //props.invalidateList('workspaces.archives')
    },
    delete: () => {
      props.invalidateList('workspaces.registered')
      props.invalidateList('workspaces.public')
      props.invalidateList('workspaces.managed')
      //props.invalidateList('workspaces.models')
      //props.invalidateList('workspaces.archives')
    }
  }

  return (
    <Tool
      {...props}
      redirect={[
        {from: '/', exact: true, to: '/registered', disabled: props.contextType === toolConstants.TOOL_PUBLIC || isEmpty(props.currentUser)},
        {from: '/', exact: true, to: '/public',     disabled: props.contextType !== toolConstants.TOOL_PUBLIC && !isEmpty(props.currentUser)}
      ]}
      menu={[
        {
          name: 'registered',
          type: LINK_BUTTON,
          label: trans('my_workspaces_menu', {}, 'workspace'),
          target: props.path+'/registered',
          displayed: !isEmpty(props.currentUser) && props.contextType !== toolConstants.TOOL_PUBLIC
        }, {
          name: 'all',
          type: LINK_BUTTON,
          label: trans('all_workspaces', {}, 'workspace'),
          target: props.path,
          exact: true
        }, {
          name: 'managed',
          type: LINK_BUTTON,
          label: trans('managed_workspaces_menu', {}, 'workspace'),
          target: props.path+'/managed',
          displayed: !isEmpty(props.currentUser) && props.contextType !== toolConstants.TOOL_PUBLIC && false
        }, {
          name: 'model',
          type: LINK_BUTTON,
          label: trans('models'),
          target: props.path+'/model',
          displayed: props.canCreate && props.contextType !== toolConstants.TOOL_PUBLIC && false
        }, {
          name: 'archive',
          type: LINK_BUTTON,
          label: trans('archives'),
          target: props.path+'/archived',
          displayed: props.canArchive && props.contextType !== toolConstants.TOOL_PUBLIC && false
        }
      ]}
      editor={WorkspacesEditor}
      pages={[
        {
          path: '/registered',
          disabled: isEmpty(props.currentUser) || props.contextType !== toolConstants.TOOL_DESKTOP,
          render: () => {
            const Registered = (
              <WorkspacesPage path={props.path} title={trans('my_workspaces', {}, 'workspace')} canCreate={props.canCreate}>
                <PageListSection>
                  <WorkspaceList
                    flush={true}
                    url={['apiv2_workspace_list_registered']}
                    name="workspaces.registered"
                    refresher={refresher}
                    display={{
                      current: listConst.DISPLAY_TILES_SM
                    }}
                  />
                </PageListSection>
              </WorkspacesPage>
            )

            return Registered
          }
        }, {
          path: '/',
          exact: true,
          render: () => {
            const PublicList = (
              <WorkspacesPage path={props.path} title={trans('all_workspaces', {}, 'workspace')} canCreate={props.canCreate}>
                <PageListSection>
                  <WorkspaceList
                    flush={true}
                    url={props.contextType === toolConstants.TOOL_PUBLIC ? ['apiv2_workspace_list_public'] : ['apiv2_workspace_list']}
                    name="workspaces.public"
                    refresher={refresher}
                    display={{
                      current: listConst.DISPLAY_TILES_SM
                    }}
                  />
                </PageListSection>
              </WorkspacesPage>
            )

            return PublicList
          }
        }, {
          path: '/managed',
          disabled: isEmpty(props.currentUser) || props.contextType !== toolConstants.TOOL_DESKTOP,
          render: () => {
            const ManagedList = (
              <WorkspacesPage path={props.path} title={trans('managed_workspaces', {}, 'workspace')} canCreate={props.canCreate}>
                <PageListSection>
                  <WorkspaceList
                    flush={true}
                    url={['apiv2_workspace_list_managed']}
                    name="workspaces.managed"
                    refresher={refresher}
                    display={{
                      current: listConst.DISPLAY_TILES_SM
                    }}
                  />
                </PageListSection>
              </WorkspacesPage>
            )

            return ManagedList
          }
        }/*, {
          path: '/model',
          disabled: !props.canCreate || props.contextType !== toolConstants.TOOL_DESKTOP,
          render: () => {
            const ModelList = (
              <WorkspacesPage path={props.path} title={trans('workspace_models', {}, 'workspace')} canCreate={props.canCreate}>
                <PageListSection>
                  <WorkspaceList
                    url={['apiv2_workspace_list_model']}
                    name="workspaces.models"
                    refresher={refresher}
                  />
                </PageListSection>
              </WorkspacesPage>
            )

            return ModelList
          }
        },*/ /*{
          path: '/archived',
          disabled: !props.canArchive || props.contextType !== toolConstants.TOOL_DESKTOP,
          render: () => {
            const ArchiveList = (
              <WorkspacesPage path={props.path} title={trans('workspace_archived', {}, 'workspace')} canCreate={props.canCreate}>
                <PageListSection>
                  <WorkspaceList
                    url={['apiv2_workspace_list_archive']}
                    name="workspaces.archives"
                    refresher={refresher}
                    customDefinition={[
                      {
                        name: 'meta.model',
                        label: trans('model'),
                        type: 'boolean',
                        alias: 'model'
                      }
                    ]}
                  />
                </PageListSection>
              </WorkspacesPage>
            )

            return ArchiveList
          }
        }*/
      ]}
    />
  )
}

WorkspacesTool.propTypes = {
  path: T.string.isRequired,
  currentUser: T.shape({
    // TODO : user types
  }),
  canCreate: T.bool.isRequired,
  contextType: T.string,
  canArchive: T.bool.isRequired,
  resetForm: T.func.isRequired,
  invalidateList: T.func.isRequired
}

export {
  WorkspacesTool
}
