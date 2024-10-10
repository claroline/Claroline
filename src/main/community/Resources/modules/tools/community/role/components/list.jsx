import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/main/community/tools/community/role/store/selectors'
import {RoleList as BaseRoleList} from '#/main/community/role/components/list'
import {PageListSection} from '#/main/app/page/components/list-section'

const RoleList = props =>
  <ToolPage
    title={trans('roles', {}, 'community')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        label: trans('add_role', {}, 'actions'),
        icon: 'fa fa-fw fa-plus',
        target: `${props.path}/roles/new`,
        primary: true,
        displayed: props.canCreate
      }
    ]}
  >
    <PageListSection>
      <BaseRoleList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={!isEmpty(props.contextData) ?
          ['apiv2_workspace_list_roles_configurable', {workspace: props.contextData.id}] :
          ['apiv2_role_list']
        }
      />
    </PageListSection>
  </ToolPage>

RoleList.propTypes = {
  path: T.string,
  contextData: T.object,
  canCreate: T.bool.isRequired
}

export {
  RoleList
}
