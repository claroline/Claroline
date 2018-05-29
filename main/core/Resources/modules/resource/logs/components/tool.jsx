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
  PageActions,
  MoreAction,
  PageAction,
  PageHeader
} from '#/main/core/layout/page'
import {
  RoutedPageContainer,
  RoutedPageContent
} from '#/main/core/layout/router'

// app pages
import {Logs} from '#/main/core/resource/logs/components/log-list.jsx'
import {UserLogs} from '#/main/core/resource/logs/components/user-list.jsx'
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
        icon: 'fa fa-users'
      },
      {
        type: 'download',
        file: {
          url: url(['apiv2_resource_logs_list_csv', {'resourceId': props.resourceId}]) + props.logsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download'
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
        icon: 'fa fa-list'
      },
      {
        type: 'download',
        file: {
          url: url(['apiv2_resource_logs_list_users_csv', {'resourceId': props.resourceId}]) + props.usersQuery
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
  resourceId: T.number.isRequired,
  location: T.object.isRequired,
  logsQuery: T.string,
  usersQuery: T.string
}

const ToolActions = withRouter(Actions)

const Tool = (props) =>
  <RoutedPageContainer>
    <PageHeader title={trans('logs', {}, 'tools')}>
      <ToolActions
        resourceId={props.resourceId}
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
          onEnter: (params) => props.openLog(params.id, props.resourceId)
        }, {
          path: '/users',
          component: UserLogs,
          exact: true
        }
      ]}
    />
  </RoutedPageContainer>

Tool.propTypes = {
  resourceId: T.number.isRequired,
  openLog: T.func.isRequired,
  logsQuery: T.string,
  usersQuery: T.string
}

const ToolContainer = connect(
  state => ({
    resourceId: state.resourceId,
    logsQuery: select.queryString(select.list(state, 'logs')),
    usersQuery: select.queryString(select.list(state, 'userActions'))
  }),
  dispatch => ({
    openLog(id, resourceId) {
      dispatch(logActions.openLog('apiv2_resource_logs_get', {id, resourceId}))
    }
  })
)(Tool)

export {
  ToolContainer as LogTool
}
