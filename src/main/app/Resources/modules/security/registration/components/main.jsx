import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {FormStepper} from '#/main/app/content/form/components/stepper'

import {Facet} from '#/main/app/security/registration/components/facet'
import {Required} from '#/main/app/security/registration/components/required'
import {Optional} from '#/main/app/security/registration/components/optional'
import {Organization} from '#/main/app/security/registration/components/organization'
import {Workspace} from '#/main/app/security/registration/components/workspace'
import {Registration} from '#/main/app/security/registration/components/registration'
import {OrganizationSelection} from '#/main/app/security/registration/components/organization-selection'

import {constants} from '#/main/app/security/registration/constants'

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
        title: trans('my_account'),
        component: Required
      }, {
        title: 'Configuration',
        component: Optional
      }
    ], this.props.facets.map(facet => ({
      title: facet.title,
      render: () => {
        const currentFacet = <Facet facet={facet} allFields={this.props.allFacetFields} user={this.props.user} />

        return currentFacet
      }
    })))

    if (constants.ORGANIZATION_SELECTION_CREATE === this.props.options.organizationSelection) {
      steps.push({
        title: trans('organization'),
        component: Organization
      })
    } else if (constants.ORGANIZATION_SELECTION_SELECT === this.props.options.organizationSelection) {
      steps.push({
        title: trans('organization'),
        component: OrganizationSelection
      })
    }

    if (this.props.options.allowWorkspace) {
      steps.push({
        title: trans('workspaces'),
        component: Workspace
      })
    }

    return (
      <FormStepper
        submit={{
          type: CALLBACK_BUTTON,
          label: trans('create-account', {}, 'actions'),
          confirm: {
            title: trans('registration'),
            message: trans('register_confirm_message'),
            button: trans('registration_confirm'),
            additional: constants.REGISTRATION_MAIL_VALIDATION_NONE !== this.props.options.validation ? (
              <div className="modal-body">
                <Alert type="info">
                  {trans('registration_mail_help')}
                </Alert>

                {constants.REGISTRATION_MAIL_VALIDATION_FULL === this.props.options.validation &&
                  <Alert type="warning">
                    {trans('registration_validation_help')}
                  </Alert>
                }
              </div>
            ) : undefined
          },
          callback: () => this.props.register(this.props.user, this.props.termOfService, (user) => {
            this.props.onRegister(user)
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
  facets: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })),
  termOfService: T.string,
  register: T.func.isRequired,
  fetchRegistrationData: T.func.isRequired,
  options: T.shape({
    validation: T.bool,
    allowWorkspace: T.bool,
    organizationSelection: T.string
  }).isRequired,
  defaultWorkspaces: T.array,
  allFacetFields: T.array,
  onRegister: T.func
}

export {
  RegistrationMain
}
