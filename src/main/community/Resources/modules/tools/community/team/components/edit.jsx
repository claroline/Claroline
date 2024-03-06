import React from 'react'
import {PropTypes as T} from 'prop-types'

import {TeamPage} from '#/main/community/team/components/page'
import {Team as TeamTypes} from '#/main/community/team/prop-types'
import {TeamForm} from '#/main/community/team/components/form'

import {selectors} from '#/main/community/tools/community/team/store/selectors'

const TeamEdit = (props) =>
  <TeamPage
    path={props.path}
    team={props.team}
    reload={props.reload}
  >
    <TeamForm
      className="mt-3"
      path={props.path}
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
