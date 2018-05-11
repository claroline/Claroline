import React, {Component} from 'react'
import {connect} from 'react-redux'
import {select} from '#/main/core/administration/transfer/selector'
import {actions} from '#/main/core/administration/transfer/actions'
import {actions as logActions} from '#/main/core/administration/transfer/components/log/actions'
import has from 'lodash/has'
import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {Routes} from '#/main/core/router'
import {withRouter} from '#/main/core/router'
import classes from 'classnames'
import {Logs} from '#/main/core/administration/transfer/components/log/components/logs.jsx'

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

const RoutedExplain = props => {
  const entity = props.match.params.entity
  const action = props.match.params.action
  const choices = {}
  choices['none'] = ''
  Object.keys(props.explanation[entity]).reduce((o, key) => Object.assign(o, {[entity + '_' + key]: trans(key, {}, 'transfer')}), choices)

  return (
    <div>
      <h3>{trans(entity)}</h3>
      <div>
        <FormContainer
          level={3}
          name="import"
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
      </div>

      <Logs/>

      {props.explanation[entity][action] &&
        <div>
          <div> {trans('import_headers')} </div>
          <Fields {...props.explanation[entity][action]} />
        </div>

      }
    </div>
  )
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

class Import extends Component
{
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <div className="user-profile container row">
        <div className="col-md-3">
          <Tabs {...this.props}></Tabs>
        </div>

        <div className="col-md-9">
          <Routes
            routes={[{
              path: '/import/:entity/:action',
              exact: true,
              component: ConnectedExplain,
              onEnter: (params) => this.props.openForm(params)
            }]}
          />
        </div>
      </div>
    )
  }
}

const ConnectedImport = connect(
  state => ({explanation: select.explanation(state)}),
  dispatch =>({openForm(params) {
    dispatch(actions.open('import', params))
  }})
)(Import)

export {
  ConnectedImport as Import
}
