import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/transfer/tools/transfer/export/store'

class ExportForm extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    const props = this.props

    const entity = props.match.params.entity
    const action = props.match.params.action

    const defaultFields = [
      {
        name: 'action',
        type: 'choice',
        label: trans('action'),
        onChange: (value) => {
          let action = ''
          if (value) {
            action = value.substring(value.indexOf('_') + 1)
          }

          props.history.push(`${this.props.path}/export/new/${entity}/${action}`)
        },
        required: true,
        options: {
          noEmpty: false,
          condensed: true,
          choices: Object.keys(get(props.explanation, entity, [])).reduce((o, key) => Object.assign(o, {
            [entity + '_' + key]: trans(key, {}, 'transfer')
          }), {})
        }
      }, {
        name: 'format',
        type: 'choice',
        label: trans('format'),
        required: true,
        options: {
          noEmpty: true,
          choices: {
            csv: trans('csv')
          }
        },
        linked: [
          {
            name: 'header',
            type: 'boolean',
            label: trans('csv_header', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => true
          }, {
            name: 'rowDelimiter',
            type: 'string',
            label: trans('row_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => '\\n'
          }, {
            name: 'columnDelimiter',
            type: 'string',
            label: trans('col_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => ';'
          }, {
            name: 'arrayDelimiter',
            type: 'string',
            label: trans('list_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => ','
          }
        ]
      }
    ]

    const explanationAction = get(props.explanation, entity+'.'+action)
    const additionalFields = explanationAction ? explanationAction.fields || []: []

    return (
      <FormData
        level={2}
        className="component-container"
        name={selectors.STORE_NAME + '.form'}
        title={trans(entity, {}, 'transfer')}
        buttons={true}
        save={{
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export', {}, 'actions'),
          callback: () => this.props.save()
        }}
        cancel={{
          type: LINK_BUTTON,
          target: `${this.props.path}/export/new`,
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
        {action &&
          <Fragment>

          </Fragment>
        }
      </FormData>
    )
  }
}

ExportForm.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  match: T.shape({
    params: T.object.isRequired
  }).isRequired,
  explanation: T.object.isRequired,

  save: T.func.isRequired
}

export {
  ExportForm
}