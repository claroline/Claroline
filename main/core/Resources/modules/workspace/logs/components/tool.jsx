import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {select} from '#/main/core/data/list/selectors'
import {url} from '#/main/app/api'
import {
  matchPath,
  withRouter
} from '#/main/app/router'
import {
  PageContainer,
  PageActions,
  MoreAction,
  PageAction,
  PageHeader
} from '#/main/core/layout/page'
import {
  RoutedPageContent
} from '#/main/core/layout/router'

// app pages
import {Logs} from '#/main/core/workspace/logs/components/log-list.jsx'
import {UserLogs} from '#/main/core/workspace/logs/components/user-list.jsx'
import {LogDetails} from '#/main/core/layout/logs'
import {actions as logActions} from  '#/main/core/layout/logs/actions'

const Actions = (props) => {
  let moreActions = []
  if (matchPath(props.location.pathname, {path: '/', exact: true})) {
    moreActions = moreActions.concat([
      {
        type: 'link',
        target: '/users',
        label: trans('user_tracking', {}, 'log'),
        icon: 'fa fa-fw fa-users'
      },
      {
        type: 'download',
        file: {
          url: url(['apiv2_workspace_tool_logs_list_csv', {'workspaceId': props.workspaceId}]) + props.logsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-fw fa-download'
      }
    ])
  }
  
  if (matchPath(props.location.pathname, {path: '/users', exact: true})) {
    moreActions = moreActions.concat([
      {
        type: 'link',
        target: '/',
        exact: true,
        label: trans('list', {}, 'platform'),
        icon: 'fa fa-fw fa-list'
      },
      {
        type: 'download',
        file: {
          url: url(['apiv2_workspace_tool_logs_list_users_csv', {'workspaceId': props.workspaceId}]) + props.usersQuery
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
          title={trans('back')}
          icon={'fa fa-share fa-flip-horizontal'}
          type={'link'}
          target={'/'}
          exact={true}
        />
      }
      {moreActions.length > 0 && <MoreAction actions = {moreActions}/>}
    </PageActions>
  )
}
Actions.propTypes = {
  workspaceId: T.number.isRequired,
  location: T.object.isRequired,
  logsQuery: T.string,
  usersQuery: T.string
}

const ToolActions = withRouter(Actions)

const Tool = (props) =>
  <PageContainer>
    <PageHeader title={trans('logs', {}, 'tools')}>
      <ToolActions
        workspaceId={props.workspaceId}
        logsQuery={props.logsQuery}
        usersQuery={props.usersQuery}
      />
    </PageHeader>
    <RoutedPageContent
      routes={[
        {
          path: '/',
          component: Logs,
          exact: true
        }, {
          path: '/log/:id',
          component: LogDetails,
          exact: true,
          onEnter: (params) => props.openLog(params.id, props.workspaceId)
        }, {
          path: '/users',
          component: UserLogs,
          exact: true
        }
      ]}
    />
  </PageContainer>

Tool.propTypes = {
  workspaceId: T.number.isRequired,
  openLog: T.func.isRequired,
  logsQuery: T.string,
  usersQuery: T.string
}

const ToolContainer = connect(
  state => ({
    workspaceId: state.workspaceId,
    logsQuery: select.queryString(select.list(state, 'logs')),
    usersQuery: select.queryString(select.list(state, 'userActions'))
  }),
  dispatch => ({
    openLog(id, workspaceId) {
      dispatch(logActions.openLog('apiv2_workspace_tool_logs_get', {id, workspaceId}))
    }
  })
)(Tool)

export {
  ToolContainer as LogTool
}
