import React from 'react'

import {trans} from '#/main/core/translation'
import {DataTreeContainer} from '#/main/core/data/list/containers/data-tree.jsx'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list.jsx'

// TODO : upgrade to DataCard format

const Organizations = () =>
  <DataTreeContainer
    name="organizations.list"
    primaryAction={OrganizationList.open}
    fetch={{
      url: ['apiv2_organization_list_recursive'],
      autoload: true
    }}
    deleteAction={(rows) => ({
      type: 'url',
      target: ['apiv2_organization_delete_bulk'],
      displayed: 0 !== rows.filter(organization => !organization.meta.default).length
    })}
    definition={OrganizationList.definition}
    actions={(rows) => [
      {
        type: 'link',
        icon: 'fa fa-fw fa-plus',
        label: trans('add_sub_organization'),
        context: 'row',
        target: 'organizations/form/parent/' + rows[0].id
      }
    ]}
    card={(row) => ({
      icon: 'fa fa-building',
      title: row.name,
      subtitle: row.code,
      flags: [
        row.meta.default && ['fa fa-check', trans('default')]
      ].filter(flag => !!flag)
    })}
  />

export {
  Organizations
}
