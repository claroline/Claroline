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
      }
    ]}
    subtitle={trans('new_role', {}, 'community')}
  >
    <RoleForm
      className="mt-3"
      path={props.path}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

RoleCreate.propTypes = {
  path: T.string
}

export {
  RoleCreate
}
