import React, {Component}from 'react'
import {PropTypes as T} from 'prop-types'
import isUndefined from 'lodash/isUndefined'

import {trans, displayDate} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {PasswordInput} from '#/main/app/data/types/password/components/input'
import {FormGroup} from '#/main/app/content/form/components/group'
import {ContentHelp} from '#/main/app/content/components/help'
import {ContentRestriction} from '#/main/app/content/components/restriction'
import {MODAL_LOGIN} from '#/main/app/modals/login'

class ResourceRestrictions extends Component {
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
      <div className="access-restrictions">
        <h2>{trans('restricted_access')}</h2>
        <p>{trans('restricted_access_message', {}, 'resource')}</p>

        <ContentRestriction
          icon="fa fa-fw fa-id-badge"
          failed={this.props.errors.noRights}
          success={{
            title: trans(this.props.managed ? 'restrictions.manager' : 'restrictions.rights', {}, 'resource'),
            help: trans(this.props.managed ? 'restrictions.manager_help' : 'restrictions.rights_help', {}, 'resource')
          }}
          fail={{
            title: trans('restrictions.no_rights', {}, 'resource'),
            help: trans('restrictions.no_rights_help', {}, 'resource')
          }}
        >
          {!this.props.authenticated &&
            <Button
              style={{marginTop: 20}}
              className="btn btn-block btn-emphasis"
              type={MODAL_BUTTON}
              label={trans('login', {}, 'actions')}
              modal={[MODAL_LOGIN]}
              primary={true}
            />
          }
        </ContentRestriction>

        <ContentRestriction
          icon="fa fa-fw fa-eye"
          failed={this.props.errors.deleted || this.props.errors.notPublished}
          success={{
            title: trans('restrictions.published', {}, 'resource')
          }}
          fail={{
            title: trans(this.props.errors.deleted ? 'restrictions.deleted' : 'restrictions.not_published', {}, 'resource'),
            help: trans(this.props.errors.deleted ? 'restrictions.deleted_help' : 'restrictions.not_published_help', {}, 'resource')
          }}
        />

        {(!isUndefined(this.props.errors.notStarted) || !isUndefined(this.props.errors.ended)) &&
          <ContentRestriction
            icon="fa fa-fw fa-calendar"
            failed={this.props.errors.notStarted || this.props.errors.ended}
            success={{
              title: trans('restrictions.period_opened', {}, 'resource'),
              help: (this.props.errors.startDate || this.props.errors.endDate) ? trans('restrictions.period_opened_help', {
                start: this.props.errors.startDate ? displayDate(this.props.errors.startDate, false, true) : `(${trans('empty_value')})`,
                end: this.props.errors.endDate ? displayDate(this.props.errors.endDate, false, true) : `(${trans('empty_value')})`
              }, 'resource') : trans('restrictions.period_not_limited', {}, 'resource')
            }}
            fail={{
              title: trans(this.props.errors.notStarted ? 'restrictions.not_started' : 'restrictions.ended', {}, 'resource'),
              help: this.props.errors.notStarted ?
                trans('restrictions.not_started_help', {date: displayDate(this.props.errors.startDate, false, true)}, 'resource') :
                trans('restrictions.ended_help', {date: displayDate(this.props.errors.endDate, false, true)}, 'resource')
            }}
          />
        }

        {!isUndefined(this.props.errors.locked) &&
          <ContentRestriction
            icon="fa fa-fw fa-key"
            onlyWarn={true}
            failed={this.props.errors.locked}
            success={{
              title: trans('restrictions.unlocked', {}, 'resource')
            }}
            fail={{
              title: trans('restrictions.locked', {}, 'resource'),
              help: this.props.errors.locked && !(this.props.errors.noRights || this.props.errors.notPublished || this.props.errors.deleted || this.props.errors.notStarted || this.props.errors.ended) ?
                trans('restrictions.locked_help', {}, 'resource') :
                ''
            }}
          >
            {this.props.errors.locked && !(this.props.errors.noRights || this.props.errors.notPublished || this.props.errors.deleted || this.props.errors.notStarted || this.props.errors.ended) &&
              <div style={{marginTop: 20}}>
                <FormGroup
                  id="access-code"
                  label={trans('access_code')}
                  hideLabel={true}
                >
                  <PasswordInput
                    id="access-code"
                    value={this.state.codeAccess}
                    onChange={this.updateCodeAccess}
                  />
                </FormGroup>

                <Button
                  className="btn btn-block btn-emphasis"
                  type={CALLBACK_BUTTON}
                  icon="fa fa-fw fa-sign-in-alt"
                  disabled={!this.state.codeAccess}
                  label={trans('open-resource', {}, 'actions')}
                  callback={this.submitCodeAccess}
                  primary={true}
                />
              </div>
            }
          </ContentRestriction>
        }

        {!isUndefined(this.props.errors.invalidLocation) &&
          <ContentRestriction
            icon="fa fa-fw fa-laptop"
            onlyWarn={true}
            failed={this.props.errors.invalidLocation}
            success={{
              title: trans('restrictions.location_authorized', {}, 'resource')
            }}
            fail={{
              title: trans('restrictions.location_unauthorized', {}, 'resource'),
              help: trans('restrictions.location_unauthorized_help', {}, 'resource')
            }}
          />
        }

        {this.props.managed &&
          <Button
            className="btn btn-block btn-emphasis"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-sign-in-alt"
            label={trans('open-resource', {}, 'actions')}
            callback={this.props.dismiss}
            primary={true}
          />
        }

        {this.props.managed &&
          <ContentHelp
            help={trans('restrictions.dismiss_help', {}, 'resource')}
          />
        }
      </div>
    )
  }
}

ResourceRestrictions.propTypes = {
  managed: T.bool,
  errors: T.shape({
    noRights: T.bool.isRequired,
    deleted: T.bool.isRequired,
    notPublished: T.bool.isRequired,
    // optional restrictions (if we get nothing, the restriction is disabled)
    locked: T.bool,
    notStarted: T.bool,
    ended: T.bool,
    invalidLocation: T.bool,
    startDate: T.string,
    endDate: T.string
  }).isRequired,
  authenticated: T.bool.isRequired,
  dismiss: T.func.isRequired,
  checkAccessCode: T.func
}

export {
  ResourceRestrictions
}
