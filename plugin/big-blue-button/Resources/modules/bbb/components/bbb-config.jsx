import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans, t} from '#/main/core/translation'
import {Date} from '#/main/core/layout/form/components/field/date.jsx'

import {actions} from '../actions'

class BBBConfig extends Component {
  componentDidMount() {
    this.props.initializeForm()
  }

  componentWillUnmount() {
    this.props.resetMessage()
  }

  updateConfig(property, value) {
    this.props.updateForm(property, value)
  }

  render() {
    return (
      <div>
        {this.props.message && this.props.message.content &&
          <div className={`alert alert-${this.props.message.type}`}>
            <i
              className="fa fa-times close"
              onClick={() => this.props.resetMessage()}
            >
            </i>
            {this.props.message.content}
          </div>
        }
        <div className="form-group row">
          <label className="control-label col-md-3">
            {trans('room_name', {}, 'bbb')}
          </label>
          <div className="col-md-9">
            <input
              type="text"
              className="form-control"
              value={this.props.params.roomName ? this.props.params.roomName : undefined}
              onChange={e => this.updateConfig('roomName', e.target.value)}
            />
          </div>
        </div>

        <div className="form-group row">
          <label className="control-label col-md-3">
            {trans('welcome_message', {}, 'bbb')}
          </label>
          <div className="col-md-9">
            <input
              type="text"
              className="form-control"
              value={this.props.params.welcomeMessage ? this.props.params.welcomeMessage : undefined}
              onChange={e => this.updateConfig('welcomeMessage', e.target.value)}
            />
          </div>
        </div>

        <div className="form-group row">
          <label className="control-label col-md-3">
            {trans('open_bbb_in_new_tab', {}, 'bbb')}
          </label>
          <div className="col-md-9">
            <input
              type="checkbox"
              checked={this.props.params.newTab}
              onChange={e => this.updateConfig('newTab', e.target.checked)}
            />
          </div>
        </div>

        <div className="form-group row">
          <label className="control-label col-md-3">
            {trans('wait_for_moderator', {}, 'bbb')}
          </label>
          <div className="col-md-9">
            <input
              type="checkbox"
              checked={this.props.params.moderatorRequired}
              onChange={e => this.updateConfig('moderatorRequired', e.target.checked)}
            />
          </div>
        </div>

        <div className="form-group row">
          <label className="control-label col-md-3">
            {trans('allow_recording', {}, 'bbb')}
          </label>
          <div className="col-md-9">
            <input
              type="checkbox"
              checked={this.props.params.record}
              onChange={e => this.updateConfig('record', e.target.checked)}
            />
          </div>
        </div>

        <div className={classes('form-group row', {'has-error': this.props.params.startDateError})}>
          <div className="control-label col-md-3">
            <label htmlFor="startDate">{t('start_date')}</label>
          </div>
          <div className="col-md-9">
            <Date
              id="startDate"
              value={this.props.params.startDate}
              onChange={date => this.updateConfig('startDate', date)}
              time={true}
            />
            {this.props.params.startDateError &&
              <div className="help-block field-error">
                {this.props.params.startDateError}
              </div>
            }
          </div>
        </div>

        <div className={classes('form-group row', {'has-error': this.props.params.endDateError})}>
          <div className="control-label col-md-3">
            <label htmlFor="endDate">{t('end_date')}</label>
          </div>
          <div className="col-md-9">
            <Date
              id="endDate"
              value={this.props.params.endDate}
              onChange={date => this.updateConfig('endDate', date)}
              time={true}
            />

            {this.props.params.endDateError &&
              <div className="help-block field-error">
                {this.props.params.endDateError}
              </div>
            }
          </div>
        </div>
      </div>
    )
  }
}

BBBConfig.propTypes = {
  params: T.shape({
    id: T.number,
    roomName: T.string,
    welcomeMessage: T.string,
    newTab: T.boolean,
    moderatorRequired: T.boolean,
    record: T.boolean,
    startDate: T.oneOfType([T.object, T.string]),
    endDate: T.oneOfType([T.object, T.string]),
    startDateError: T.string,
    endDateError: T.string
  }),
  message: T.shape({
    content: T.string,
    type: T.string
  }),
  initializeForm: T.func,
  updateForm: T.func,
  resetMessage: T.func
}

function mapStateToProps(state) {
  return {
    params: state.resourceForm,
    message: state.message
  }
}

function mapDispatchToProps(dispatch) {
  return {
    initializeForm: () => dispatch(actions.initializeResourceForm()),
    updateForm: (property, value) => dispatch(actions.updateResourceForm(property, value)),
    resetMessage: () => dispatch(actions.resetMessage())
  }
}

const ConnectedBBBConfig = connect(mapStateToProps, mapDispatchToProps)(BBBConfig)

export {ConnectedBBBConfig as BBBConfig}