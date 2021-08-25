import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isUndefined from 'lodash/isUndefined'

import {trans, displayDate} from '#/main/app/intl'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import {PageFull} from '#/main/app/page/components/full'
import {PasswordInput} from '#/main/app/data/types/password/components/input'
import {ContentHelp} from '#/main/app/content/components/help'
import {ContentRestriction} from '#/main/app/content/components/restriction'

import {MODAL_LOGIN} from '#/main/app/modals/login'
import {MODAL_REGISTRATION} from '#/main/app/modals/registration'

import {constants as toolConstants} from '#/main/core/tool/constants'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'

class WorkspaceRestrictions extends Component {
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
      this.props.checkAccessCode(this.state.codeAccess)
    }
  }

  render() {
    return (
      <PageFull
        showBreadcrumb={showToolBreadcrumb(toolConstants.TOOL_WORKSPACE, this.props.workspace)}
        path={getToolBreadcrumb(null, toolConstants.TOOL_WORKSPACE, this.props.workspace)}
        title={this.props.workspace.name}
        poster={this.props.workspace.poster ? this.props.workspace.poster.url : undefined}
      >
        <div className="access-restrictions">
          <h2>{trans('restricted_access')}</h2>
          <p>{trans('restricted_workspace.access_message', {}, 'workspace')}</p>

          {!this.props.errors.pendingRegistration &&
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
                title: trans('restricted_workspace.cannot_access', {}, 'workspace'),
                help: classes({
                  [trans('restricted_workspace.contact_manager', {
                    'manager_email': this.props.workspace.contactEmail ? `(<a href="mailto:${this.props.workspace.contactEmail}">${this.props.workspace.contactEmail}</a>)` : ''
                  }, 'workspace')]: !this.props.errors.selfRegistration,
                  [trans('restricted_workspace.self_registration', {}, 'workspace')]: this.props.errors.selfRegistration
                })
              }}
            >
              {this.props.authenticated && !this.props.errors.archived && this.props.errors.selfRegistration &&
                <Button
                  style={{marginTop: 20}}
                  className="btn btn-block btn-emphasis"
                  type={CALLBACK_BUTTON}
                  label={trans('restricted_workspace.self_register', {}, 'workspace')}
                  callback={this.props.selfRegister}
                  primary={true}
                />
              }

              {!this.props.authenticated &&
                <Button
                  style={{marginTop: 20}}
                  className="btn btn-block btn-emphasis"
                  type={MODAL_BUTTON}
                  label={trans('login', {}, 'actions')}
                  modal={[MODAL_LOGIN, {
                    onLogin: () => {
                      if (!this.props.errors.archived && this.props.errors.selfRegistration) {
                        this.props.selfRegister()
                      } else {
                        this.props.reload()
                      }
                    }
                  }]}
                  primary={true}
                />
              }

              {!this.props.authenticated && !this.props.errors.archived && this.props.platformSelfRegistration && this.props.errors.selfRegistration &&
                <Button
                  className="btn btn-block"
                  type={MODAL_BUTTON}
                  label={trans('self_register', {}, 'actions')}
                  modal={[MODAL_REGISTRATION, {
                    onRegister: this.props.selfRegister
                  }]}
                />
              }

              {!this.props.authenticated && !this.props.errors.archived && this.props.errors.selfRegistration &&
                <ContentHelp
                  help="Vous serez automatiquement inscrit à l'espace d'activités après votre connexion ou inscription."
                />
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
                <Fragment>
                  <PasswordInput
                    id="access-code"
                    value={this.state.codeAccess}
                    onChange={this.updateCodeAccess}
                  />

                  <Button
                    className="btn btn-block btn-emphasis"
                    type={CALLBACK_BUTTON}
                    icon="fa fa-fw fa-sign-in-alt"
                    disabled={!this.state.codeAccess}
                    label={trans('restricted_workspace.access', {}, 'workspace')}
                    callback={this.submitCodeAccess}
                    primary={true}
                  />
                </Fragment>
              }
            </ContentRestriction>
          }

          {!isUndefined(this.props.errors.invalidLocation) &&
            <ContentRestriction
              icon="fa fa-fw fa-laptop"
              onlyWarn={true}
              failed={this.props.errors.invalidLocation}
              success={{
                title: trans('restricted_workspace.authorized_ip', {}, 'workspace'),
                help: ''
              }}
              fail={{
                title: trans('restricted_workspace.authorized_ip_required', {}, 'workspace'),
                help: trans('restricted_workspace.use_authorized_ip', {}, 'workspace')
              }}
            />
          }

          {this.props.managed &&
            <Button
              className="btn btn-block btn-emphasis"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-sign-in-alt"
              label={trans('restricted_workspace.access', {}, 'workspace')}
              callback={this.props.dismiss}
              primary={true}
            />
          }

          {this.props.managed &&
            <ContentHelp
              help={trans('restricted_workspace.manager_info', {}, 'workspace')}
            />
          }
        </div>
      </PageFull>
    )
  }
}

WorkspaceRestrictions.propTypes = {
  authenticated: T.bool.isRequired,
  managed: T.bool.isRequired,
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
  dismiss: T.func.isRequired,
  checkAccessCode: T.func,
  selfRegister: T.func,
  reload: T.func.isRequired
}

export {
  WorkspaceRestrictions
}
