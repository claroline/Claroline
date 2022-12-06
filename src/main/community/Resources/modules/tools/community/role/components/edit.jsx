import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {RolePage} from '#/main/community/role/components/page'
import {Role as RoleTypes} from '#/main/community/role/prop-types'

import {RoleForm} from '#/main/community/role/components/form'
import {selectors} from '#/main/community/tools/community/role/store/selectors'

const RoleEdit = (props) =>
  <RolePage
    path={props.path}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('edition'),
        target: '' // current page, link is not needed
      }
    ]}
    role={props.role}
    reload={props.reload}
  >
    <RoleForm
      path={`${props.path}/roles/${props.role ? props.role.id : ''}`}
      name={selectors.FORM_NAME}
    />
  </RolePage>

RoleEdit.propTypes = {
  path: T.string.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ),

  workspace: T.object,
  shortcuts: T.array,

  reload: T.func.isRequired
}

export {
  RoleEdit
}
