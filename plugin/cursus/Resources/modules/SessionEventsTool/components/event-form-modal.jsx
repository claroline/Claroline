import React, {Component, PropTypes as T} from 'react'
import Modal from 'react-bootstrap/lib/Modal'
import classes from 'classnames'
import moment from 'moment'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {Textarea} from '#/main/core/layout/form/components/textarea.jsx'
import {t, trans} from '#/main/core/translation'
import Datetime from 'react-datetime'
import 'react-datetime/css/react-datetime.css'

const locale = getLocale()

export const MODAL_EVENT_FORM = 'MODAL_EVENT_FORM'

export class EventFormModal  extends Component {
  constructor(props) {
    super(props)
    this.state = {
      hasError: false,
      nameError: null,
      startDateError: null,
      endDateError: null,
      name: props.event.name ? props.event.name : undefined,
      description: props.event.description ? props.event.description : undefined,
      registrationType: props.event.registrationType ? props.event.registrationType : undefined,
      maxUsers: props.event.maxUsers ? props.event.maxUsers : undefined,
      startDate: props.event.startDate ?  new Date(props.event.startDate) : new Date(props.session.startDate),
      endDate: props.event.endDate ?  new Date(props.event.endDate) : new Date(props.session.endDate)
    }
  }

  updateEventProps(property, value) {
    switch (property) {
      case 'name':
        this.setState({name: value})
        break
      case 'description':
        this.setState({description: value})
        break
      case 'registrationType':
        this.setState({registrationType: value})
        break
      case 'maxUsers':
        this.setState({maxUsers: value})
        break
      case 'startDate':
        this.setState({startDate: value})
        break
      case 'endDate':
        this.setState({endDate: value})
        break
    }
    this.props.updateEventForm(property, value)
  }

  registerSessionEvent() {
    if (!this.state['hasError']) {
      if (this.props.mode === 'creation') {
        this.props.confirmAction(this.props.session.id, this.state)
      } else if (this.props.mode === 'edition') {
        this.props.confirmAction(this.props.event.id, this.state)
      }
      this.props.fadeModal()
    }
  }

  validateSessionEvent() {
    const validation = {
      hasError: false,
      nameError: null,
      startDateError: null,
      endDateError: null,
      maxUsersError: null
    }

    if (!this.state['name']) {
      validation['nameError'] = trans('form_not_blank_error', {}, 'cursus')
      validation['hasError'] = true
    }
    if (!moment(this.state['startDate']).isValid()) {
      validation['startDateError'] = trans('form_not_valid_error', {}, 'cursus')
      validation['hasError'] = true
    }
    if (!moment(this.state['endDate']).isValid()) {
      validation['endDateError'] = trans('form_not_valid_error', {}, 'cursus')
      validation['hasError'] = true
    }
    if (this.state['maxUsers'] !== undefined && this.state['maxUsers'] !== null && this.state['maxUsers'] !== '' &&
      (isNaN(parseInt(this.state['maxUsers'])) || parseInt(this.state['maxUsers']) < 0)
    ) {
      validation['maxUsersError'] = trans('form_number_superior_error', {value: 0}, 'cursus')
      validation['hasError'] = true
    }
    this.setState(validation, this.registerSessionEvent)
  }

  componentDidMount() {
    if (this.props.mode === 'edition') {
      this.props.loadFormData(this.props.event)
    }
  }

  componentWillUnmount() {
    this.props.resetFormData()
  }

  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Body>
          <div className={classes('form-group row', {'has-error': this.state.nameError})}>
            <label className="control-label col-md-3">
              {t('name')}
            </label>
            <div className="col-md-9">
              <input type="text"
                     className="form-control"
                     value={this.state.name}
                     onChange={e => this.updateEventProps('name', e.target.value)}
              />
              {this.state.nameError &&
                <div className="help-block field-error">
                  {this.state.nameError}
                </div>
              }
            </div>
          </div>

          <div className="form-group row">
            <div className="control-label col-md-3">
              <label>{t('description')}</label>
            </div>
            <div className="col-md-9">
              <Textarea id="event-form-description"
                        content={this.state.description}
                        onChange={text => this.updateEventProps('description', text)}
              >
              </Textarea>
            </div>
          </div>

          <div className={classes('form-group row', {'has-error': this.state.startDateError})}>
            <div className="control-label col-md-3">
              <label>{t('start_date')}</label>
            </div>
            <div className="col-md-9">
              <Datetime closeOnSelect={true}
                        dateFormat={true}
                        timeFormat={true}
                        locale={locale}
                        utc={true}
                        defaultValue={this.state.startDate}
                        onChange={date => this.updateEventProps('startDate', date)}
              />
              {this.state.startDateError &&
                <div className="help-block field-error">
                  {this.state.startDateError}
                </div>
              }
            </div>
          </div>

          <div className={classes('form-group row', {'has-error': this.state.endDateError})}>
            <div className="control-label col-md-3">
              <label>{t('end_date')}</label>
            </div>
            <div className="col-md-9">
              <Datetime closeOnSelect={true}
                        dateFormat={true}
                        timeFormat={true}
                        locale={locale}
                        utc={true}
                        defaultValue={this.state.endDate}
                        onChange={date => this.updateEventProps('endDate', date)}
              />
              {this.state.endDateError &&
                <div className="help-block field-error">
                  {this.state.endDateError}
                </div>
              }
            </div>
          </div>

          <div className="form-group row">
            <div className="control-label col-md-3">
              <label>{trans('session_event_registration', {}, 'cursus')}</label>
            </div>
            <div className="col-md-9">
              <select className="form-control"
                      value={this.state.registrationType}
                      onChange={e => this.updateEventProps('registrationType', e.target.value)}
              >
                <option value="0">{trans('event_registration_automatic', {}, 'cursus')}</option>
                <option value="1">{trans('event_registration_manual', {}, 'cursus')}</option>
                <option value="2">{trans('event_registration_public', {}, 'cursus')}</option>
              </select>
            </div>
          </div>

          <div className={classes('form-group row', {'has-error': this.state.maxUsersError})}>
            <div className="control-label col-md-3">
              <label>{trans('max_users', {}, 'cursus')}</label>
            </div>
            <div className="col-md-9">
              <input type="number"
                     className="form-control"
                     value={this.state.maxUsers}
                     min="0"
                     onChange={e => this.updateEventProps('maxUsers', e.target.value)}
              />
              {this.state.maxUsersError &&
                <div className="help-block field-error">
                  {this.state.maxUsersError}
                </div>
              }
            </div>
          </div>
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

EventFormModal.propTypes = {
  event: T.shape({
    id: T.number,
    name: T.string,
    description: T.string,
    startDate: T.string,
    endDate: T.string,
    registrationType: T.number.isRequired,
    maxUsers: T.number
  }).isRequired,
  mode: T.string.isRequired,
  session: T.object,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  updateEventForm: T.func.isRequired,
  confirmAction: T.func.isRequired,
  resetFormData: T.func.isRequired,
  loadFormData: T.func
}

function getLocale() {
  const homeLocale = document.querySelector('#homeLocale')

  if (homeLocale) {
    return homeLocale.innerHTML.trim()
  }
  return 'en'
}
