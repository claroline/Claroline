import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Tool} from '#/main/core/tool'

import {EvaluationUser} from '#/main/evaluation/tools/evaluation/containers/user'
import {EvaluationUsers} from '#/main/evaluation/tools/evaluation/containers/users'
import {EvaluationEditor} from '#/main/evaluation/tools/evaluation/editor/components/main'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EvaluationAbout} from '#/main/evaluation/tools/evaluation/components/about'
import {EvaluationActivities} from '#/main/evaluation/tools/evaluation/containers/activities'

const EvaluationTool = (props) => {
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
      ]}
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
      ]}
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
