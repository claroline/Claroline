import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import has from 'lodash/has'
import merge from 'lodash/merge'

import {getTypeOrDefault} from '#/main/core/data/index'

import {Table} from '#/main/core/layout/table/components/table'
import {CallbackButton} from '#/main/app/button/components/callback'

const RowDataCell = props => {

  const typeDef = getTypeOrDefault(props.type)

  let cellRendering
  if (props.renderer) {
    cellRendering = props.renderer(props.data, props.index, props.id)
  } else if (typeDef.components && typeDef.components.table) {
    // use custom component defined in the type definition
    cellRendering = React.createElement(typeDef.components.table, merge({data: props.data}, props.options || {}))
  } else {
    // use render defined in the type definition
    cellRendering = typeDef.render(props.data, props.options || {})
  }

  return <td>{cellRendering}</td>
}

const ComparisonTable = props =>
  <Table>

    {props.title && <thead>
      <tr>
        <th />
        {props.data.map((elem, index) => <th key={index}>{props.title(elem)}</th>)}
      </tr>
    </thead>}

    {props.action && <tfoot>
      <tr>
        <td />
        {props.data.map((elem, index) =>
          <td key={index}>
            <CallbackButton
              callback={() => props.action.action(elem, props.data)}
              disabled={props.action.disabled(elem, props.data)}>
              {props.action.text(elem)}
            </CallbackButton>
          </td>)}
      </tr>
    </tfoot>}

    <tbody>
      {props.rows.map((elem, index) =>

        has(props.data[0], elem.name) && <tr key={index}>

          <td>{elem.label}</td>

          {props.data.map((data, index) =>
            <RowDataCell
              key={index}
              index={index}
              data={get(data, elem.name)}
              type={elem.type}
              name={elem.name}
              renderer={elem.renderer}
              options={elem.options}
              id={get(data, 'id')} />
          )}
        </tr>
      )}
    </tbody>

  </Table>

ComparisonTable.propTypes = {
  data: T.array.isRequired,
  rows: T.arrayOf(T.shape({
    name: T.string.isRequired,
    label: T.string.isRequired,
    type: T.string,
    renderer: T.func
  })).isRequired,
  action: T.shape({
    text: T.func.isRequired,
    action: T.func.isRequired,
    disabled: T.func
  }),
  title: T.func.isRequired
}

export {
  ComparisonTable
}

