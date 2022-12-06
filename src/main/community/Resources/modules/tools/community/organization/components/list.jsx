import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/main/community/tools/community/organization/store'
import {OrganizationList as BaseOrganizationList} from '#/main/community/organization/components/list'

const OrganizationList = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('organizations', {}, 'community'),
      target: `${props.path}/organizations`
    }]}
    subtitle={trans('organizations', {}, 'community')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_organization', {}, 'actions'),
        target: `${props.path}/organizations/new`,
        primary: true,
        displayed: props.canCreate
      }
    ]}
  >
    <BaseOrganizationList
      path={props.path}
      name={selectors.LIST_NAME}
      url={['apiv2_organization_list']}
      tree={true}
      customActions={(rows) => [
        {
          name: 'add',
          type: 'link',
          icon: 'fa fa-fw fa-plus',
          label: trans('add_sub_organization', {}, 'actions'),
          target: props.path+'/organizations/new/' + rows[0].id,
          displayed: hasPermission('edit', rows[0]),
          scope: ['object'],
          group: trans('management')
        }
      ]}
    />
  </ToolPage>

OrganizationList.propTypes = {
  path: T.string.isRequired,
  canCreate: T.bool.isRequired
}

export {
  OrganizationList
}

