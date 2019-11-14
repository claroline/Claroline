import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormStepper} from '#/main/app/content/form/components/stepper'

import {Facet} from '#/main/app/security/registration/components/facet'
import {Required} from '#/main/app/security/registration/components/required'
import {Optional} from '#/main/app/security/registration/components/optional'
import {Organization} from '#/main/app/security/registration/components/organization'
import {Workspace} from '#/main/app/security/registration/components/workspace'
import {Registration} from '#/main/app/security/registration/components/registration'

class RegistrationMain extends Component {
  componentDidMount() {
    this.props.fetchRegistrationData()
  }

  render() {
    let steps = []

    if (!this.props.options.allowWorkspace && this.props.defaultWorkspaces) {
      steps.push({
        title: 'Registration',
        component: Registration
      })
    }

    steps = steps.concat([
      {
        title: 'Compte utilisateur',
        component: Required
      }, {
        title: 'Configuration',
        component: Optional
      }
    ], this.props.facets.map(facet => ({
      title: facet.title,
      component: () => {
        const currentFacet = <Facet facet={facet}/>

        return currentFacet
      }
    })))

    if (this.props.options.forceOrganizationCreation) {
      steps.push({
        title: 'Organization',
        component: Organization
      })
    }

    if (this.props.options.allowWorkspace) {
      steps.push({
        title: 'Workspace',
        component: Workspace
      })
    }

    return (
      <FormStepper
        submit={{
          type: CALLBACK_BUTTON,
          icon: 'fa fa-user-plus',
          label: trans('registration_confirm'),
          callback: () => this.props.register(this.props.user, this.props.termOfService, (user) => {
            this.props.onRegister(user)
            this.props.history.push('/login')
          })
        }}
        steps={steps}
      />
    )
  }
}

RegistrationMain.propTypes = {
  path: T.string,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
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
  fetchRegistrationData: T.func.isRequired,
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
