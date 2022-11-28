import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {OrganizationForm} from '#/main/community/organization/components/form'
import {selectors} from '#/main/community/tools/community/organization/store'

const OrganizationCreate = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('organizations', {}, 'community'),
        target: `${props.path}/organizations`
      }, {
        type: LINK_BUTTON,
        label: trans('new_organization', {}, 'community'),
        target: '' // current page, no need to add a link
      }
    ]}
    primaryAction="add"
    subtitle={trans('new_organization', {}, 'community')}
    actions={[{
      name: 'add',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('add_organization', {}, 'actions'),
      target: `${props.path}/organizations/new`,
      primary: true
    }]}
  >
    <OrganizationForm path={`${props.path}/organizations`} name={selectors.FORM_NAME} />
  </ToolPage>

OrganizationCreate.propTypes = {
  path: T.string
}

export {
  OrganizationCreate
}
