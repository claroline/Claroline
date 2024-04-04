import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'

import {makeCancelable} from '#/main/app/api'
import {Tool} from '#/main/core/tool'

import {getTabs} from '#/main/evaluation/evaluation'

import {EvaluationUser} from '#/main/evaluation/tools/evaluation/containers/user'
import {EvaluationUsers} from '#/main/evaluation/tools/evaluation/containers/users'
import {EvaluationParameters} from '#/main/evaluation/tools/evaluation/containers/parameters'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

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
      }))).concat([{
        name: 'parameters',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('parameters'),
        target: props.path+'/parameters',
        displayed: props.canEdit && 'workspace' === props.contextType
      }])}
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
        }, {
          path: '/parameters',
          disabled: !props.canEdit || !props.contextId,
          component: EvaluationParameters
        }
      ].concat(pages)}
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
