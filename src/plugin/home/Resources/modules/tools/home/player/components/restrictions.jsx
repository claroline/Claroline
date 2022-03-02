import React, {Component}from 'react'
import {PropTypes as T} from 'prop-types'
import isUndefined from 'lodash/isUndefined'

import {trans, displayDate} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {PasswordInput} from '#/main/app/data/types/password/components/input'
import {FormGroup} from '#/main/app/content/form/components/group'
import {ContentHelp} from '#/main/app/content/components/help'
import {ContentRestriction} from '#/main/app/content/components/restriction'

class PlayerRestrictions extends Component {
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
        <p>{trans('restricted_access_message', {}, 'home')}</p>

        {(!isUndefined(this.props.errors.notStarted) || !isUndefined(this.props.errors.ended)) &&
          <ContentRestriction
            icon="fa fa-fw fa-calendar"
            failed={this.props.errors.notStarted || this.props.errors.ended}
            success={{
              title: trans('restrictions.period_opened', {}, 'home'),
              help: (this.props.errors.startDate || this.props.errors.endDate) ? trans('restrictions.period_opened_help', {
                start: this.props.errors.startDate ? displayDate(this.props.errors.startDate, false, true) : `(${trans('empty_value')})`,
                end: this.props.errors.endDate ? displayDate(this.props.errors.endDate, false, true) : `(${trans('empty_value')})`
              }, 'home') : trans('restrictions.period_not_limited', {}, 'home')
            }}
            fail={{
              title: trans(this.props.errors.notStarted ? 'restrictions.not_started' : 'restrictions.ended', {}, 'home'),
              help: this.props.errors.notStarted ?
                trans('restrictions.not_started_help', {date: displayDate(this.props.errors.startDate, false, true)}, 'home') :
                trans('restrictions.ended_help', {date: displayDate(this.props.errors.endDate, false, true)}, 'home')
            }}
          />
        }

        {!isUndefined(this.props.errors.locked) &&
          <ContentRestriction
            icon="fa fa-fw fa-key"
            onlyWarn={true}
            failed={this.props.errors.locked}
            success={{
              title: trans('restrictions.unlocked', {}, 'home')
            }}
            fail={{
              title: trans('restrictions.locked', {}, 'home'),
              help: this.props.errors.locked && !(this.props.errors.notStarted || this.props.errors.ended) ?
                trans('restrictions.locked_help', {}, 'home') :
                ''
            }}
          >
            {this.props.errors.locked && !(this.props.errors.notStarted || this.props.errors.ended) &&
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
                  label={trans('open-tab', {}, 'actions')}
                  callback={this.submitCodeAccess}
                  primary={true}
                />
              </div>
            }
          </ContentRestriction>
        }

        {this.props.managed &&
          <Button
            className="btn btn-block btn-emphasis"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-sign-in-alt"
            label={trans('open-tab', {}, 'actions')}
            callback={this.props.dismiss}
            primary={true}
          />
        }

        {this.props.managed &&
          <ContentHelp
            help={trans('restrictions.dismiss_help', {}, 'home')}
          />
        }
      </div>
    )
  }
}

PlayerRestrictions.propTypes = {
  managed: T.bool,
  errors: T.shape({
    // optional restrictions (if we get nothing, the restriction is disabled)
    locked: T.bool,
    notStarted: T.bool,
    ended: T.bool,
    startDate: T.string,
    endDate: T.string
  }).isRequired,
  dismiss: T.func.isRequired,
  checkAccessCode: T.func
}

export {
  PlayerRestrictions
}
