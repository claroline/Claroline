import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'
import classes from 'classnames'
import moment from 'moment'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {t, trans} from '#/main/core/translation'
import {DatePicker} from '#/main/core/layout/form/components/field/date-picker.jsx'

export const MODAL_EVENT_REPEAT_FORM = 'MODAL_EVENT_REPEAT_FORM'

export class EventRepeatFormModal  extends Component {
  constructor(props) {
    super(props)
    this.state = {
      monday: false,
      tuesday: false,
      wednesday: false,
      thursday: false,
      friday: false,
      saturday: false,
      sunday: false,
      until: undefined,
      duration: '',
      hasError: false,
      daysError: null,
      untilError: null,
      durationError: null
    }
  }

  updateFormState(property, value) {
    switch (property) {
      case 'monday':
        this.setState({monday: value})
        break
      case 'tuesday':
        this.setState({tuesday: value})
        break
      case 'wednesday':
        this.setState({wednesday: value})
        break
      case 'thursday':
        this.setState({thursday: value})
        break
      case 'friday':
        this.setState({friday: value})
        break
      case 'saturday':
        this.setState({saturday: value})
        break
      case 'sunday':
        this.setState({sunday: value})
        break
      case 'registrationType':
        this.setState({registrationType: value})
        break
      case 'until':
        this.setState({until: value})
        break
      case 'duration':
        this.setState({duration: parseInt(value)})
        break
    }
  }

  repeatSessionEvent() {
    if (!this.state['hasError']) {
      this.props.repeatSessionEvent(this.props.event.id, this.state)
      this.props.fadeModal()
    }
  }

  validateSessionEvent() {
    const validation = {
      hasError: false,
      daysError: null,
      untilError: null,
      durationError: null
    }

    if (!this.state['monday'] &&
      !this.state['tuesday'] &&
      !this.state['wednesday'] &&
      !this.state['thursday'] &&
      !this.state['friday'] &&
      !this.state['saturday'] &&
      !this.state['sunday']
    ) {
      validation['daysError'] = trans('form_not_blank_error', {}, 'cursus')
      validation['hasError'] = true
    }
    if (!moment(this.state['until']).isValid()) {
      validation['until'] = undefined
    } else {
      validation['until'] = this.state['until']
    }
    if (!validation['until'] && !this.state['duration']) {
      validation['untilError'] = trans('form_not_blank_error', {}, 'cursus')
      validation['durationError'] = trans('form_not_blank_error', {}, 'cursus')
      validation['hasError'] = true
    }
    this.setState(validation, this.repeatSessionEvent)
  }

  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Body>
          <div className="row">
            <div className="col-md-12">
              <label className="control-label">
                {trans('repetition', {}, 'cursus')}
              </label>
            </div>
          </div>
          {this.state.daysError &&
            <div className="alert alert-danger">
              {this.state.daysError}
            </div>
          }
          <div className="row">
            <div className="col-md-6">
              <label>
                <input type="checkbox" onChange={e => this.updateFormState('monday', e.target.checked)}/>
                &nbsp;
                {trans('day.monday', {}, 'agenda')}
              </label>
            </div>
            <div className="col-md-6">
              <label>
                <input type="checkbox" onChange={e => this.updateFormState('saturday', e.target.checked)}/>
                &nbsp;
                {trans('day.saturday', {}, 'agenda')}
              </label>
            </div>
          </div>
          <div className="row">
            <div className="col-md-6">
              <label>
                <input type="checkbox" onChange={e => this.updateFormState('tuesday', e.target.checked)}/>
                &nbsp;
                {trans('day.tuesday', {}, 'agenda')}
              </label>
            </div>
            <div className="col-md-6">
              <label>
                <input type="checkbox" onChange={e => this.updateFormState('sunday', e.target.checked)}/>
                &nbsp;
                {trans('day.sunday', {}, 'agenda')}
              </label>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <label>
                <input type="checkbox" onChange={e => this.updateFormState('wednesday', e.target.checked)}/>
                &nbsp;
                {trans('day.wednesday', {}, 'agenda')}
              </label>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <label>
                <input type="checkbox" onChange={e => this.updateFormState('thursday', e.target.checked)}/>
                &nbsp;
                {trans('day.thursday', {}, 'agenda')}
              </label>
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <label>
                <input type="checkbox" onChange={e => this.updateFormState('friday', e.target.checked)}/>
                &nbsp;
                {trans('day.friday', {}, 'agenda')}
              </label>
            </div>
          </div>
          <hr/>
          {!this.state.duration &&
            <div className={classes('form-group row', {'has-error': this.state.untilError})}>
              <label className="control-label col-md-3">
                {trans('until', {}, 'cursus')}
              </label>
              <div className="col-md-9">
                <DatePicker
                  id="event-repeat-until"
                  name="event-repeat-until"
                  value={this.state.until || ''}
                  minDate={moment.utc(this.props.event.startDate)}
                  onChange={date => this.updateFormState('until', date)}
                />
                {this.state.untilError &&
                  <div className="help-block field-error">
                    {this.state.untilError}
                  </div>
                }
              </div>
            </div>
          }
          {!this.state.until &&
            <div className={classes('form-group row', {'has-error': this.state.durationError})}>
              <label className="control-label col-md-3">
                {t('duration')}
              </label>
              <div className="col-md-9">
                <span className="input-group">
                  <input
                    type="number"
                    className="form-control"
                    min="1"
                    value={this.state.duration}
                    onChange={e => this.updateFormState('duration', e.target.value)}
                  />
                  <span className="input-group-addon">
                    {t('weeks')}
                  </span>
                </span>
                {this.state.durationError &&
                  <div className="help-block field-error">
                    {this.state.durationError}
                  </div>
                }
              </div>
            </div>
          }
        </Modal.Body>
        <Modal.Footer>
          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {t('cancel')}
          </button>
          <button className="btn btn-primary" onClick={() => this.validateSessionEvent()}>
            {t('ok')}
          </button>
        </Modal.Footer>
      </BaseModal>
    )
  }
}

EventRepeatFormModal.propTypes = {
  event: T.shape({
    id: T.number,
    name: T.string,
    description: T.string,
    startDate: T.string,
    endDate: T.string,
    registrationType: T.number.isRequired,
    maxUsers: T.number
  }).isRequired,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  repeatSessionEvent: T.func.isRequired
}
