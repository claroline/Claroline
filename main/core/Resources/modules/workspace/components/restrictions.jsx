import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isUndefined from 'lodash/isUndefined'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import {PasswordInput} from '#/main/app/data/types/password/components/input'
import {ContentHelp} from '#/main/app/content/components/help'

import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

const Restriction = props => {
  let title, help
  if (props.failed) {
    title = props.fail.title
    help = props.fail.help
  } else {
    title = props.success.title
    help = props.success.help
  }

  return (
    <div className={classes('access-restriction alert alert-detailed', {
      'alert-success': !props.failed,
      'alert-warning': props.failed && props.onlyWarn,
      'alert-danger': props.failed && !props.onlyWarn
    })}>
      <span className={classes('alert-icon', props.icon)} />

      <div className="alert-content">
        <h5 className="alert-title h4">{title}</h5>

        {help &&
          <p className="alert-text">{help}</p>
        }

        {props.failed && props.children}
      </div>
    </div>
  )
}

Restriction.propTypes = {
  icon: T.string.isRequired,
  success: T.shape({
    title: T.string.isRequired,
    help: T.string
  }).isRequired,
  fail: T.shape({
    title: T.string.isRequired,
    help: T.string
  }).isRequired,
  failed: T.bool.isRequired,
  onlyWarn: T.bool, // we only warn for restrictions that can be fixed
  children: T.node
}

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
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-fw fa-lock"
        title={trans('restricted_access')}
        help={trans('restricted_workspace.access_message')}
      >
        <Restriction
          icon="fa fa-fw fa-id-badge"
          failed={this.props.errors.noRights}
          success={{
            title: this.props.managed ? trans('restricted_workspace.you_are_manager') : trans('restricted_workspace.can_access'),
            help: this.props.managed ? trans('restricted_workspace.manager_rights_access') : trans('restricted_workspace.rights_access')
          }}
          fail={{
            title: trans('restricted_workspace.cannot_access'),
            help: trans('restricted_workspace.contact_manager')
          }}
        />

        {(!isUndefined(this.props.errors.notStarted) || !isUndefined(this.props.errors.ended)) &&
          <Restriction
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
          <Restriction
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
            <div>
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
            </div>
            }
          </Restriction>
        }

        {!isUndefined(this.props.errors.invalidLocation) &&
          <Restriction
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

        {this.props.errors.noRights && /*this.props.workspace && this.props.workspace.registration.selfRegistration &&*/
          <Button
            className="btn btn-block btn-emphasis"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-user-plus"
            label={trans('restricted_workspace.self_register')}
            callback={this.props.selfRegister}
            primary={true}
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
      </EmptyPlaceholder>
    )
  }
}

WorkspaceRestrictions.propTypes = {
  managed: T.bool,
  errors: T.shape({
    noRights: T.bool.isRequired,
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
  dismiss: T.func.isRequired,
  checkAccessCode: T.func,
  selfRegister: T.func
}

export {
  WorkspaceRestrictions
}
