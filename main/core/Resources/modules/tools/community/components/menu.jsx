import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {User as UserType} from '#/main/core/user/prop-types'
import {constants} from '#/main/core/tools/community/constants'
import {getPermissionLevel} from  '#/main/core/tools/community/permissions'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {constants as toolConstants} from '#/main/core/tool/constants'

const CommunityMenu = (props) => {
  const permLevel = getPermissionLevel(props.currentUser, props.workspace)

  return (
    <MenuSection
      {...omit(props, 'path')}
      title={trans('community', {}, 'tools')}
    >
      <Toolbar
        className="list-group"
        buttonName="list-group-item"
        actions={[
          {
            name: 'users',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-user',
            label: trans('users'),
            target: `${props.path}/users`,
            displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model')
          }, {
            name: 'groups',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-users',
            label: trans('groups'),
            target: `${props.path}/groups`,
            displayed: props.contextType === toolConstants.TOOL_WORKSPACE && !get(props.workspace, 'meta.model')
          }, {
            name: 'roles',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-id-badge',
            label: trans('roles'),
            target: `${props.path}/roles`,
            displayed: props.contextType === toolConstants.TOOL_WORKSPACE && permLevel !== constants.READ_ONLY
          }, {
            name: 'pending',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-user-plus',
            label: trans('pending_registrations'),
            target: `${props.path}/pending`,
            displayed: props.contextType === toolConstants.TOOL_WORKSPACE && permLevel !== constants.READ_ONLY && get(props.workspace, 'registration.selfRegistration') && get(props.workspace, 'registration.validation')
          }, {
            name: 'parameters',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-cog',
            label: trans('parameters'),
            target: `${props.path}/parameters`,
            displayed: props.contextType === toolConstants.TOOL_WORKSPACE && permLevel !== constants.READ_ONLY
          }
        ]}
      />
    </MenuSection>
  )
}

CommunityMenu.propTypes = {
  contextType: T.string,
  path: T.string,
  currentUser: T.shape(
    UserType.propTypes
  ),
  workspace: T.object
}

export {
  CommunityMenu
}
