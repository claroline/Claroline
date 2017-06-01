import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'

const TableCell = props =>
  <td className={classes(`text-${props.align}`, props.className)}>
    {props.children}
  </td>

TableCell.propTypes = {
  className: T.string,
  align: T.oneOf(['left', 'center', 'right']),
  children: T.node
}

TableCell.defaultProps = {
  align: 'left',
  children: null
}

const TableTooltipCell = props =>
  <TableCell {...props}>
    <OverlayTrigger
      placement={props.placement}
      overlay={<Tooltip id={props.id}>{props.tooltip}</Tooltip>}
    >
      <span>
        {props.children}
      </span>
    </OverlayTrigger>
  </TableCell>

TableTooltipCell.propTypes = {
  id: T.node.isRequired,
  placement: T.string,
  tooltip: T.string.isRequired,
  children: T.node
}

TableTooltipCell.defaultProps = {
  children: null
}

const TableHeaderCell = props =>
  <th scope="col" className={`text-${props.align}`}>
    {props.children}
  </th>

TableHeaderCell.propTypes = {
  align: T.oneOf(['left', 'center', 'right']),
  children: T.node
}

TableHeaderCell.defaultProps = {
  align: 'left',
  children: null
}

const TableSortingCell = props =>
  <th
    scope="col"
    className={`sorting-cell text-${props.align}`}
    onClick={e => {
      e.stopPropagation()
      props.onSort()
    }}
  >
    {props.children}

    <span className={
      classes(
        'fa',
        0 === props.direction ? 'fa-sort' : (1 === props.direction ? 'fa-sort-asc' : 'fa-sort-desc')
      )} aria-hidden="true"
    />
  </th>

TableSortingCell.propTypes = {
  align: T.oneOf(['left', 'center', 'right']),
  direction: T.oneOf([0, -1, 1]),
  onSort: T.func.isRequired,
  children: T.node
}

TableSortingCell.defaultProps = {
  align: 'left',
  direction: 0,
  children: null
}

const TableHeader = props =>
  <thead>
    {props.children}
  </thead>

TableHeader.propTypes = {
  children: T.node.isRequired
}

const TableRow = props =>
  <tr {...props}>
    {props.children}
  </tr>

TableRow.propTypes = {
  children: T.node.isRequired
}

const Table = props =>
  <table className="table table-striped table-hover">
    {props.children}
  </table>

Table.propTypes = {
  emptyText: T.string,
  children: T.array.isRequired
}

export {
  Table,
  TableRow,
  TableCell,
  TableTooltipCell,
  TableHeader,
  TableHeaderCell,
  TableSortingCell
}