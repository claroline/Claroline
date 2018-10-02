import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {url} from '#/main/app/api'
import {select} from '#/main/app/content/list/store'
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
import {Logs} from '#/main/core/administration/logs/log/components/log-list'
import {UserLogs} from '#/main/core/administration/logs/log/components/user-list'
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
          url: url(['apiv2_admin_tool_logs_list_csv']) + props.logsQuery
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
          url: url(['apiv2_admin_tool_logs_list_users_csv']) + props.usersQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download'
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
  logsQuery: T.string,
  usersQuery: T.string
}

const LogTabActions = withRouter(connect(
  state => ({
    logsQuery: select.queryString(select.list(state, 'logs')),
    usersQuery: select.queryString(select.list(state, 'userActions'))
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
        onEnter: (params) => props.openLog(params.id)
      }, {
        path: '/log/users/logs',
        component: UserLogs,
        exact: true
      }
    ]}
  />

LogTabComponent.propTypes = {
  openLog: T.func.isRequired
}

const LogTab = connect(
  null,
  dispatch => ({
    openLog(id) {
      dispatch(logActions.openLog('apiv2_admin_tool_logs_get', {id}))
    }
  })
)(LogTabComponent)

export {
  LogTabActions,
  LogTab
}
