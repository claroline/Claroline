import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isUndefined from 'lodash/isUndefined'

import {trans} from '#/main/app/intl/translation'
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
          <p>{trans('restricted_workspace.access_message')}</p>

          {!this.props.errors.pendingRegistration &&
            <ContentRestriction
              icon="fa fa-fw fa-id-badge"
              failed={this.props.errors.noRights}
              onlyWarn={true}
              success={{
                title: classes({
                  [trans('restricted_workspace.you_are_manager')]: this.props.managed,
                  [trans('restricted_workspace.can_access')]: !this.props.managed
                }),
                help: classes({
                  [trans('restricted_workspace.manager_rights_access')]: this.props.managed,
                  [trans('restricted_workspace.rights_access')]: !this.props.managed
                })
              }}
              fail={{
                title: trans('restricted_workspace.cannot_access'),
                help: classes({
                  [trans('restricted_workspace.contact_manager')]: !this.props.errors.selfRegistration,
                  [trans('restricted_workspace.self_registration')]: this.props.errors.selfRegistration
                })
              }}
            >
              {this.props.authenticated && this.props.errors.selfRegistration &&
                <Button
                  style={{marginTop: 20}}
                  className="btn btn-block btn-emphasis"
                  type={CALLBACK_BUTTON}
                  label={trans('restricted_workspace.self_register')}
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
                      if (this.props.errors.selfRegistration) {
                        this.props.selfRegister()
                      }
                    }
                  }]}
                  primary={true}
                />
              }

              {!this.props.authenticated && this.props.platformSelfRegistration && this.props.errors.selfRegistration &&
                <Button
                  className="btn btn-block"
                  type={MODAL_BUTTON}
                  label={trans('self-register', {}, 'actions')}
                  modal={[MODAL_REGISTRATION, {
                    onRegister: () => this.props.selfRegister()
                  }]}
                />
              }

              {!this.props.authenticated && this.props.errors.selfRegistration &&
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
              success={{
                title: classes({
                  [trans('restricted_workspace.you_are_manager')]: this.props.managed,
                  [trans('restricted_workspace.can_access')]: !this.props.managed
                }),
                help: classes({
                  [trans('restricted_workspace.manager_rights_access')]: this.props.managed,
                  [trans('restricted_workspace.rights_access')]: !this.props.managed
                })
              }}
              fail={{
                title: this.props.managed ? trans('restricted_workspace.you_are_manager') : trans('restricted_workspace.pending_registration'),
                help: this.props.managed ? trans('restricted_workspace.manager_rights_access') : trans('restricted_workspace.wait_validation')
              }}
            />
          }

          {(!isUndefined(this.props.errors.notStarted) || !isUndefined(this.props.errors.ended)) &&
            <ContentRestriction
              icon="fa fa-fw fa-calendar"
              failed={this.props.errors.notStarted || this.props.errors.ended}
              success={{
                title: '',
                help: ''
              }}
              fail={{
                title: this.props.errors.notStarted ? trans('restricted_workspace.not_started') : trans('restricted_workspace.ended'),
                help: this.props.errors.notStarted ? `${trans('restricted_workspace.wait')} ${this.props.errors.startDate}` : ''
              }}
            />
          }

          {!isUndefined(this.props.errors.locked) &&
            <ContentRestriction
              icon="fa fa-fw fa-key"
              onlyWarn={true}
              failed={this.props.errors.locked}
              success={{
                title: trans('restricted_workspace.unlocked'),
                help: ''
              }}
              fail={{
                title: trans('restricted_workspace.code_required'),
                help: trans('restricted_workspace.enter_code')
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
                    label={trans('restricted_workspace.access')}
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
                title: trans('restricted_workspace.authorized_ip'),
                help: ''
              }}
              fail={{
                title: trans('restricted_workspace.authorized_ip_required'),
                help: trans('restricted_workspace.use_authorized_ip')
              }}
            />
          }

          {this.props.managed &&
            <Button
              className="btn btn-block btn-emphasis"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-sign-in-alt"
              label={trans('restricted_workspace.access')}
              callback={this.props.dismiss}
              primary={true}
            />
          }

          {this.props.managed &&
            <ContentHelp
              help={trans('restricted_workspace.manager_info')}
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
  selfRegister: T.func
}

export {
  WorkspaceRestrictions
}
