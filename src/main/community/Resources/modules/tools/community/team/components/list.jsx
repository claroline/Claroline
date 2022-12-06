import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {TeamList as BaseTeamList} from '#/main/community/team/components/list'
import {selectors} from '#/main/community/tools/community/team/store/selectors'

const TeamList = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('teams', {}, 'community'),
      target: `${props.path}/teams`
    }]}
    subtitle={trans('teams', {}, 'community')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_team', {}, 'actions'),
        target: `${props.path}/teams/new`,
        displayed: props.canCreate,
        primary: true
      }
    ]}
  >
    <BaseTeamList
      path={props.path}
      name={selectors.LIST_NAME}
      url={['apiv2_workspace_team_list', {id: props.contextData.id}]}
    />
  </ToolPage>

TeamList.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,
  canCreate: T.bool.isRequired
}

export {
  TeamList
}
