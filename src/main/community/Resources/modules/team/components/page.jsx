import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/team/utils'
import {route} from '#/main/community/team/routing'
import {Team as TeamTypes} from '#/main/community/team/prop-types'

const Team = (props) =>
  <ToolPage
    className="team-page"
    meta={{
      title: trans('team_name', {name: get(props.team, 'name', trans('loading'))}, 'community'),
      description: get(props.team, 'meta.description')
    }}
    path={[
      {
        type: LINK_BUTTON,
        label: trans('teams', {}, 'community'),
        target: `${props.path}/teams`
      }, {
        type: LINK_BUTTON,
        label: get(props.team, 'name', trans('loading')),
        target: !isEmpty(props.team) ? route(props.team, props.path) : ''
      }
    ].concat(props.team ? props.breadcrumb : [])}
    subtitle={trans('team_name', {name: get(props.team, 'name', trans('loading'))}, 'community')}
    toolbar="edit | fullscreen more"
    poster={get(props.team, 'poster')}
    actions={!isEmpty(props.team) ? getActions([props.team], {
      add: props.reload,
      update: props.reload,
      delete: props.reload
    }, props.path, props.currentUser) : []}
  >
    {props.children}
  </ToolPage>

Team.propTypes = {
  path: T.string,
  breadcrumb: T.array,
  team: T.shape(
    TeamTypes.propTypes
  ),
  currentUser: T.object,
  children: T.any,
  reload: T.func
}

Team.defaultProps = {
  breadcrumb: []
}

const TeamPage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(Team)

export {
  TeamPage
}
