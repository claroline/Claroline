import React from 'react'

import {t} from '#/main/core/translation'

import {DataTreeContainer} from '#/main/core/data/list/containers/data-tree.jsx'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list.jsx'

const Organizations = () =>
  <DataTreeContainer
    name="organizations.list"
    open={OrganizationList.open}
    fetch={{
      url: ['apiv2_organization_list_recursive'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_organization_delete_bulk'],
      displayed: (organizations) => 0 !== organizations.filter(organization => !organization.meta.default).length
    }}
    definition={OrganizationList.definition}
    actions={[
      {
        icon: 'fa fa-fw fa-plus',
        label: t('add_sub_organization'),
        context: 'row',
        action: () => {
          // todo open orga form
        }
      }
    ]}
    card={OrganizationList.card}
  />

export {
  Organizations
}
