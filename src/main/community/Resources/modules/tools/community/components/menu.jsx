import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {User as UserType} from '#/main/community/prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {constants as toolConstants} from '#/main/core/tool/constants'

const CommunityMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('community', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'activity',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-wave-square',
          label: trans('activity'),
          target: `${props.path}/activity`,
          displayed: props.canShowActivity && (props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model'))
        }, {
          name: 'users',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user',
          label: trans('users', {}, 'community'),
          target: `${props.path}/users`,
          displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model')
        }, {
          name: 'groups',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-users',
          label: trans('groups', {}, 'community'),
          target: `${props.path}/groups`,
          displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model')
        }, {
          name: 'pending',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user-plus',
          label: trans('pending_registrations'),
          target: `${props.path}/pending`,
          displayed: props.contextType === toolConstants.TOOL_WORKSPACE && props.canEdit && get(props.workspace, 'registration.selfRegistration') && get(props.workspace, 'registration.validation')
        }, {
          name: 'teams',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user-group',
          label: trans('teams', {}, 'community'),
          target: `${props.path}/teams`,
          displayed: props.contextType === toolConstants.TOOL_WORKSPACE
        }, {
          name: 'organizations',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-building',
          label: trans('organizations'),
          target: `${props.path}/organizations`,
          displayed: props.contextType === toolConstants.TOOL_DESKTOP/* && props.canEdit*/
        }, {
          name: 'roles',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-id-badge',
          label: trans('roles', {}, 'community'),
          target: `${props.path}/roles`,
          displayed: props.canEdit
        }, {
          name: 'profile',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user-circle',
          label: trans('user_profile'),
          target: `${props.path}/parameters/profile`,
          displayed: props.contextType === toolConstants.TOOL_DESKTOP && props.canEdit
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

CommunityMenu.propTypes = {
  contextType: T.string,
  path: T.string,
  currentUser: T.shape(
    UserType.propTypes
  ),
  workspace: T.object,
  canEdit: T.bool.isRequired,
  canShowActivity: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  CommunityMenu
}
