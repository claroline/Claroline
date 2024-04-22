import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {makeCancelable} from '#/main/app/api'
import {Tool} from '#/main/core/tool'

import {getTabs} from '#/main/evaluation/evaluation'

import {EvaluationUser} from '#/main/evaluation/tools/evaluation/containers/user'
import {EvaluationUsers} from '#/main/evaluation/tools/evaluation/containers/users'
import {EvaluationEditor} from '#/main/evaluation/tools/evaluation/containers/editor'
import {ASYNC_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

const EvaluationTool = (props) => {
  const [pages, setPages] = useState([])

  useEffect(() => {
    const evaluationPages = makeCancelable(getTabs(props.contextType, props.permissions))

    evaluationPages.promise.then((loadedPages) => setPages(loadedPages.map(app => ({
      name: app.name,
      path: `/${app.name}`,
      component: app.component
    }))))

    return evaluationPages.cancel
  }, [props.contextType, JSON.stringify(props.permissions)])

  return (
    <Tool
      {...props}
      redirect={[
        {from: '/', exact: true, to: '/users'}
      ]}
      menu={[
        {
          name: 'users-progression',
          type: LINK_BUTTON,
          label: trans('users_progression', {}, 'evaluation'),
          target: props.path+'/users'
        }
      ].concat(pages.map(page => ({
        name: page.name,
        type: LINK_BUTTON,
        label: trans(page.name, {}, 'evaluation'),
        target: `${props.path}/${page.name}`
      })))}
      actions={[
        {
          name: 'initialize',
          type: ASYNC_BUTTON,
          icon: 'fa fa-fw fa-sync',
          label: trans('initialize_evaluations', {}, 'evaluation'),
          request: {
            url: ['apiv2_workspace_evaluations_init', {workspace: props.contextId}],
            request: {
              method: 'PUT'
            }
          },
          group: trans('management')
        }, {
          name: 'recompute',
          type: ASYNC_BUTTON,
          icon: 'fa fa-fw fa-calculator',
          label: trans('recompute_evaluations', {}, 'evaluation'),
          request: {
            url: ['apiv2_workspace_evaluations_recompute', {workspace: props.contextId}],
            request: {
              method: 'PUT'
            }
          },
          group: trans('management')
        }
      ]}
      pages={[
        {
          path: '/users',
          component: EvaluationUsers,
          exact: true
        }, {
          path: '/users/:userId/:workspaceId?',
          disabled: !props.canShowEvaluations && !props.canEdit,
          onEnter: (params = {}) => props.openEvaluation(params.workspaceId || props.contextId, params.userId),
          component: EvaluationUser
        }
      ].concat(pages)}
      editor={EvaluationEditor}
    />
  )
}

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
