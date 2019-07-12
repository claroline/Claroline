import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {FormStepper} from '#/main/core/layout/form/components/form-stepper'

import {Facet} from '#/main/app/security/registration/components/facet'
import {Required} from '#/main/app/security/registration/components/required'
import {Optional} from '#/main/app/security/registration/components/optional'
import {Organization} from '#/main/app/security/registration/components/organization'
import {Workspace} from '#/main/app/security/registration/components/workspace'
import {Registration} from '#/main/app/security/registration/components/registration'

const RegistrationMain = props => {
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
    <FormStepper
      path={props.path}
      location={props.location}
      submit={{
        icon: 'fa fa-user-plus',
        label: trans('registration_confirm'),
        action: () => props.register(props.user, props.termOfService, props.onRegister)
      }}
      steps={steps}
      redirect={[
        {from: '/', exact: true, to: !props.options.allowWorkspace && props.defaultWorkspaces ? '/registration' : '/account'}
      ]}
    />
  )
}

RegistrationMain.propTypes = {
  path: T.string,
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
  defaultWorkspaces: T.array,
  onRegister: T.func
}

export {
  RegistrationMain
}
