import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {DOWNLOAD_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ProgressionUsers} from '#/plugin/analytics/tools/dashboard/progression/components/users'
import {ProgressionUser} from '#/plugin/analytics/tools/dashboard/progression/containers/user'
import {ProgressionParameters} from '#/plugin/analytics/tools/dashboard/progression/containers/parameters'
import {ProgressionRequirements} from '#/plugin/analytics/tools/dashboard/progression/containers/requirements'

const ProgressionMain = (props) =>
  <ToolPage
    subtitle={trans('progression')}
    actions={[
      {
        name: 'configure',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('configure', {}, 'actions'),
        target: `${props.path}/progression/parameters`,
        primary: true,
        displayed: props.canConfigure
      }, {
        name: 'download-progression',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-progression', {}, 'actions'),
        file: {
          url: ['apiv2_workspace_evaluation_csv', {workspaceId: props.workspaceId}]
        },
        group: trans('transfer')
      }
    ]}
  >
    {!props.canConfigure && props.currentUserId &&
      <ProgressionUser
        userId={props.currentUserId}
        workspaceId={props.workspaceId}
      />
    }

    {props.canConfigure &&
      <Routes
        path={props.path + '/progression'}
        routes={[
          {
            path: '/',
            exact: true,
            render: () => {
              const Users = (
                <ProgressionUsers path={props.path} workspaceId={props.workspaceId} />
              )

              return Users
            }
          }, {
            path: '/parameters',
            exact: true,
            component: ProgressionParameters
          }, {
            path: '/parameters/:id',
            component: ProgressionRequirements,
            onEnter: (params) => props.openRequirements(params.id),
            onLeave: () => props.resetRequirements()
          }, {
            path: '/:userId',
            render: (routeProps) => {
              const User = (
                <ProgressionUser
                  userId={routeProps.match.params.userId}
                  workspaceId={props.workspaceId}
                  backAction={{
                    type: LINK_BUTTON,
                    target: props.path + '/progression',
                    exact: true
                  }}
                />
              )

              return User
            }
          }
        ]}
      />
    }
  </ToolPage>

ProgressionMain.propTypes = {
  path: T.string.isRequired,
  workspaceId: T.string.isRequired,
  canConfigure: T.bool.isRequired,
  currentUserId: T.string,
  openRequirements: T.func.isRequired,
  resetRequirements: T.func.isRequired
}

export {
  ProgressionMain
}
