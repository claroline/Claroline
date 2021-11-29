import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {ToolPage} from '#/main/core/tool/containers/page'
import {WorkspaceList} from '#/main/core/workspace/components/list'
import {WorkspaceCreation} from '#/main/core/tools/workspaces/containers/creation'

const WorkspacesTool = (props) => {
  // we invalidate all the workspaces list when we execute an action on one or many workspaces
  // because actions can make the ws appear/disappear from multiple mounted lists (eg. archive or unarchive)
  // and this is not predictable.
  const refresher = {
    add: () => {
      props.invalidateList('workspaces.registered')
      props.invalidateList('workspaces.public')
      props.invalidateList('workspaces.managed')
      props.invalidateList('workspaces.models')
      props.invalidateList('workspaces.archives')
    },
    update: () => {
      props.invalidateList('workspaces.registered')
      props.invalidateList('workspaces.public')
      props.invalidateList('workspaces.managed')
      props.invalidateList('workspaces.models')
      props.invalidateList('workspaces.archives')
    },
    delete: () => {
      props.invalidateList('workspaces.registered')
      props.invalidateList('workspaces.public')
      props.invalidateList('workspaces.managed')
      props.invalidateList('workspaces.models')
      props.invalidateList('workspaces.archives')
    }
  }

  return (
    <ToolPage
      primaryAction="add"
      actions={[
        {
          name: 'add',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('create_workspace', {}, 'workspace'),
          target: `${props.path}/new`,
          primary: true,
          displayed: props.canCreate
        }
      ]}
      subtitle={
        <Routes
          path={props.path}
          routes={[
            {path: '/new',        render: () => trans('new_workspace', {}, 'workspace'), disabled: !props.canCreate},
            {path: '/registered', render: () => trans('my_workspaces', {}, 'workspace')},
            {path: '/public',     render: () => trans('public_workspaces', {}, 'workspace')},
            {path: '/managed',    render: () => trans('managed_workspaces', {}, 'workspace')},
            {path: '/model',      render: () => trans('workspace_models', {}, 'workspace'), disabled: !props.canCreate}
          ]}
        />
      }
    >
      <Routes
        path={props.path}
        routes={[
          {
            path: '/new',
            disabled: !props.canCreate,
            component: WorkspaceCreation,
            onEnter: () => props.resetForm('workspaces.creation', merge({}, WorkspaceType.defaultProps, {meta: {creator: props.currentUser}}))
          }, {
            path: '/registered',
            disabled: isEmpty(props.currentUser),
            render: () => {
              const Registered = (
                <WorkspaceList
                  url={['apiv2_workspace_list_registered']}
                  name="workspaces.registered"
                  refresher={refresher}
                />
              )

              return Registered
            }
          }, {
            path: '/public',
            render: () => {
              const PublicList = (
                <WorkspaceList
                  url={['apiv2_workspace_list_registerable']}
                  name="workspaces.public"
                  refresher={refresher}
                />
              )

              return PublicList
            }
          }, {
            path: '/managed',
            disabled: isEmpty(props.currentUser),
            render: () => {
              const ManagedList = (
                <WorkspaceList
                  url={['apiv2_workspace_list_managed']}
                  name="workspaces.managed"
                  refresher={refresher}
                />
              )

              return ManagedList
            }
          }, {
            path: '/model',
            disabled: !props.canCreate,
            render: () => {
              const ModelList = (
                <WorkspaceList
                  url={['apiv2_workspace_list_model']}
                  name="workspaces.models"
                  refresher={refresher}
                />
              )

              return ModelList
            }
          }, {
            path: '/archived',
            disabled: !props.canArchive,
            render: () => {
              const ArchiveList = (
                <WorkspaceList
                  url={['apiv2_workspace_list_archive']}
                  name="workspaces.archives"
                  refresher={refresher}
                />
              )

              return ArchiveList
            }
          }
        ]}

        redirect={[
          {from: '/', exact: true, to: '/registered', disabled: isEmpty(props.currentUser)},
          {from: '/', exact: true, to: '/public',     disabled: !isEmpty(props.currentUser)}
        ]}
      />
    </ToolPage>
  )
}

WorkspacesTool.propTypes = {
  path: T.string.isRequired,
  currentUser: T.shape({
    // TODO : user types
  }),
  canCreate: T.bool.isRequired,
  canArchive: T.bool.isRequired,
  resetForm: T.func.isRequired,
  invalidateList: T.func.isRequired
}

export {
  WorkspacesTool
}
