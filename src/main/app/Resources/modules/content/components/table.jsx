import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

// TODO : add Content prefix to components

const TableCell = props =>
  <td className={classes(props.className, {
    'text-center': 'center' === props.align,
    'text-end': 'right' === props.align
  })}>
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
    <TooltipOverlay
      id={props.id}
      tip={props.tip}
      position={props.placement}
    >
      <span>
        {props.children}
      </span>
    </TooltipOverlay>
  </TableCell>

TableTooltipCell.propTypes = {
  id: T.node.isRequired,
  placement: T.string,
  tip: T.string.isRequired,
  children: T.node
}

TableTooltipCell.defaultProps = {
  children: null
}

const TableHeaderCell = props =>
  <th scope="col" className={classes(props.className, {
    'text-center': 'center' === props.align,
    'text-end': 'right' === props.align
  })}>
    {props.children}
  </th>

TableHeaderCell.propTypes = {
  className: T.string,
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
    className={classes(props.className, 'sorting-cell', {
      'text-center': 'center' === props.align,
      'text-end': 'right' === props.align
    })}
    onClick={e => {
      e.stopPropagation()
      props.onSort()
    }}
  >
    {props.children}

    <span aria-hidden="true" className={classes('fa', 0 === props.direction ? 'fa-sort' : (1 === props.direction ? 'fa-sort-asc' : 'fa-sort-desc'))} />
  </th>

TableSortingCell.propTypes = {
  className: T.string,
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
    <tr>
      {props.children}
    </tr>
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
  <table
    className={classes('table table-hover', /*'table-striped table-borderless',*/ {
      'table-sm': props.condensed
    }, props.className)}
  >
    {props.children}
  </table>

Table.propTypes = {
  children: T.array.isRequired,
  className: T.string,
  condensed: T.bool
}

Table.defaultProps = {
  condensed: false
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
