import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {TeamPage} from '#/main/community/team/components/page'
import {Team as TeamTypes} from '#/main/community/team/prop-types'
import {TeamForm} from '#/main/community/team/components/form'

import {selectors} from '#/main/community/tools/community/team/store/selectors'

const TeamEdit = (props) =>
  <TeamPage
    path={props.path}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('edition'),
        target: '' // current page, link is not needed
      }
    ]}
    team={props.team}
    reload={props.reload}
  >
    <TeamForm
      path={`${props.path}/teams/${props.team ? props.team.id : ''}`}
      name={selectors.FORM_NAME}
    />
  </TeamPage>

TeamEdit.propTypes = {
  path: T.string.isRequired,
  team: T.shape(
    TeamTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  TeamEdit
}
