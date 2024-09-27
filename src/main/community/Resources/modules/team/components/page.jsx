import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageTitle} from '#/main/app/page/components/title'
import {ToolPage} from '#/main/core/tool'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/team/utils'
import {Team as TeamTypes} from '#/main/community/team/prop-types'

const Team = (props) =>
  <ToolPage
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('teams', {}, 'community'),
        target: `${props.path}/teams`
      }
    ].concat(props.team ? props.breadcrumb : [])}
    poster={get(props.team, 'poster')}
    title={trans('team_name', {name: get(props.team, 'name', trans('loading'))}, 'community')}
    description={get(props.team, 'meta.description')}
  >
    {isEmpty(props.team) &&
      <ContentLoader
        size="lg"
        description={trans('team_loading', {}, 'community')}
      />
    }

    {!isEmpty(props.team) &&
      <PageTitle
        size="md"
        title={get(props.team, 'name', trans('loading'))}
        primaryAction="edit"
        actions={!isEmpty(props.team) ? getActions([props.team], {
          add: () => props.reload(props.team.id),
          update: () => props.reload(props.team.id),
          delete: () => props.reload(props.team.id)
        }, props.path, props.currentUser) : []}
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
