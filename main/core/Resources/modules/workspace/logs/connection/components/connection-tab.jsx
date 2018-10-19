import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {url} from '#/main/app/api'
import {select as listSelect} from '#/main/app/content/list/store'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {trans} from '#/main/app/intl/translation'
// import {select as workspaceSelect} from '#/main/core/workspace/selectors'
import {
  PageActions,
  MoreAction
} from '#/main/core/layout/page/components/page-actions'
import {Connections} from '#/main/core/workspace/logs/connection/components/connections'

const ConnectionTabActionsComponent = (props) =>
  <PageActions>
    <MoreAction
      actions={[
        {
          type: DOWNLOAD_BUTTON,
          file: {
            url: url(['apiv2_log_connect_workspace_list_csv', {workspace: props.workspaceId}]) + props.queryString
          },
          label: trans('download_csv_list', {}, 'log'),
          icon: 'fa fa-download'
        }
      ]}
    />
  </PageActions>

ConnectionTabActionsComponent.propTypes = {
  workspaceId: T.string,
  queryString: T.string
}

const ConnectionTabActions = connect(
  state => ({
    workspaceId: state.workspaceId,
    // workspaceUuid: workspaceSelect.workspace(state).uuid,
    queryString: listSelect.queryString(listSelect.list(state, 'connections.list'))
  })
)(ConnectionTabActionsComponent)

const ConnectionTab = () =>
  <Routes
    routes={[
      {
        path: '/connections',
        component: Connections,
        exact: true
      }
    ]}
  />

export {
  ConnectionTabActions,
  ConnectionTab
}
