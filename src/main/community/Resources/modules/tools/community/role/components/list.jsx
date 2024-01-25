import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/main/community/tools/community/role/store/selectors'
import {RoleList as BaseRoleList} from '#/main/community/role/components/list'
import {ContentSizing} from '#/main/app/content/components/sizing'

const RoleList = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('roles', {}, 'community'),
      target: `${props.path}/roles`
    }]}
    subtitle={trans('roles', {}, 'community')}
    /*primaryAction="add"*/
    primaryAction={
      {
        name: 'add',
        type: LINK_BUTTON,
        label: trans('add_role', {}, 'actions'),
        icon: 'fa fa-fw fa-plus',
        target: `${props.path}/roles/new`,
        primary: true,
        displayed: props.canCreate
      }
    }
  >
    <ContentSizing size="full">
      <BaseRoleList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={!isEmpty(props.contextData) ?
          ['apiv2_workspace_list_roles_configurable', {workspace: props.contextData.id}] :
          ['apiv2_role_list']
        }
      />
    </ContentSizing>
  </ToolPage>

RoleList.propTypes = {
  path: T.string,
  contextData: T.object,
  canCreate: T.bool.isRequired
}

export {
  RoleList
}
