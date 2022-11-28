import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {GroupForm} from '#/main/community/group/components/form'
import {selectors} from '#/main/community/tools/community/group/store'

const GroupCreate = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('groups', {}, 'community'),
        target: `${props.path}/groups`
      }, {
        type: LINK_BUTTON,
        label: trans('new_group', {}, 'community'),
        target: '' // current page, no need to add a link
      }
    ]}
    primaryAction="add"
    subtitle={trans('new_group', {}, 'community')}
    actions={[{
      name: 'add',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('add_group', {}, 'actions'),
      target: `${props.path}/groups/new`,
      primary: true
    }]}
  >
    <GroupForm path={`${props.path}/groups`} name={selectors.FORM_NAME} />
  </ToolPage>

GroupCreate.propTypes = {
  path: T.string
}

export {
  GroupCreate
}
