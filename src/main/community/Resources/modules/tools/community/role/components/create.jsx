import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {RoleForm} from '#/main/community/role/components/form'
import {selectors} from '#/main/community/tools/community/role/store'

const RoleCreate = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('roles', {}, 'community'),
        target: `${props.path}/roles`
      }, {
        type: LINK_BUTTON,
        label: trans('new_role', {}, 'community'),
        target: '' // current page, no need to add a link
      }
    ]}
    primaryAction="add"
    subtitle={trans('new_role', {}, 'community')}
    actions={[{
      name: 'add',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('add_role'),
      target: `${props.path}/roles/new`,
      primary: true
    }]}
  >
    <RoleForm path={`${props.path}/roles`} name={selectors.FORM_NAME} />
  </ToolPage>

RoleCreate.propTypes = {
  path: T.string
}

export {
  RoleCreate
}
