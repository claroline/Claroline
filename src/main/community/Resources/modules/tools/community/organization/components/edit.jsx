import React from 'react'
import {PropTypes as T} from 'prop-types'

import {OrganizationPage} from '#/main/community/organization/components/page'
import {Organization as OrganizationTypes} from '#/main/community/organization/prop-types'
import {OrganizationForm} from '#/main/community/organization/components/form'

import {selectors} from '#/main/community/tools/community/organization/store/selectors'

const OrganizationEdit = (props) =>
  <OrganizationPage
    path={props.path}
    organization={props.organization}
    reload={props.reload}
  >
    <OrganizationForm
      className="mt-3"
      path={props.path}
      name={selectors.FORM_NAME}
    />
  </OrganizationPage>

OrganizationEdit.propTypes = {
  path: T.string.isRequired,
  organization: T.shape(
    OrganizationTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  OrganizationEdit
}
