import React from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {ContentCounter} from '#/main/app/content/components/counter'
import {ToolPage} from '#/main/core/tool/containers/page'

import {TopUsersChart} from '#/plugin/analytics/charts/top-users/containers/chart'
import {UsersChart} from '#/plugin/analytics/charts/users/containers/chart'

const CommunityTab = (props) =>
  <ToolPage
    subtitle={trans('community')}
  >
    <div className="row">
      <ContentCounter
        icon="fa fa-user"
        label={trans('users')}
        color={schemeCategory20c[1]}
        value={props.count.users}
      />

      <ContentCounter
        icon="fa fa-users"
        label={trans('groups')}
        color={schemeCategory20c[5]}
        value={props.count.groups}
      />

      <ContentCounter
        icon="fa fa-id-badge"
        label={trans('roles')}
        color={schemeCategory20c[9]}
        value={props.count.roles}
      />

      <ContentCounter
        icon="fa fa-building"
        label={trans('organizations')}
        color={schemeCategory20c[13]}
        value={props.count.organizations}
      />
    </div>

    <div className="row">
      <div className="col-md-4">
        <UsersChart url={['apiv2_admin_tool_analytics_users']} />
      </div>

      <div className="col-md-8">
        <TopUsersChart url={['apiv2_admin_tool_analytics_top_users']} />
      </div>
    </div>
  </ToolPage>

CommunityTab.propTypes = {
  count: T.shape({
    users: T.number,
    roles: T.number,
    groups: T.number,
    organizations: T.number
  }).isRequired
}

export {
  CommunityTab
}
