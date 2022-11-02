import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {TreeData} from '#/main/app/content/tree/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {OrganizationCard} from '#/main/core/user/data/components/organization-card'
import {OrganizationList} from '#/main/community/administration/community/organization/components/organization-list'

const OrganizationsList = (props) =>
  <TreeData
    name={`${baseSelectors.STORE_NAME}.organizations.list`}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/organizations/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    fetch={{
      url: ['apiv2_organization_list_recursive'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_organization_delete_bulk'],
      disabled: (rows) => 0 === rows.filter(organization => !organization.meta.default).length
    }}
    definition={OrganizationList.definition}
    actions={(rows) => [
      {
        name: 'add',
        type: 'link',
        icon: 'fa fa-fw fa-plus',
        label: trans('add_sub_organization'),
        scope: ['object'],
        target: props.path+'/organizations/form/parent/' + rows[0].id
      }
    ]}
    card={OrganizationCard}
  />

OrganizationsList.propTypes = {
  path: T.string.isRequired
}

const Organizations = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(OrganizationsList)

export {
  Organizations
}
