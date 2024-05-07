import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {makeCancelable} from '#/main/app/api'
import {Tool} from '#/main/core/tool'

import {getTabs} from '#/main/evaluation/evaluation'

import {EvaluationUser} from '#/main/evaluation/tools/evaluation/containers/user'
import {EvaluationUsers} from '#/main/evaluation/tools/evaluation/containers/users'
import {EvaluationEditor} from '#/main/evaluation/tools/evaluation/editor/components/main'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EvaluationAbout} from '#/main/evaluation/tools/evaluation/components/about'
import {EvaluationActivities} from '#/main/evaluation/tools/evaluation/containers/activities'

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
          name: 'about',
          type: LINK_BUTTON,
          label: trans('about'),
          target: props.path,
          exact: true
        }, {
          name: 'users',
          type: LINK_BUTTON,
          label: trans('users'),
          target: props.path+'/users'
        }, {
          name: 'activities',
          type: LINK_BUTTON,
          label: trans('activities'),
          target: props.path+'/activities',
          displayed: 'workspace' === props.contextType
        }
      ].concat(pages.map(page => ({
        name: page.name,
        type: LINK_BUTTON,
        label: trans(page.name, {}, 'evaluation'),
        target: `${props.path}/${page.name}`
      })))}
      pages={[
        {
          path: '/',
          component: EvaluationAbout,
          exact: true
        }, {
          path: '/activities',
          component: EvaluationActivities,
        }, {
          path: '/users',
          component: EvaluationUsers,
          exact: true
        }, {
          path: '/users/:userId/:workspaceId?',
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
  contextType: T.string.isRequired,
  contextId: T.string,
  currentUserId: T.string,
  permissions: T.object,
  openEvaluation: T.func.isRequired
}

export {
  EvaluationTool
}
