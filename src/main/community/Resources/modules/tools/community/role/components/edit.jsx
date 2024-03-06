import React from 'react'
import {PropTypes as T} from 'prop-types'

import {RolePage} from '#/main/community/role/components/page'
import {Role as RoleTypes} from '#/main/community/role/prop-types'

import {RoleForm} from '#/main/community/role/components/form'
import {selectors} from '#/main/community/tools/community/role/store/selectors'

const RoleEdit = (props) =>
  <RolePage
    path={props.path}
    role={props.role}
    reload={props.reload}
  >
    <RoleForm
      path={props.path}
      name={selectors.FORM_NAME}
    />
  </RolePage>

RoleEdit.propTypes = {
  path: T.string.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  RoleEdit
}
