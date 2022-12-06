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
      }, {
        type: LINK_BUTTON,
        label: trans('new_team', {}, 'community'),
        target: '' // current page, no need to add a link
      }
    ]}
    primaryAction="add"
    subtitle={trans('new_team', {}, 'community')}
    actions={[{
      name: 'add',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('add_team', {}, 'actions'),
      target: `${props.path}/teams/new`,
      primary: true
    }]}
  >
    <TeamForm path={`${props.path}/teams`} name={selectors.FORM_NAME} />
  </ToolPage>

TeamCreate.propTypes = {
  path: T.string
}

export {
  TeamCreate
}
