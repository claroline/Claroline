import React, {Fragment, useState} from 'react'

import has from 'lodash/has'
import classes from 'classnames'
import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {EditorPage} from '#/main/app/editor'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const Field = props => {
  if (has(props, 'oneOf')) {
    return (
      <div className={classes('transfer-schema-one-of', {required: props.required})}>
        <div className="schema-field-meta mb-1">
          {trans('one_of_field_list', {}, 'transfer')}
          {props.required &&
            <span className="badge text-bg-primary">{trans('required')}</span>
          }
        </div>

        <div className="transfer-schema-fields">
          <div className="transfer-schema-fields-list">
            {props.oneOf.map((oneOf, index) => oneOf.properties.map(property =>
              <Field key={index} {...property} required={false} />
            ))}
          </div>

          <div className="transfer-schema-or">
            {trans('or')}
          </div>
        </div>
      </div>
    )
  }

  let types = []
  if (props.type) {
    types = !Array.isArray(props.type) ? [props.type] : props.type
    types = types.filter(type => 'null' !== type)
  }

  return (
    <div className={classes('transfer-schema-field', {required: props.required})}>
      <div className="schema-field-meta">
        <strong>
          {props.name}
          <small className="text-secondary">
            ({types.map(type => {
              if (props.isArray) {
                return trans('array_of', {type: trans(type, {}, 'data')}, 'data')
              }

              return trans(type, {}, 'data')
            }).join(' / ')})
          </small>
        </strong>

        {props.required &&
          <span className="badge text-bg-primary">{trans('required')}</span>
        }
      </div>

      <p>{props.description}</p>
    </div>
  )
}

const ImportEditorFormat = (props) => {
  const [columns, setColumns] = useState('all')

  return (
    <EditorPage
      title={trans('format')}
      help={trans('transfer_format_help', {}, 'transfer')}
      definition={[
        {
          name: 'format',
          title: trans('format'),
          primary: true,
          fields: [
            {
              name: 'format',
              type: 'choice',
              label: trans('format'),
              hideLabel: true,
              disabled: true,
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
        }, {
          name: 'explanation',
          title: trans('format'),
          hideTitle: true,
          primary: true,
          render: () => {
            if (props.schema.properties && 0 !== props.schema.properties.length) {
              return (
                <Fragment>
                  <div
                    className="transfer-schema-filter">
                    {trans('list_filter_by')}
                    <Button
                      type={CALLBACK_BUTTON}
                      className="btn btn-link"
                      label={trans('all' === columns ? 'schema_all_properties' : 'schema_required_properties', {}, 'transfer')}
                      callback={() => setColumns('all' === columns ? 'required' : 'all')}
                      primary={true}
                    />
                  </div>

                  {props.schema.properties.filter(prop => 'all' === columns || prop.required).map((prop, index) =>
                    <Field key={index} {...prop} />)
                  }
                </Fragment>
              )
            }
          }
        }
      ]}
    />
  )
}

export {
  ImportEditorFormat
}
