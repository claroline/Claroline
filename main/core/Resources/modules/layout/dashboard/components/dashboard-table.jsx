import React from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'
import {trans} from '#/main/core/translation'
import {
  Table,
  TableHeaderCell,
  TableRow,
  TableCell
} from '#/main/core/layout/table/components/table.jsx'

const DashboardTable = (props) =>
  <Table className="data-table" condensed={true}>
    <thead>
      <TableRow>
        {props.definition.map((val, index) =>
          <TableHeaderCell key={index} align={'left'}>
            {val.label}
          </TableHeaderCell>
        )}
      </TableRow>
    </thead>
    <tbody>
      {props.data.map((data,idx) =>
        <TableRow key={idx}>
          {props.definition.map((val, idxn) =>
            <TableCell key={idx + '-' + idxn} align={'left'}>
              { val.colorLegend &&
                <span
                  className="dashboard-color-legend"
                  style={{backgroundColor: props.colors[idx % props.colors.length]}}
                />
              }
              <span>{val.transDomain ? trans(data[val.name], {}, val.transDomain) : data[val.name]}</span>
            </TableCell>
          )}
        </TableRow>
      )}
    </tbody>
  </Table>

DashboardTable.propTypes = {
  definition: T.arrayOf(T.shape({
    name: T.string.isRequired,
    label: T.string.isRequired,
    colorLegend: T.bool,
    transDomain: T.string
  })).isRequired,
  data: T.array.isRequired,
  colors: T.array.isRequired
}

DashboardTable.defaultProps = {
  definitions: {
    colorLegend: false
  },
  colors: schemeCategory20c
}

export {
  DashboardTable
}