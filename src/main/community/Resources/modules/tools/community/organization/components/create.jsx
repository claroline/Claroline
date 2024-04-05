import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'

import {OrganizationForm} from '#/main/community/organization/components/form'
import {selectors} from '#/main/community/tools/community/organization/store'

const OrganizationCreate = (props) =>
  <ToolPage
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('organizations', {}, 'community'),
        target: `${props.path}/organizations`
      }
    ]}
    title={trans('new_organization', {}, 'community')}
  >
    <OrganizationForm
      className="mt-3"
      path={props.path}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

OrganizationCreate.propTypes = {
  path: T.string
}

export {
  OrganizationCreate
}
