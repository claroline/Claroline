import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {url} from '#/main/app/api'
import {select as listSelect} from '#/main/app/content/list/store'
import {LINK_BUTTON, DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {
  matchPath,
  withRouter,
  Routes
} from '#/main/app/router'

import {trans} from '#/main/core/translation'
import {actions as logActions} from  '#/main/core/layout/logs/actions'
import {
  PageActions,
  PageAction,
  MoreAction
} from '#/main/core/layout/page/components/page-actions'
import {Logs} from '#/main/core/resource/logs/log/components/log-list'
import {UserLogs} from '#/main/core/resource/logs/log/components/user-list'
import {LogDetails} from '#/main/core/layout/logs'

const LogTabActionsComponent = (props) => {
  let moreActions = []
  if (matchPath(props.location.pathname, {path: '/log', exact: true})) {
    moreActions = moreActions.concat([
      {
        type: LINK_BUTTON,
        target: '/log/users/logs',
        label: trans('user_tracking', {}, 'log'),
        icon: 'fa fa-users'
      }, {
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_resource_logs_list_csv', {'resourceId': props.resourceId}]) + props.logsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download'
      }
    ])
  }

  if (matchPath(props.location.pathname, {path: '/log/users/logs', exact: true})) {
    moreActions = moreActions.concat([
      {
        type: LINK_BUTTON,
        target: '/log',
        exact: true,
        label: trans('list', {}, 'platform'),
        icon: 'fa fa-list'
      }, {
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_resource_logs_list_users_csv', {'resourceId': props.resourceId}]) + props.usersQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-fw fa-download'
      }
    ])
  }
  return (
    <PageActions>
      {
        matchPath(props.location.pathname, {path: '/log/:id'}) &&
        <PageAction
          id={'back-to-list'}
          label={trans('back')}
          icon={'fa fa-share fa-flip-horizontal'}
          type={LINK_BUTTON}
          target={'/log'}
          exact={true}
        />
      }
      {moreActions.length > 0 &&
        <MoreAction actions={moreActions} />
      }
    </PageActions>
  )
}

LogTabActionsComponent.propTypes = {
  location: T.object.isRequired,
  resourceId: T.number.isRequired,
  logsQuery: T.string,
  usersQuery: T.string
}

const LogTabActions = withRouter(connect(
  state => ({
    resourceId: state.resourceId,
    logsQuery: listSelect.queryString(listSelect.list(state, 'logs')),
    usersQuery: listSelect.queryString(listSelect.list(state, 'userActions'))
  })
)(LogTabActionsComponent))

const LogTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/log',
        component: Logs,
        exact: true
      }, {
        path: '/log/:id',
        component: LogDetails,
        exact: true,
        onEnter: (params) => props.openLog(params.id, props.resourceId)
      }, {
        path: '/log/users/logs',
        component: UserLogs,
        exact: true
      }
    ]}
  />

LogTabComponent.propTypes = {
  resourceId: T.number.isRequired,
  openLog: T.func.isRequired
}

const LogTab = connect(
  state => ({
    resourceId: state.resourceId
  }),
  dispatch => ({
    openLog(id, resourceId) {
      dispatch(logActions.openLog('apiv2_resource_logs_get', {id, resourceId}))
    }
  })
)(LogTabComponent)

export {
  LogTabActions,
  LogTab
}
