import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

import {Schema as SchemaTypes} from '#/main/transfer/tools/transfer/prop-types'
import {Checkbox} from '#/main/app/input/components/checkbox'

const Fields = props =>
  <Fragment>
    {props.properties.map((prop, index) =>
      <Field
        key={index}
        {...prop}
        selected={-1 !== props.selected.indexOf(prop.name)}
        update={(columnName, selected) => {
          const newColumns = [].concat(props.selected)

          if (selected && -1 === newColumns.indexOf(columnName)) {
            newColumns.push(columnName)
          } else if (-1 !== newColumns.indexOf(columnName)) {
            newColumns.splice(newColumns.indexOf(columnName), 1)
          }

          props.update(newColumns)
        }}
      />
    )}
  </Fragment>

Fields.propTypes = {
  properties: T.arrayOf(T.shape({
    name: T.string.isRequired,
    type: T.oneOfType([T.arrayOf(T.string), T.string]),
    description: T.string
  })).isRequired,
  selected: T.arrayOf(T.string),
  update: T.func.isRequired
}

const Field = props => {
  let types = []
  if (props.type) {
    types = !Array.isArray(props.type) ? [props.type] : props.type
    types = types.filter(type => 'null' !== type)
  }

  return (
    <div className={classes('transfer-schema-field', {
      selected: props.selected
    })}>
      <div className="schema-field-meta">
        <Checkbox
          id={props.name}
          label={
            <strong>
              {props.name}
              <small className="text-muted">
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
  name: T.string.isRequired,
  type: T.oneOfType([T.arrayOf(T.string), T.string]),
  description: T.string,
  isArray: T.bool,
  selected: T.bool.isRequired,
  update: T.func.isRequired
}

const ExportExplanation = (props) => {
  if (props.schema.properties && 0 !== props.schema.properties.length) {
    return (
      <Fragment>
        <Checkbox
          id="export-column-select"
          className="transfer-schema-select"
          label={trans('list_select_all')}
          labelChecked={trans('list_deselect_all')}
          checked={0 < props.columns.length}
          onChange={() => {
            if (0 === props.columns.length) {
              props.update(props.schema.properties.map(column => column.name))
            } else {
              props.update([])
            }
          }}
        />

        <Fields
          properties={props.schema.properties}
          update={props.update}
          selected={props.columns}
        />
      </Fragment>
    )
  }

  return null
}

ExportExplanation.propTypes = {
  schema: T.shape(SchemaTypes.propTypes),
  columns: T.arrayOf(T.string),
  update: T.func.isRequired
}

export {
  ExportExplanation
}