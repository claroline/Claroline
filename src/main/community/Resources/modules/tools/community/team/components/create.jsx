import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {TeamForm} from '#/main/community/team/components/form'
import {selectors} from '#/main/community/tools/community/team/store'

const TeamCreate = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('teams', {}, 'community'),
        target: `${props.path}/teams`
      }
    ]}
    subtitle={trans('new_team', {}, 'community')}
  >
    <TeamForm
      className="mt-3"
      path={props.path}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

TeamCreate.propTypes = {
  path: T.string
}

export {
  TeamCreate
}
