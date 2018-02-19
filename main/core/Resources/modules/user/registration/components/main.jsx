import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'

import {PageContainer, PageHeader} from '#/main/core/layout/page/index'
import {FormStepper} from '#/main/core/layout/form/components/form-stepper.jsx'
import {actions as formActions} from '#/main/core/data/form/actions'
import {select as formSelect} from '#/main/core/data/form/selectors'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CONFIRM} from '#/main/core/layout/modal'

import {Facet} from '#/main/core/user/registration/components/facet.jsx'
import {Required} from '#/main/core/user/registration/components/required.jsx'
import {Optional} from '#/main/core/user/registration/components/optional.jsx'
import {Organization} from '#/main/core/user/registration/components/organization.jsx'

import {select} from '#/main/core/user/registration/selectors'

const RegistrationForm = props => {
  const steps = [].concat([
    {
      path: '/account',
      title: 'Compte utilisateur',
      component: Required
    }, {
      path: '/options',
      title: 'Configuration',
      component: Optional
    }
  ], props.facets.map(facet => ({
    path: `/${facet.id}`,
    title: facet.title,
    component: () => {
      const currentFacet = <Facet facet={facet}/>

      return currentFacet
    }
  })))

  if (props.options.forceOrganizationCreation) {
    steps.push({
      path: '/organization',
      title: 'Organization',
      component: Organization
    })
  }

  return (
    <PageContainer id="user-registration">
      <PageHeader
        key="header"
        title={t('user_registration')}
      />
      <FormStepper
        key="form"
        className="page-content"
        submit={{
          icon: 'fa fa-user-plus',
          label: t('registration_confirm'),
          action: () => props.register(props.user, props.termOfService)
        }}
        steps={steps}
        redirect={[
          {from: '/', exact: true, to: '/account'}
        ]}
      />
    </PageContainer>
  )
}

RegistrationForm.propTypes = {
  user: T.shape({
    // user type
  }).isRequired,
  organization: T.shape({
    // organization type
  }).isRequired,
  facets: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })),
  termOfService: T.string,
  register: T.func.isRequired,
  options: T.shape({
    forceOrganizationCreation: T.bool
  }).isRequired
}

const UserRegistration = connect(
  (state) => ({
    user: formSelect.data(formSelect.form(state, 'user')),
    facets: select.facets(state),
    termOfService: select.termOfService(state),
    options: select.options(state)
  }),
  (dispatch) => ({
    register(user, termOfService) {
      if (termOfService) {
        // show terms before create new account
        dispatch(modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-copyright',
          title: t('term_of_service'),
          question: termOfService,
          isHtml: true,
          confirmButtonText: t('accept_term_of_service'),
          handleConfirm: () => {
            // todo : set acceptedTerms flag
            dispatch(formActions.saveForm('user', ['apiv2_user_create_and_login']))
          }
        }))
      } else {
        // create new account
        dispatch(formActions.saveForm('user', ['apiv2_user_create_and_login']))
      }
    }
  })
)(RegistrationForm)

export {
  UserRegistration
}
