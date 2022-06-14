import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {EvaluationUser} from '#/main/evaluation/tools/evaluation/containers/user'
import {EvaluationUsers} from '#/main/evaluation/tools/evaluation/containers/users'
import {EvaluationParameters} from '#/main/evaluation/tools/evaluation/containers/parameters'

const EvaluationTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/users', disabled: props.currentUserId && props.contextId}
    ]}
    routes={[
      {
        path: '/',
        exact: true,
        disabled: !props.currentUserId || !props.contextId,
        render: () => (
          <ToolPage subtitle={trans('my_progression')}>
            <EvaluationUser
              userId={props.currentUserId}
              workspaceId={props.contextId}
            />
          </ToolPage>
        )
      }, {
        path: '/users',
        component: EvaluationUsers,
        disabled: !props.canShowEvaluations && !props.canEdit,
        exact: true
      }, {
        path: '/users/:userId/:workspaceId?',
        disabled: !props.canShowEvaluations && !props.canEdit,
        render: (routeProps) => (
          <ToolPage subtitle={trans('users_progression', {}, 'evaluation')}>
            <EvaluationUser
              userId={routeProps.match.params.userId}
              workspaceId={routeProps.match.params.workspaceId || props.contextId}
              backAction={{
                type: LINK_BUTTON,
                target: props.path + '/users',
                exact: true
              }}
            />
          </ToolPage>
        )
      }, {
        path: '/parameters',
        disabled: !props.canEdit || !props.contextId,
        component: EvaluationParameters
      }
    ]}
  />

EvaluationTool.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  canShowEvaluations: T.bool.isRequired,
  contextId: T.string,
  currentUserId: T.string
}

export {
  EvaluationTool
}
