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
import {ContentLoader} from '#/main/app/content/components/loader'

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
      }
    ].concat(props.team ? props.breadcrumb : [])}
    title={get(props.team, 'name', trans('loading'))}
    toolbar="edit | fullscreen more"
    poster={get(props.team, 'poster')}
    actions={!isEmpty(props.team) ? getActions([props.team], {
      add: () => props.reload(props.team.id),
      update: () => props.reload(props.team.id),
      delete: () => props.reload(props.team.id)
    }, props.path, props.currentUser) : []}
  >
    {isEmpty(props.team) &&
      <ContentLoader
        size="lg"
        description={trans('team_loading', {}, 'community')}
      />
    }

    {!isEmpty(props.team) && props.children}
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
