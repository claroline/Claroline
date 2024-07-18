import React, {Component, createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import isUndefined from 'lodash/isUndefined'

import {trans, displayDate} from '#/main/app/intl'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import {PasswordInput} from '#/main/app/data/types/password/components/input'
import {FormGroup} from '#/main/app/content/form/components/group'
import {ContentRestriction} from '#/main/app/content/components/restriction'
import {ContentHtml} from '#/main/app/content/components/html'

import {MODAL_LOGIN} from '#/main/app/modals/login'
import {MODAL_REGISTRATION} from '#/main/app/modals/registration'

import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {getRestrictions} from '#/main/core/workspace/utils'
import {PageSection} from '#/main/app/page/components/section'
import {ContextPage} from '#/main/app/context/components/page'

class StandardRestrictions extends Component {
  constructor(props) {
    super(props)

    this.state = {
      codeAccess: ''
    }

    this.updateCodeAccess = this.updateCodeAccess.bind(this)
    this.submitCodeAccess = this.submitCodeAccess.bind(this)
  }

  updateCodeAccess(value) {
    this.setState({codeAccess: value})
  }

  submitCodeAccess() {
    if (this.state.codeAccess) {
      this.props.checkAccessCode(this.props.workspace, this.state.codeAccess)
    }
  }

  render() {
    const authenticated = !isEmpty(this.props.currentUser)
    const displayAuthenticationBtn = !authenticated
    const displayWsRegistrationBtn = authenticated && !this.props.errors.archived && this.props.errors.selfRegistration
    const displayPlatformRegistrationBtn = !authenticated && !this.props.errors.archived && this.props.platformSelfRegistration && this.props.errors.selfRegistration

    return (
      <>
        {get(this.props.workspace, 'meta.description') &&
          <PageSection size="md">
            <ContentHtml className="lead my-5">{get(this.props.workspace, 'meta.description')}</ContentHtml>
          </PageSection>
        }

        <PageSection size="md" className="py-4 bg-body-tertiary">
          <h2 className="h3 text-center">{trans('restricted_access')}</h2>
          <p className="lead text-body-secondary text-center">{trans('restricted_workspace.access_message', {}, 'workspace')}</p>

          {!this.props.errors.pendingRegistration &&
            <ContentRestriction
              icon="fa fa-fw fa-id-badge"
              failed={this.props.errors.noRights}
              onlyWarn={true}
              success={{
                title: classes({
                  [trans('restricted_workspace.you_are_manager', {}, 'workspace')]: this.props.managed,
                  [trans('restricted_workspace.can_access', {}, 'workspace')]: !this.props.managed && authenticated,
                  [trans('restricted_workspace.anonymous_can_access', {}, 'workspace')]: !this.props.managed && !authenticated
                }),
                help: classes({
                  [trans('restricted_workspace.manager_rights_access', {}, 'workspace')]: this.props.managed,
                  [trans('restricted_workspace.rights_access', {}, 'workspace')]: !this.props.managed && authenticated,
                  [trans('restricted_workspace.anonymous_rights_access', {}, 'workspace')]: !this.props.managed && !authenticated
                })
              }}
              fail={{
                title: trans('restricted_workspace.cannot_access', {}, 'workspace'),
                help: classes({
                  [trans('restricted_workspace.contact_manager', {
                    'manager_email': this.props.workspace.contactEmail ? `(<a href="mailto:${this.props.workspace.contactEmail}">${this.props.workspace.contactEmail}</a>)` : ''
                  }, 'workspace')]: !this.props.errors.selfRegistration,
                  [trans('restricted_workspace.self_registration', {}, 'workspace')]: this.props.errors.selfRegistration
                })
              }}
            >
              {(displayWsRegistrationBtn || displayAuthenticationBtn || displayPlatformRegistrationBtn) &&
                <div className="btn-toolbar gap-1 mt-3 justify-content-end">
                  {displayWsRegistrationBtn &&
                    <Button
                      className="btn btn-warning"
                      type={CALLBACK_BUTTON}
                      label={trans('restricted_workspace.self_register', {}, 'workspace')}
                      callback={() => this.props.selfRegister(this.props.workspace)}
                    />
                  }

                  {displayAuthenticationBtn &&
                    <Button
                      className="btn btn-warning"
                      type={MODAL_BUTTON}
                      label={trans('login', {}, 'actions')}
                      modal={[MODAL_LOGIN, {
                        onLogin: () => {
                          if (!this.props.errors.archived && this.props.errors.selfRegistration) {
                            this.props.selfRegister(this.props.workspace)
                          }
                        }
                      }]}
                      primary={true}
                    />
                  }

                  {displayPlatformRegistrationBtn &&
                    <Button
                      className="btn btn-outline-warning"
                      type={MODAL_BUTTON}
                      label={trans('create-account', {}, 'actions')}
                      modal={[MODAL_REGISTRATION, {
                        onRegister: () => this.props.selfRegister(this.props.workspace)
                      }]}
                    />
                  }

                  {!authenticated && !this.props.errors.archived && this.props.errors.selfRegistration &&
                    <small className="text-secondary">{trans('restricted_workspace.login_help', {}, 'workspace')}</small>
                  }
                </div>
              }
            </ContentRestriction>
          }

          {this.props.errors.pendingRegistration &&
            <ContentRestriction
              icon="fa fa-fw fa-id-badge"
              failed={this.props.errors.noRights}
              onlyWarn={true}
              success={{
                title: classes({
                  [trans('restricted_workspace.you_are_manager', {}, 'workspace')]: this.props.managed,
                  [trans('restricted_workspace.can_access', {}, 'workspace')]: !this.props.managed
                }),
                help: classes({
                  [trans('restricted_workspace.manager_rights_access', {}, 'workspace')]: this.props.managed,
                  [trans('restricted_workspace.rights_access', {}, 'workspace')]: !this.props.managed
                })
              }}
              fail={{
                title: this.props.managed ? trans('restricted_workspace.you_are_manager', {}, 'workspace') : trans('restricted_workspace.pending_registration', {}, 'workspace'),
                help: classes({
                  [trans('restricted_workspace.wait_validation', {}, 'workspace')]: !this.props.managed,
                  [trans('restricted_workspace.contact_manager', {
                    'manager_email': this.props.workspace.contactEmail ? `(<a href="mailto:${this.props.workspace.contactEmail}">${this.props.workspace.contactEmail}</a>)` : ''
                  }, 'workspace')]: !this.props.managed,
                  [trans('restricted_workspace.manager_rights_access', {}, 'workspace')]: this.props.managed
                })
              }}
            />
          }

          {this.props.errors.archived &&
            <ContentRestriction
              icon="fa fa-fw fa-eye"
              failed={this.props.errors.archived}
              fail={{
                title: trans('restricted_workspace.archived', {}, 'workspace'),
                help: trans('restricted_workspace.archived_help', {}, 'workspace')
              }}
            />
          }

          {(!isUndefined(this.props.errors.notStarted) || !isUndefined(this.props.errors.ended)) &&
            <ContentRestriction
              icon="fa fa-fw fa-calendar"
              failed={this.props.errors.notStarted || this.props.errors.ended}
              success={{
                title: trans('restricted_workspace.period_opened', {}, 'workspace'),
                help: (this.props.errors.startDate || this.props.errors.endDate) ? trans('restricted_workspace.period_opened_help', {
                  start: this.props.errors.startDate ? displayDate(this.props.errors.startDate, false, true) : `(${trans('empty_value')})`,
                  end: this.props.errors.endDate ? displayDate(this.props.errors.endDate, false, true) : `(${trans('empty_value')})`
                }, 'workspace') : trans('restricted_workspace.period_not_limited', {}, 'workspace')
              }}
              fail={{
                title: trans(this.props.errors.notStarted ? 'restricted_workspace.not_started' : 'restricted_workspace.ended', {}, 'workspace'),
                help: this.props.errors.notStarted ?
                  trans('restricted_workspace.not_started_help', {date: displayDate(this.props.errors.startDate, false, true)}, 'workspace') :
                  trans('restricted_workspace.ended_help', {date: displayDate(this.props.errors.endDate, false, true)}, 'workspace')
              }}
            />
          }

          {!isUndefined(this.props.errors.locked) &&
            <ContentRestriction
              icon="fa fa-fw fa-key"
              onlyWarn={true}
              failed={this.props.errors.locked}
              success={{
                title: trans('restricted_workspace.unlocked', {}, 'workspace'),
                help: ''
              }}
              fail={{
                title: trans('restricted_workspace.code_required', {}, 'workspace'),
                help: trans('restricted_workspace.enter_code', {}, 'workspace')
              }}
            >
              {this.props.errors.locked &&
                <div className="mt-3">
                  <FormGroup
                    id="access-code"
                    label={trans('access_code')}
                    hideLabel={true}
                  >
                    <PasswordInput
                      id="access-code"
                      value={this.state.codeAccess}
                      onChange={this.updateCodeAccess}
                      disablePasswordCheck={true}
                      hideStrength={true}
                    />
                  </FormGroup>

                  <Button
                    className="btn btn-warning w-100"
                    type={CALLBACK_BUTTON}
                    disabled={!this.state.codeAccess}
                    label={trans('unlock', {}, 'actions')}
                    callback={this.submitCodeAccess}
                  />
                </div>
              }
            </ContentRestriction>
          }
        </PageSection>
      </>
    )
  }
}

const WorkspaceForbidden = (props) => {
  const [restrictions, setRestrictions] = useState(undefined)

  useEffect(() => {
    getRestrictions(props.workspace, props.errors, props.managed).then((pluginRestrictions) => {
      setRestrictions(pluginRestrictions)
    })
  }, [props.workspace.id])

  return (
    <ContextPage root={true}>
      {!isUndefined(restrictions) && 0 !== restrictions.length &&
        createElement(restrictions[0].component, {
          workspace: props.workspace,
          errors: props.errors,
          managed: props.managed,
          currentUser: props.currentUser,
          dismiss: props.dismiss
        })
      }

      {!isUndefined(restrictions) && 0 === restrictions.length &&
        <StandardRestrictions {...props} />
      }
    </ContextPage>
  )
}

WorkspaceForbidden.propTypes = {
  currentUser: T.object,
  errors: T.shape({
    noRights: T.bool.isRequired,
    registered: T.bool,
    pendingRegistration: T.bool,
    archived: T.bool,
    selfRegistration: T.bool,
    locked: T.bool,
    invalidLocation: T.bool,
    notStarted: T.bool,
    startDate: T.string,
    ended: T.bool,
    endDate: T.string
  }).isRequired,
  workspace: T.shape(
    WorkspaceType.propTypes
  ),
  platformSelfRegistration: T.bool,
  checkAccessCode: T.func,
  selfRegister: T.func
}

export {
  WorkspaceForbidden
}
