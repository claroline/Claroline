import React from 'react'

import {trans} from '#/main/core/translation'
import {DataTreeContainer} from '#/main/core/data/list/containers/data-tree.jsx'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list.jsx'
import {navigate} from '#/main/core/router'

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
        label: trans('add_sub_organization'),
        context: 'row',
        action: (row) => {
          navigate('organizations/form/parent/' + row[0].id)
        }
      }
    ]}
    card={OrganizationList.card}
  />

export {
  Organizations
}
