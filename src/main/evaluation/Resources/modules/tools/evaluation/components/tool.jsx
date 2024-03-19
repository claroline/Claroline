import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'

import {trans} from '#/main/app/intl/translation'
import {theme} from '#/main/theme/config'
import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool/containers/page'

import {getTabs} from '#/main/evaluation/evaluation'

import {EvaluationUser} from '#/main/evaluation/tools/evaluation/containers/user'
import {EvaluationUsers} from '#/main/evaluation/tools/evaluation/containers/users'
import {EvaluationParameters} from '#/main/evaluation/tools/evaluation/containers/parameters'

const EvaluationTool = (props) =>
  <Await
    for={getTabs(props.contextType, props.permissions)}
    placeholder={
      <ContentLoader
        size="lg"
        description={trans('loading', {}, 'tools')}
      />
    }
    then={(apps) => (
      <Fragment>
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

        <Helmet>
          {apps.map(app => app.styles && app.styles.map(styles => (
            <link key={styles} rel="stylesheet" type="text/css" href={theme(styles)} />
          )))}
        </Helmet>
      </Fragment>
    )}
  />

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
