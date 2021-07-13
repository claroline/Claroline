import {connect} from 'react-redux'

import {actions as registrationActions} from '#/main/app/security/registration/store/actions'
import {actions as formActions} from '#/main/app/content/form/store'
import {selectors} from '#/main/app/security/registration/store/selectors'
import {OrganizationSelection} from '#/main/app/security/registration/components/organization-selection'

const ConnectedOrganizationSelection = connect(
  (state) => (
    {
      allOrganizations: selectors.existingOrganizations(state)
    }
  ), (dispatch) => (
    {
      getOrganizations() {
        dispatch(registrationActions.fetchExistingOrganizationsData())
      },
      updateMainOrganization(organizationName, organizationCode) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, 'mainOrganization.name', organizationName))
        dispatch(formActions.updateProp(selectors.FORM_NAME, 'mainOrganization.code', organizationCode))
      }
    }
  )
)(OrganizationSelection)

export {ConnectedOrganizationSelection as OrganizationSelection}