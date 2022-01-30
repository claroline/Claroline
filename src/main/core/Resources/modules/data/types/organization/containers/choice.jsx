import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as registrationSelectors} from '#/main/app/security/registration/store/selectors'
import {selectors as profileSelectors} from '#/main/core/user/profile/store/selectors'
import {OrganizationChoice} from '#/main/core/data/types/organization/components/choice'

const ConnectedOrganizationChoice = connect(
  null,
  (dispatch) => (
    {
      updateMainOrganization({organizationName, organizationCode, formPath}) {
        const selectors = formPath ? registrationSelectors : profileSelectors
        
        dispatch(formActions.updateProp(selectors.FORM_NAME, 'mainOrganization.name', organizationName))
        dispatch(formActions.updateProp(selectors.FORM_NAME, 'mainOrganization.code', organizationCode))
      }
    }
  )
)(OrganizationChoice)

export {ConnectedOrganizationChoice as OrganizationChoice}
