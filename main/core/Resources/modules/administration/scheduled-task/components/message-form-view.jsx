/*global UserPicker*/
import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import moment from 'moment'
import Datetime from 'react-datetime'
import 'react-datetime/css/react-datetime.css'
import {t} from '#/main/core/translation'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {actions} from '../actions'
import {navigate} from '../router'

class MessageFormView extends Component {
  constructor(props) {
    super(props)
    this.state = {
      hasError: false,
      scheduledDateError: null,
      receiversError: null,
      objectError: null,
      contentError: null,
      type: this.props.task.type ? this.props.task.type : undefined,
      name: this.props.task.name ? this.props.task.name : undefined,
      scheduledDate: this.props.task.scheduledDate ?  new Date(this.props.task.scheduledDate) : new Date(),
      receivers : this.props.task.users ? this.props.task.users : [],
      receiversIds : this.props.task.users ? this.props.task.users.map(u => u.id) : [],
      object: this.props.task.data && this.props.task.data.object ? this.props.task.data.object : undefined,
      content: this.props.task.data && this.props.task.data.content ? this.props.task.data.content : undefined
    }
    this.addReceivers = this.addReceivers.bind(this)
  }

  componentWillUnmount() {
    this.props.resetTaskForm()
  }

  updateProps(property, value) {
    let ids = []

    switch (property) {
      case 'name':
        this.setState({name: value})
        break
      case 'scheduledDate':
        this.setState({scheduledDate: value})
        break
      case 'object':
        this.setState({object: value})
        break
      case 'content':
        this.setState({content: value})
        break
      case 'receivers':
        ids = value.map(v => v.id)
        this.setState({receivers: value, receiversIds: ids})
        break
    }
  }

  showReceiversSelection() {
    let userPicker = new UserPicker()
    const options = {
      picker_name: 'receivers-picker',
      picker_title: t('receivers_selection'),
      multiple: true,
      return_datas: true,
      selected_users: this.state.receiversIds,
      filter_admin_orgas: true
    }
    userPicker.configure(options, this.addReceivers)
    userPicker.open()
  }

  addReceivers(users) {
    const receivers = users ? users : []
    this.updateProps('receivers', receivers)
  }

  registerTask() {
    if (!this.state['hasError']) {
      if (this.props.task.id) {
        this.props.editTask(this.props.task.id, this.state)
      } else {
        this.props.createTask(this.state)
      }
      navigate('', true)
    }
  }

  validate() {
    const validation = {
      hasError: false,
      scheduledDateError: null,
      receiversError: null,
      objectError: null,
      contentError: null
    }

    if (!moment(this.state['scheduledDate']).isValid()) {
      validation['scheduledDateError'] = t('form_not_valid_error')
      validation['hasError'] = true
    }
    if (!this.state['receivers'] || this.state['receivers'].length === 0) {
      validation['receiversError'] = t('form_not_blank_error')
      validation['hasError'] = true
    }
    if (!this.state['object']) {
      validation['objectError'] = t('form_not_blank_error')
      validation['hasError'] = true
    }
    if (!this.state['content']) {
      validation['contentError'] = t('form_not_blank_error')
      validation['hasError'] = true
    }
    this.setState(validation, this.registerTask)
  }

  render() {
    if (this.state.type) {
      return (
        <div>
          <h1>{this.state.type === 'message' ? t('message_sending') : t('mail_sending')}</h1>
          <hr/>
          <div className="form-group row">
            <label className="control-label col-md-3">
              {t('title')}
            </label>
            <div className="col-md-9">
              <input
                type="text"
                className="form-control"
                value={this.state.name}
                onChange={e => this.updateProps('name', e.target.value)}
              />
            </div>
          </div>

          <div className={classes('form-group row', {'has-error': this.state.scheduledDateError})}>
            <div className="control-label col-md-3">
              <label>{t('planning_date')}</label>
            </div>
            <div className="col-md-9">
              <Datetime
                closeOnSelect={true}
                dateFormat={true}
                timeFormat={true}
                locale="fr"
                utc={false}
                defaultValue={this.state.scheduledDate}
                onChange={date => this.updateProps('scheduledDate', date)}
              />
              {this.state.scheduledDateError &&
              <div className="help-block field-error">
                {this.state.scheduledDateError}
              </div>
              }
            </div>
          </div>

          <div className={classes('form-group row', {'has-error': this.state.receiversError})}>
            <label className="control-label col-md-3">
              {t('receivers')}
            </label>
            <div className="col-md-9">
              <div className="input-group">
                <input
                  type="text"
                  className="form-control"
                  value={this.state.receivers.map(r => `${r.firstName} ${r.lastName}`)}
                  disabled
                />
                <span className="input-group-btn">
                  <button className="btn btn-default" onClick={() => this.showReceiversSelection()}>
                    <i className="fa fa-user"></i>
                  </button>
                </span>
              </div>
              {this.state.receiversError &&
                <div className="help-block field-error">
                  {this.state.receiversError}
                </div>
              }
            </div>
          </div>

          <div className={classes('form-group row', {'has-error': this.state.objectError})}>
            <label className="control-label col-md-3">
              {t('object')}
            </label>
            <div className="col-md-9">
              <input
                type="text"
                className="form-control"
                value={this.state.object}
                onChange={e => this.updateProps('object', e.target.value)}
              />
              {this.state.objectError &&
                <div className="help-block field-error">
                  {this.state.objectError}
                </div>
              }
            </div>
          </div>

          <div className={classes('form-group row', {'has-error': this.state.contentError})}>
            <div className="control-label col-md-3">
              <label>{t('content')}</label>
            </div>
            <div className="col-md-9">
              <Textarea
                id="message-form-content"
                content={this.state.content}
                onChange={text => this.updateProps('content', text)}
              >
              </Textarea>
              {this.state.contentError &&
                <div className="help-block field-error">
                  {this.state.contentError}
                </div>
              }
            </div>
          </div>
          <hr/>
          <button className="btn btn-primary" onClick={() => this.validate()}>
            {t('ok')}
          </button>
          &nbsp;
          <a className="btn btn-default" href={'#'}>
            {t('cancel')}
          </a>
        </div>
      )
    }
  }
}

MessageFormView.propTypes = {
  task: T.object,
  createTask: T.func.isRequired,
  editTask: T.func.isRequired,
  resetTaskForm: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    task: state.taskForm
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createTask: (data) => dispatch(actions.createMessageTask(data)),
    editTask: (taskId, data) => dispatch(actions.editMessageTask(taskId, data)),
    resetTaskForm: () => dispatch(actions.resetTaskForm())
  }
}

const ConnectedMessageFormView = connect(mapStateToProps, mapDispatchToProps)(MessageFormView)

export {ConnectedMessageFormView as MessageFormView}