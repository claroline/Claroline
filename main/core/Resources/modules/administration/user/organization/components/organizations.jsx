import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {TreeData} from '#/main/app/content/tree/containers/data'

import {OrganizationCard} from '#/main/core/user/data/components/organization-card'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list'

const Organizations = () =>
  <TreeData
    name="organizations.list"
    primaryAction={OrganizationList.open}
    fetch={{
      url: ['apiv2_organization_list_recursive'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_organization_delete_bulk'],
      displayed: (rows) => 0 !== rows.filter(organization => !organization.meta.default).length
    }}
    definition={OrganizationList.definition}
    actions={(rows) => [
      {
        type: 'link',
        icon: 'fa fa-fw fa-plus',
        label: trans('add_sub_organization'),
        scope: ['object'],
        target: 'organizations/form/parent/' + rows[0].id
      }
    ]}
    card={OrganizationCard}
  />

export {
  Organizations
}
