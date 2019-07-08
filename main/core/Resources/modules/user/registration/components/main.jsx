import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'

import {PageContainer, PageHeader} from '#/main/core/layout/page/index'
import {FormStepper} from '#/main/core/layout/form/components/form-stepper'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {Facet} from '#/main/core/user/registration/components/facet'
import {Required} from '#/main/core/user/registration/components/required'
import {Optional} from '#/main/core/user/registration/components/optional'
import {Organization} from '#/main/core/user/registration/components/organization'
import {Workspace} from '#/main/core/user/registration/components/workspace'
import {Registration} from '#/main/core/user/registration/components/registration'

import {select} from '#/main/core/user/registration/selectors'

const RegistrationForm = props => {
  let steps = []

  if (!props.options.allowWorkspace && props.defaultWorkspaces) {
    steps.push({
      path: '/registration',
      title: 'Registration',
      component: Registration
    })
  }

  steps = steps.concat([
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

  if (props.options.allowWorkspace) {
    steps.push({
      path: '/workspace',
      title: 'Workspace',
      component: Workspace
    })
  }

  return (
    <PageContainer id="user-registration">
      <PageHeader
        key="header"
        title={trans('user_registration')}
      />
      <FormStepper
        key="form"
        className="page-content"
        location={props.location}
        submit={{
          icon: 'fa fa-user-plus',
          label: trans('registration_confirm'),
          action: () => props.register(props.user, props.termOfService)
        }}
        steps={steps}
        redirect={[
          {from: '/', exact: true, to: !props.options.allowWorkspace && props.defaultWorkspaces ? '/registration': '/account'}
        ]}
      />
    </PageContainer>
  )
}

RegistrationForm.propTypes = {
  location: T.shape({
    path: T.string
  }),
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
    forceOrganizationCreation: T.bool,
    allowWorkspace: T.bool
  }).isRequired,
  defaultWorkspaces: T.array
}

const UserRegistration = withRouter(connect(
  (state) => ({
    user: formSelect.data(formSelect.form(state, 'user')),
    facets: select.facets(state),
    termOfService: select.termOfService(state),
    options: select.options(state),
    workspaces: select.workspaces(state),
    defaultWorkspaces: select.defaultWorkspaces(state)
  }),
  (dispatch) => ({
    register(user, termOfService) {
      if (termOfService) {
        dispatch(modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-copyright',
          title: trans('term_of_service'),
          question: termOfService,
          isHtml: true,
          confirmButtonText: trans('accept_term_of_service'),
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
)(RegistrationForm))

export {
  UserRegistration
}
