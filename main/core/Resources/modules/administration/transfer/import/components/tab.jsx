import React, {Component} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import has from 'lodash/has'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {Routes, withRouter} from '#/main/app/router'
import {Heading} from '#/main/core/layout/components/heading'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {Logs} from '#/main/core/administration/transfer/log/components/logs'
import {select} from '#/main/core/administration/transfer/selectors'
import {actions} from '#/main/core/administration/transfer/actions'
import {actions as logActions} from '#/main/core/administration/transfer/log/actions'

const Tabs = props =>
  <ul className="nav nav-pills nav-stacked">
    {Object.keys(props.explanation).map((key) =>
      <li key={key} role="presentation" className="">
        <a href={'#/import/' + key + '/none'}>{trans(key)}</a>
      </li>
    )}
  </ul>

const Field = props => {
  let i = 0
  if (has(props, 'oneOf')) {
    return (
      <div className="panel panel-body">
        {trans('one_of_field_list')} <span className={classes('label', {'label-danger': props.oneOf.required}, {'label-warning': !props.oneOf.required})}>{props.oneOf.required ? trans('required'): trans('optional')}</span>
        {props.oneOf.map(oneOf => {
          i++
          return(<Fields key={'field'+i} properties={oneOf.properties}/>)
        })}
      </div>
    )
  } else {
    return(
      <div>
        <div className="well">
          <div><strong>{props.name}</strong>{'\u00A0'}{'\u00A0'}<span className={classes('label', {'label-danger': props.required}, {'label-warning': !props.required})}>{props.required ? trans('required'): trans('optional')}</span></div>
          <div>{props.description}</div>
        </div>
      </div>
    )
  }
}

const Fields = props => {
  return (
    <div>
      {props.properties.map((prop, index) => <Field key={index} {...prop}/> )}
    </div>
  )
}

class RoutedExplain extends Component {
  constructor(props) {
    super(props)
    this.currentLogId = this.generateLogId()
  }

  generateLogId() {
    const log = Math.random().toString(36).substring(7)
    this.currentLogId = log

    return log
  }

  getLogId() {
    return this.currentLogId
  }

  render() {
    const props = this.props

    const entity = props.match.params.entity
    const action = props.match.params.action
    const choices = {}
    choices['none'] = ''
    Object.keys(props.explanation[entity]).reduce((o, key) => Object.assign(o, {[entity + '_' + key]: trans(key, {}, 'transfer')}), choices)

    return (
      <div>
        <FormData
          level={2}
          name="import"
          title={trans(entity)}
          target={['apiv2_transfer_start', {log: this.getLogId()}]}
          buttons={true}
          save={{
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-upload',
            label: trans('import', {}, 'actions'),
            callback: () => {
              const logName = this.getLogId()
              const refresher = setInterval(() => {
                this.props.loadLog(logName)
                if (this.props.logs && this.props.logs.total !== undefined && this.props.logs.processed === this.props.logs.total) {
                  clearInterval(refresher)
                }
              }, 2000)

              this.generateLogId()
            }
          }}
          cancel={{
            type: LINK_BUTTON,
            target: '/import',
            exact: true
          }}
          sections={[
            {
              title: trans('general'),
              primary: true,
              fields: [
                {
                  name: 'action',
                  type: 'choice',
                  label: trans('action'),
                  onChange: (value) => {
                    props.history.push('/import/' + entity + '/' + value.substring(value.indexOf('_') + 1))
                    props.resetLog()
                  },
                  required: true,
                  options: {
                    noEmpty: true,
                    condensed: true,
                    choices: choices
                  }
                }, {
                  name: 'file',
                  type: 'file',
                  label: trans('file'),
                  options: {
                    uploadUrl: ['apiv2_transfer_upload_file']
                  }
                }
              ]
            }
          ]}
        />

        <Logs/>

        {props.explanation[entity][action] &&
        <div>
          <Heading level={3}>{trans('import_headers')}</Heading>
          <Fields {...props.explanation[entity][action]} />
        </div>
        }
      </div>
    )
  }
}

const ConnectedExplain = withRouter(connect(
  state => ({
    explanation: select.explanation(state),
    logs: state.log
  }),
  dispatch =>({
    updateProp: (prop, value, form, entity) => dispatch(actions.updateProp(prop, value, form, entity)),
    resetLog: () => dispatch(logActions.reset())
  })
)(RoutedExplain))

const Import = props =>
  <div className="user-profile row">
    <div className="col-md-3">
      <Tabs {...props} />
    </div>

    <div className="col-md-9">
      <Routes
        routes={[{
          path: '/import/:entity/:action',
          exact: true,
          component: ConnectedExplain,
          onEnter: (params) => props.openForm(params)
        }]}
      />
    </div>
  </div>

const ConnectedImport = connect(
  state => ({explanation: select.explanation(state)}),
  dispatch =>({openForm(params) {
    dispatch(actions.open('import', params))
  }})
)(Import)

export {
  ConnectedImport as Import
}
