import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/main/community/tools/community/organization/store'
import {OrganizationList as BaseOrganizationList} from '#/main/community/organization/components/list'
import {PageListSection} from '#/main/app/page/components/list-section'

const OrganizationList = (props) =>
  <ToolPage
    title={trans('organizations', {}, 'community')}
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
    <PageListSection>
      <BaseOrganizationList
        path={props.path}
        name={selectors.LIST_NAME}
        url={['apiv2_organization_list']}
      />
    </PageListSection>
  </ToolPage>

OrganizationList.propTypes = {
  path: T.string.isRequired,
  canCreate: T.bool.isRequired
}

export {
  OrganizationList
}

