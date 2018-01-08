import React, {Component} from 'react'
import {connect} from 'react-redux'
import {select} from '#/main/core/administration/transfer/selector'
import {actions} from '#/main/core/administration/transfer/actions'
import has from 'lodash/has'
import {t} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {Routes} from '#/main/core/router'
import {navigate} from '#/main/core/router'
import classes from 'classnames'

const Tabs = props =>
  <ul className="nav nav-pills nav-stacked">
    {Object.keys(props.explanation).map((key) =>
      <li key={key} role="presentation" className="">
        <a href={'#/import/' + key + '/none'}>{t(key)}</a>
      </li>
    )}
  </ul>

const Field = props => {
  if (has(props, 'oneOf')) {
    return (
      <div className="panel panel-body">
        {t('one_of_field_list')} <span className={classes('label', {'label-danger': props.oneOf.required}, {'label-warning': !props.oneOf.required})}>{props.oneOf.required ? t('required'): t('optional')}</span>
        {props.oneOf.map(oneOf => <Fields properties={oneOf.properties}/>)}
      </div>
    )
  } else {
    return(
      <div className="well">
        <div><strong>{props.name}</strong>{'\u00A0'}{'\u00A0'}<span className={classes('label', {'label-danger': props.required}, {'label-warning': !props.required})}>{props.required ? t('required'): t('optional')}</span></div>
        <div>{props.description}</div>
      </div>
    )
  }
}

const Fields = props => {
  return (
    <div>
      {props.properties.map(prop => <Field {...prop}/> )}
    </div>
  )
}

const RoutedExplain = props => {
  const entity = props.match.params.entity
  const action = props.match.params.action
  const choices = {}
  choices['none'] = ''
  Object.keys(props.explanation[entity]).reduce((o, key) => Object.assign(o, {[entity + '_' + key]: t(key)}), choices)

  return (
    <div>
      <h3>{t(entity)}</h3>
      <div>
        <FormContainer
          level={3}
          name="import"
          sections={[
            {
              id: 'general',
              title: t('general'),
              primary: true,
              fields: [{
                name: 'action',
                type: 'enum',
                label: t('action'),
                onChange: (value) => navigate('/import/' + entity + '/' +  value.substring(value.indexOf('_') + 1)),
                required: true,
                options: {
                  noEmpty: true,
                  choices: choices
                }},
                {
                  name: 'file',
                  type: 'file',
                  label: t('file')
                }
              ]
            }
          ]}
        />
      </div>

      {props.explanation[entity][action] &&
        <div>
          <div> {t('import_headers')} </div>
          <Fields {...props.explanation[entity][action]} />
        </div>
      }
    </div>
  )
}

const ConnectedExplain = connect(
  state => ({explanation: select.explanation(state)}),
  dispatch =>({
    updateProp: (prop, value, form, entity) => dispatch(actions.updateProp(prop, value, form, entity))
  })
)(RoutedExplain)

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
