import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'
import {Tool} from '#/main/core/tool'

import {getTabs} from '#/main/evaluation/evaluation'

import {EvaluationUser} from '#/main/evaluation/tools/evaluation/containers/user'
import {EvaluationUsers} from '#/main/evaluation/tools/evaluation/containers/users'
import {EvaluationParameters} from '#/main/evaluation/tools/evaluation/containers/parameters'

const EvaluationTool = (props) =>
  <Tool {...props}>
    <Await
      for={getTabs(props.contextType, props.permissions)}
      placeholder={
        <ContentLoader
          size="lg"
          description={trans('loading', {}, 'tools')}
        />
      }
      then={(apps) => (
        <Routes
          path={props.path}
          redirect={[
            {from: '/', exact: true, to: '/users'}
          ]}
          routes={[
            {
              path: '/users',
              component: EvaluationUsers,
              exact: true
            }, {
              path: '/users/:userId/:workspaceId?',
              disabled: !props.canShowEvaluations && !props.canEdit,
              onEnter: (params = {}) => props.openEvaluation(params.workspaceId || props.contextId, params.userId),
              component: EvaluationUser
            }, {
              path: '/parameters',
              disabled: !props.canEdit || !props.contextId,
              component: EvaluationParameters
            }
          ].concat(apps.map(app => ({
            path: `/${app.name}`,
            component: app.component
          })))}
        />
      )}
    />
  </Tool>

EvaluationTool.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  canShowEvaluations: T.bool.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  currentUserId: T.string,
  permissions: T.object,
  openEvaluation: T.func.isRequired
}

export {
  EvaluationTool
}
