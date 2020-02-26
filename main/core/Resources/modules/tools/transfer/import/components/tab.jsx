import React, {Component} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import has from 'lodash/has'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Routes, withRouter} from '#/main/app/router'
import {FormData} from '#/main/app/content/form/containers/data'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/main/core/tools/transfer/store'
import {actions as logActions} from '#/main/core/tools/transfer/log/store'
import {Logs} from '#/main/core/tools/transfer/log/components/logs'
import {Heading} from '#/main/core/layout/components/heading'

const Field = props => {
  if (has(props, 'oneOf')) {
    return (
      <div className="panel panel-body">
        {trans('one_of_field_list')}
        <span className={classes('label', {
          'label-danger': props.oneOf.required,
          'label-warning': !props.oneOf.required
        })}>
          {props.oneOf.required ? trans('required') : trans('optional')}
        </span>

        {props.oneOf.map((oneOf, index) =>
          <Fields key={'field'+index} properties={oneOf.properties}/>
        )}
      </div>
    )
  }

  return (
    <div className="well">
      <div><strong>{props.name}</strong>{'\u00A0'}{'\u00A0'}<span className={classes('label', {'label-danger': props.required}, {'label-warning': !props.required})}>{props.required ? trans('required'): trans('optional')}</span></div>
      <div>{props.description}</div>
    </div>
  )
}

const Fields = props =>
  <div>
    {props.properties.map((prop, index) =>
      <Field key={index} {...prop} />
    )}
  </div>

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

    const defaultFields = [
      {
        name: 'action',
        type: 'choice',
        label: trans('action'),
        onChange: (value) => {
          props.history.push(`${this.props.path}/import/${entity}/${value.substring(value.indexOf('_') + 1)}`)
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
          uploadUrl: ['apiv2_transfer_upload_file', {workspace: props.workspace && props.workspace.id ? props.workspace.id : 0}]
        }
      }
    ]

    const explanationAction = props.explanation[entity][action]
    const additionalFields = explanationAction ? explanationAction.fields || []: []

    return (
      <FormData
        level={2}
        name={selectors.STORE_NAME + '.import'}
        title={trans(entity, {}, 'transfer')}
        target={['apiv2_transfer_start', {log: this.getLogId(), workspace: props.workspace && props.workspace.id ? props.workspace.id : null }]}
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
          target: `${this.props.path}/import`,
          exact: true
        }}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: defaultFields.concat(additionalFields)
          }
        ]}
      >
        <Logs/>

        {props.explanation[entity][action] &&
          <div>
            <Heading level={3}>{trans('import_headers')}</Heading>
            <Fields {...props.explanation[entity][action]} />
          </div>
        }
      </FormData>
    )
  }
}

const ConnectedExplain = withRouter(connect(
  state => ({
    path: toolSelectors.path(state),
    explanation: selectors.explanation(state),
    logs: selectors.log(state),
    workspace: toolSelectors.contextData(state)
  }),
  dispatch =>({
    updateProp: (prop, value, form, entity) => dispatch(actions.updateProp(prop, value, form, entity)),
    resetLog: () => dispatch(logActions.reset()),
    loadLog(filename) {
      dispatch(logActions.load(filename))
    }
  })
)(RoutedExplain))

const ImportComponent = props => {
  const entities = Object.keys(props.explanation)
    .map((key) => ({
      title: trans(key, {}, 'transfer'),
      path: `/${key}`
    }))
    .sort((a, b) => {
      if (a.title < b.title) {
        return -1
      }

      if (a.title > b.title) {
        return 1
      }

      return 0
    })

  return (
    <div className="row">
      <div className="col-md-3">
        <Vertical
          style={{
            marginTop: 20 // FIXME
          }}
          basePath={`${props.path}/import`}
          tabs={entities}
        />
      </div>

      <div className="col-md-9">
        <Routes
          path={props.path + '/import'}
          redirect={!isEmpty(entities) ? [
            {from: '/', exact: true, to: entities[0].path}
          ] : undefined}
          routes={[{
            path: '/:entity/:action?',
            component: ConnectedExplain,
            onEnter: (params) => props.openForm(params)
          }]}
        />
      </div>
    </div>
  )
}

const Import = connect(
  state => ({
    path: toolSelectors.path(state),
    explanation: selectors.explanation(state)
  }),
  dispatch =>({
    openForm(params) {
      dispatch(actions.open(selectors.STORE_NAME + '.import', params))
    }
  })
)(ImportComponent)

export {
  Import
}
