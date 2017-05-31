import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import { DropdownButton, MenuItem } from 'react-bootstrap'
import {tex, trans, transChoice} from '#/main/core/translation'
import {getDefinition} from './../../items/item-types'
import {Icon as ItemIcon} from './../../items/components/icon.jsx'

import {
  Table,
  TableRow,
  TableCell,
  TableTooltipCell,
  TableHeader,
  TableHeaderCell,
  TableSortingCell
} from '#/main/core/layout/table/components/table.jsx'

const SelectedRow = props =>
  <tr className="selected-rows active">
    <td className="text-center">
      <span className="fa fa-fw fa-check-square" />
    </td>
    <td
      colSpan={5}
      dangerouslySetInnerHTML={{ __html: transChoice('questions_selected', props.selected.length, {count: props.selected.length})}}
    >
    </td>
    <td className="table-actions text-right">
      <button
        role="button"
        className="btn btn-link"
        onClick={() => props.onShare(props.selected)}
      >
        <span className="fa fa-fw fa-share" />
        <span className="sr-only">{tex('questions_share')}</span>
      </button>
      <button
        role="button"
        className="btn btn-link btn-link-danger"
        onClick={() => props.onDelete(props.selected)}
      >
        <span className="fa fa-fw fa-trash-o" />
        <span className="sr-only">{tex('questions_delete')}</span>
      </button>
    </td>
  </tr>

SelectedRow.propTypes = {
  selected: T.arrayOf(T.string).isRequired,
  onDelete: T.func.isRequired,
  onShare: T.func.isRequired
}

const QuestionTableHeader = props =>
  <TableHeader>
    <tr>
      <TableHeaderCell align="center">
        <input type="checkbox" onChange={props.toggleSelectAll} />
      </TableHeaderCell>
      <TableSortingCell
        direction={'type' === props.sortBy.property ? props.sortBy.direction : 0}
        onSort={() => props.onSort('type')}
      >
        {tex('type')}
      </TableSortingCell>
      <TableSortingCell
        direction={'content' === props.sortBy.property ? props.sortBy.direction : 0}
        onSort={() => props.onSort('content')}
      >
        {tex('question')}
      </TableSortingCell>
      <TableSortingCell
        direction={'category' === props.sortBy.property ? props.sortBy.direction : 0}
        onSort={() => props.onSort('category')}
      >
        {tex('category')}
      </TableSortingCell>
      <TableSortingCell
        direction={'updated' === props.sortBy.property ? props.sortBy.direction : 0}
        onSort={() => props.onSort('updated')}
      >
        {tex('last_modified')}
      </TableSortingCell>
      <TableSortingCell
        direction={'author' === props.sortBy.property ? props.sortBy.direction : 0}
        onSort={() => props.onSort('author')}
      >
        {tex('creator')}
      </TableSortingCell>
      <TableHeaderCell align="right">&nbsp;</TableHeaderCell>
    </tr>
    {0 < props.selected.length &&
      <SelectedRow
        selected={props.selected}
        onDelete={props.onDelete}
        onShare={props.onShare}
      />
    }
  </TableHeader>

QuestionTableHeader.propTypes = {
  selected: T.array.isRequired,
  sortBy: T.shape({
    property: T.string,
    direction: T.number
  }),
  toggleSelectAll: T.func.isRequired,
  onSort: T.func.isRequired,
  onShare: T.func.isRequired,
  onDelete: T.func.isRequired
}

const QuestionRow = props =>
  <TableRow className={props.isSelected ? 'selected' : null}>
    <TableCell align="center">
      <input type="checkbox" onChange={() => props.toggleSelect(props.question)} />
    </TableCell>
    <TableTooltipCell
      align="center"
      id={props.question.id}
      tooltip={trans(getDefinition(props.question.type).name, {}, 'question_types')}
    >
      <ItemIcon name={getDefinition(props.question.type).name} />
    </TableTooltipCell>
    <TableCell>
      {props.question.title || props.question.content}
    </TableCell>
    <TableCell>
      {props.question.meta.category && props.question.meta.category.name ? props.question.meta.category.name : '-'}
    </TableCell>
    <TableCell align="right">
      {props.question.meta.updated ?
          <small className="text-muted">{props.question.meta.updated}</small> : '-'
      }
    </TableCell>
    <TableCell>
      {props.question.meta.authors ?
        <small className="text-muted">
          {props.question.meta.authors[0].name}
        </small> : '-'
      }
    </TableCell>
    <TableCell align="right" className="table-actions">
      <DropdownButton
        id={`dropdown-other-actions-${props.question.id}`}
        title={<span className="fa fa-fw fa-ellipsis-v" />}
        bsStyle="link"
        noCaret={true}
        pullRight={true}
      >
        <MenuItem header>Actions</MenuItem>
        <MenuItem
          onClick={() => props.onShare([props.question.id])}
        >
          <span className="fa fa-fw fa-share" />&nbsp;
          {tex('question_share')}
        </MenuItem>
        <MenuItem divider />

        <MenuItem
          className="dropdown-link-danger"
          onClick={() => props.onDelete([props.question.id])}
        >
          <span className="fa fa-fw fa-trash-o" />&nbsp;
          {tex('question_delete')}
        </MenuItem>
      </DropdownButton>
    </TableCell>
  </TableRow>

QuestionRow.propTypes = {
  question: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    title: T.string,
    content: T.string.isRequired,
    meta: T.shape({
      updated: T.string,
      category: T.shape({
        name: T.string
      }),
      authors: T.arrayOf(T.shape({
        name: T.isRequired
      })),
      sharedWith: T.array.isRequired,
      usedBy: T.array.isRequired
    }).isRequired
  }).isRequired,
  isSelected: T.bool,
  toggleSelect: T.func.isRequired,
  onShare: T.func.isRequired,
  onDelete: T.func.isRequired
}

QuestionRow.defaultProps = {
  isSelected: false
}

export default class QuestionList extends Component {
  render() {
    return(
      <Table
        isEmpty={0 === this.props.questions.length}
      >
        <QuestionTableHeader
          selected={this.props.selected}
          toggleSelectAll={this.props.toggleSelectAll}
          sortBy={this.props.sortBy}
          onSort={this.props.onSort}
          onShare={(items) => this.props.onShare(items)}
          onDelete={this.props.onDelete}
        />

        <tbody>
        {this.props.questions.map(question => (
          <QuestionRow
            key={question.id}
            question={question}
            isSelected={-1 !== this.props.selected.indexOf(question.id)}
            onShare={(items) => this.props.onShare(items)}
            onDelete={this.props.onDelete}
            toggleSelect={this.props.toggleSelect}
          />
        ))}
        </tbody>
      </Table>
    )
  }
}

QuestionList.propTypes = {
  questions: T.array.isRequired,
  selected: T.array.isRequired,
  sortBy: T.object.isRequired,
  onSort: T.func.isRequired,
  onDelete: T.func.isRequired,
  onShare: T.func.isRequired,
  toggleSelect: T.func.isRequired,
  toggleSelectPage: T.func.isRequired,
  toggleSelectAll: T.func.isRequired
}
