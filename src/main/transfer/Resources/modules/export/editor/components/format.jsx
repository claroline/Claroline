import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {Checkbox} from '#/main/app/input/components/checkbox'

const Field = props => {
  let types = []
  if (props.type) {
    types = !Array.isArray(props.type) ? [props.type] : props.type
    types = types.filter(type => 'null' !== type)
  }

  return (
    <div className={classes('transfer-schema-field', {selected: props.selected})}>
      <div className="schema-field-meta">
        <Checkbox
          id={props.name}
          label={
            <strong>
              {props.name}
              <small className="text-body-secondary">
                ({types.map(type => {
                  if (props.isArray) {
                    return trans('array_of', {type: trans(type, {}, 'data')}, 'data')
                  }
                  return trans(type, {}, 'data')
                }).join(' / ')})
              </small>
            </strong>
          }
          onChange={(checked) => props.update(props.name, checked)}
          checked={props.selected}
          inline={true}
        />
      </div>
      <p>{props.description}</p>
    </div>
  )
}

Field.propTypes = {
  type: T.string.isRequired,
  name: T.string.isRequired,
  description: T.string,
  isArray: T.bool,
  selected: T.bool,
  update: T.func.isRequired
}

const ExportEditorFormat = (props) => {
  return (
    <EditorPage
      title={trans('format')}
      help={trans('transfer_format_help', {}, 'transfer')}
      definition={[
        {
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
          title: trans('explanation'),
          hideTitle: true,
          primary: true,
          render: () => {
            if (props.schema.properties && 0 !== props.schema.properties.length) {
              return (
                <div role="presentation">
                  <Checkbox
                    id="export-column-select"
                    className="transfer-schema-select"
                    label={trans(0 < props.columns.length ? 'list_deselect_all' : 'list_select_all')}
                    checked={0 < props.columns.length}
                    onChange={() => {
                      if (0 === props.columns.length){
                        props.update(props.schema.properties.map(column => column.name))
                      } else {
                        props.update([])
                      }
                    }}
                  />

                  {props.schema.properties.map((prop, index) =>
                    <Field
                      key={index}
                      {...prop}
                      selected={-1 !== props.columns.indexOf(prop.name)}
                      update={(columnName, selected) => {
                        const newColumns = [].concat(props.columns)
                        if (selected && -1 === newColumns.indexOf(columnName)) {
                          newColumns.push(columnName)
                        } else if (-1 !== newColumns.indexOf(columnName)) {
                          newColumns.splice(newColumns.indexOf(columnName), 1)
                        }
                        props.update(newColumns)
                      }}
                    />
                  )}
                </div>
              )
            }
          }
        }
      ]}
    />
  )
}

ExportEditorFormat.propTypes = {
  schema: T.shape({
    properties: T.arrayOf(T.shape({
      type: T.string.isRequired,
      name: T.string.isRequired,
      description: T.string,
      isArray: T.bool
    }))
  }),
  columns: T.array.isRequired,
  update: T.func.isRequired
}

export {
  ExportEditorFormat
}
