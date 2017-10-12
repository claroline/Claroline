import React, {Component} from 'react'
import {connect} from 'react-redux'
import {withRouter} from 'react-router-dom'
import moment from 'moment'
import {PropTypes as T} from 'prop-types'
import {trans, t} from '#/main/core/translation'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {DataListContainer as DataList} from '#/main/core/layout/list/containers/data-list.jsx'
import {constants as listConstants} from '#/main/core/layout/list/constants'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {asset} from '#/main/core/asset'
import {actions} from '../actions'
import {selectors} from '../../../selectors'
import {getFieldType, getCountry} from '../../../utils'
import {select as resourceSelect} from '#/main/core/layout/resource/selectors'

class Entries extends Component {
  deleteEntry(entry) {
    this.props.showModal(MODAL_DELETE_CONFIRM, {
      title: trans('delete_entry', {}, 'clacoform'),
      question: trans('delete_entry_confirm_message', {title: entry.title}, 'clacoform'),
      handleConfirm: () => this.props.deleteEntry(entry.id)
    })
  }

  isEntryManager(entry) {
    let isManager = false

    if (entry.categories && this.props.user) {
      entry.categories.forEach(c => {
        if (!isManager && c.managers) {
          c.managers.forEach(m => {
            if (m.id === this.props.user.id) {
              isManager = true
            }
          })
        }
      })
    }

    return isManager
  }

  isEntryOwner(entry) {
    return this.props.user && entry.user && entry.user.id === this.props.user.id
  }

  canEditEntry(entry) {
    return this.canManageEntry(entry) || (this.props.editionEnabled && this.isEntryOwner(entry))
  }

  canManageEntry(entry) {
    return this.props.canEdit || this.isEntryManager(entry)
  }

  canViewMetadata() {
    return this.props.canEdit ||
      this.props.displayMetadata === 'all' ||
      (this.props.displayMetadata === 'manager' && this.props.isCategoryManager)
  }

  canViewEntryMetadata(entry) {
    return this.props.canEdit ||
      this.props.displayMetadata === 'all' ||
      this.isEntryOwner(entry) ||
      (this.props.displayMetadata === 'manager' && this.isEntryManager(entry))
  }

  navigateTo(url) {
    this.props.history.push(url)
  }

  generateColumns() {
    const columns = []

    if (!this.props.isAnon) {
      const options = []

      if (this.props.canEdit || this.props.searchEnabled) {
        options.push(trans('all_entries', {}, 'clacoform'))
      }
      if (this.props.isCategoryManager) {
        options.push(trans('manager_entries', {}, 'clacoform'))
      }
      options.push(trans('my_entries', {}, 'clacoform'))

      if (this.props.canEdit || this.props.searchEnabled || this.props.isCategoryManager) {
        columns.push({
          name: 'type',
          label: t('type'),
          displayable: false,
          displayed: false,
          type: 'enum',
          options: {
            enum: options
          }
        })
      }
    }
    columns.push({
      name: 'clacoForm',
      label: t('resource'),
      displayed: false,
      displayable: false,
      filterable: true,
      type: 'number'
    })

    columns.push({
      name: 'status',
      label: t('published'),
      displayed: true,
      type: 'boolean'
    })
    columns.push({
      name: 'title',
      label: t('title'),
      displayed: this.isDisplayedField('title'),
      renderer: (rowData) => {
        const viewLink = <a href={`#/entry/${rowData.id}/view`}>{rowData.title}</a>

        return viewLink
      }
    })

    if (this.canViewMetadata()) {
      columns.push({
        name: 'creationDate',
        label: t('date'),
        type: 'date',
        filterable: false,
        displayed: this.isDisplayedField('creationDateString'),
        renderer: (rowData) => this.canViewEntryMetadata(rowData) ? moment(rowData.creationDate).format('DD/MM/YYYY') : '-'
      })
      columns.push({
        name: 'createdAfter',
        label: t('created_after'),
        type: 'date',
        displayable: false
      })
      columns.push({
        name: 'createdBefore',
        label: t('created_before'),
        type: 'date',
        displayable: false
      })
    }
    if (this.canViewMetadata()) {
      columns.push({
        name: 'user',
        label: t('user'),
        displayed: this.isDisplayedField('userString'),
        renderer: (rowData) => rowData.user && this.canViewEntryMetadata(rowData) ?
          `${rowData.user.firstName} ${rowData.user.lastName}` :
          '-'
      })
    }
    if (this.props.displayCategories) {
      columns.push({
        name: 'categories',
        label: t('categories'),
        displayed: this.isDisplayedField('categoriesString'),
        renderer: (rowData) => rowData.categories ? rowData.categories.map(c => c.name).join(', ') : ''
      })
    }
    if (this.props.displayKeywords) {
      columns.push({
        name: 'keywords',
        label: trans('keywords', {}, 'clacoform'),
        displayed: this.isDisplayedField('keywordsString'),
        renderer: (rowData) => rowData.keywords ? rowData.keywords.map(k => k.name).join(', ') : ''
      })
    }
    this.props.fields.filter(f => !f.hidden && (!f.isMetadata || this.canViewMetadata())).forEach(f => {
      columns.push({
        name: `${f.id}`,
        label: f.name,
        type: this.getDataType(f),
        displayed: this.isDisplayedField(`${f.id}`),
        filterable: getFieldType(f.type).name !== 'date',
        renderer: (rowData) => {
          const fieldValue = rowData.fieldValues.find(fv => fv.field.id === f.id)

          if (getFieldType(f.type).name === 'rich_text') {
            const value = fieldValue && fieldValue.fieldFacetValue && fieldValue.fieldFacetValue.value ?
              <span
                className="fa fa-w fa-exclamation-circle"
                data-toggle="tooltip"
                title={trans('rich_text_field_info', {}, 'clacoform')}
              /> :
              ''

            return value
          } else {
            return fieldValue && fieldValue.fieldFacetValue && fieldValue.fieldFacetValue.value ?
              this.formatFieldValue(rowData, f, fieldValue.fieldFacetValue.value) :
              ''
          }
        }
      })
    })

    return columns
  }

  generateActions() {
    const dataListActions = [{
      icon: 'fa fa-w fa-eye',
      label: trans('view_entry', {}, 'clacoform'),
      action: (rows) => this.navigateTo(`/entry/${rows[0].id}/view`),
      context: 'row'
    }]

    if (this.props.canGeneratePdf) {
      dataListActions.push({
        icon: 'fa fa-w fa-print',
        label: trans('print_entry', {}, 'clacoform'),
        action: (rows) => this.props.downloadEntryPdf(rows[0].id),
        context: 'row'
      })
    }
    dataListActions.push({
      icon: 'fa fa-w fa-pencil',
      label: t('edit'),
      action: (rows) => this.navigateTo(`/entry/${rows[0].id}/edit`),
      displayed: (rows) => this.canEditEntry(rows[0]),
      context: 'row'
    })
    dataListActions.push({
      icon: 'fa fa-w fa-eye',
      label: t('publish'),
      action: (rows) => this.props.switchEntryStatus(rows[0].id),
      displayed: (rows) => this.canManageEntry(rows[0]) && rows[0].status !== 1,
      context: 'row'
    })
    dataListActions.push({
      icon: 'fa fa-w fa-eye-slash',
      label: t('unpublish'),
      action: (rows) => this.props.switchEntryStatus(rows[0].id),
      displayed: (rows) => this.canManageEntry(rows[0]) && rows[0].status === 1,
      context: 'row'
    })
    dataListActions.push({
      icon: 'fa fa-w fa-trash',
      label: t('delete'),
      action: (rows) => this.deleteEntry(rows[0]),
      displayed: (rows) => this.canManageEntry(rows[0]),
      dangerous: true,
      context: 'row'
    })

    return dataListActions
  }

  getDataType(field) {
    let type = 'string'

    switch (getFieldType(field.type).name) {
      case 'date':
        type = 'date'
        break
      case 'number':
        type = 'number'
        break
      case 'rich_text':
        type = 'html'
        break
    }

    return type
  }

  isDisplayedField(key) {
    return this.props.searchColumns.indexOf(key) > -1
  }

  formatFieldValue(entry, field, value) {
    let formattedValue = ''

    if (field.isMetadata && !this.canViewEntryMetadata(entry)) {
      formattedValue = '-'
    } else {
      formattedValue = value

      if (value !== undefined && value !== null && value !== '') {
        switch (getFieldType(field.type).name) {
          case 'date':
            formattedValue = value.date ? moment(value.date).format('DD/MM/YYYY') : moment(value).format('DD/MM/YYYY')
            break
          case 'country':
            formattedValue = getCountry(value).label
            break
          case 'checkboxes':
            formattedValue = value.join(', ')
            break
          case 'select':
            if (Array.isArray(value)) {
              formattedValue = value.join(', ')
            }
            break
        }
      }
    }

    return formattedValue
  }

  getCardValue(row, type) {
    let value = row.title
    let key = ''

    switch (type) {
      case 'title':
        key = this.props.displayTitle
        break
      case 'subtitle':
        key = this.props.displaySubtitle
        break
      case 'content':
        key = this.props.displayContent
        break
    }

    if (key && key !== 'title') {
      let fieldValue = null

      switch (key) {
        case 'date':
          value = row.creationDate
          break
        case 'user':
          value = row.user ? `${row.user.firstName} ${row.user.lastName}` : t('anonymous')
          break
        case 'categories':
          value = row.categories ? row.categories.map(c => c.name).join(', ') : ''
          break
        case 'keywords':
          value = row.keywords ? row.keywords.map(k => k.name).join(', ') : ''
          break
        default:
          fieldValue = row.fieldValues.find(fv => fv.field.id === parseInt(key))
          value = fieldValue && fieldValue.fieldFacetValue && fieldValue.fieldFacetValue.value ?
            this.formatFieldValue(row, fieldValue.field, fieldValue.fieldFacetValue.value) :
            ''
      }
    }

    return value
  }

  render() {
    return (
      <div>
        <h2>{trans('entries_list', {}, 'clacoform')}</h2>
        <br/>
        {this.props.canSearchEntry ?
          <div>
            <DataList
              display={{
                current: this.props.defaultDisplayMode || listConstants.DISPLAY_TABLE,
                available: Object.keys(listConstants.DISPLAY_MODES)
              }}
              name="entries"
              definition={this.generateColumns()}
              filterColumns={this.props.searchColumnEnabled}
              actions={this.generateActions()}
              card={(row) => ({
                onClick: `#/entry/${row.id}/view`,
                poster: null,
                icon: row.user.id > 0 && row.user.picture ?
                  <img src={asset('uploads/pictures/' + row.user.picture)} /> :
                  'fa fa-user',
                title: this.getCardValue(row, 'title'),
                subtitle: this.getCardValue(row, 'subtitle'),
                contentText: this.getCardValue(row, 'content'),
                flags: [].filter(flag => !!flag),
                footer:
                  <span></span>,
                footerLong:
                  <span></span>
              })}
            />
          </div> :
          <div className="alert alert-danger">
            {t('unauthorized')}
          </div>
        }
      </div>
    )
  }
}

Entries.propTypes = {
  canEdit: T.bool.isRequired,
  isAnon: T.bool.isRequired,
  user: T.object,
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    type: T.number.isRequired,
    isMetadata: T.bool.isRequired,
    hidden: T.bool
  })).isRequired,
  canGeneratePdf: T.bool.isRequired,
  resourceId: T.number.isRequired,
  canSearchEntry: T.bool.isRequired,
  searchEnabled: T.bool.isRequired,
  searchColumnEnabled: T.bool.isRequired,
  editionEnabled: T.bool.isRequired,
  defaultDisplayMode: T.string,
  displayTitle: T.string,
  displaySubtitle: T.string,
  displayContent: T.string,
  searchColumns: T.arrayOf(T.string),
  displayMetadata: T.string.isRequired,
  displayCategories: T.bool.isRequired,
  displayKeywords: T.bool.isRequired,
  isCategoryManager: T.bool.isRequired,
  downloadEntryPdf: T.func.isRequired,
  switchEntryStatus: T.func.isRequired,
  deleteEntry: T.func.isRequired,
  showModal: T.func.isRequired,
  entries: T.shape({
    data: T.array,
    totalResults: T.number,
    page: T.number,
    pageSize: T.number,
    filters: T.arrayOf(T.shape({
      property: T.string,
      value: T.any
    })),
    sortBy: T.object
  }).isRequired,
  history: T.object.isRequired
}

function mapStateToProps(state) {
  return {
    canEdit: resourceSelect.editable(state),
    isAnon: state.isAnon,
    user: state.user,
    fields: state.fields,
    canGeneratePdf: state.canGeneratePdf,
    resourceId: state.resource.id,
    canSearchEntry: selectors.canSearchEntry(state),
    searchEnabled: selectors.getParam(state, 'search_enabled'),
    searchColumnEnabled: selectors.getParam(state, 'search_column_enabled'),
    editionEnabled: selectors.getParam(state, 'edition_enabled'),
    defaultDisplayMode: selectors.getParam(state, 'default_display_mode'),
    displayTitle: selectors.getParam(state, 'display_title'),
    displaySubtitle: selectors.getParam(state, 'display_subtitle'),
    displayContent: selectors.getParam(state, 'display_content'),
    searchColumns: selectors.getParam(state, 'search_columns'),
    displayMetadata: selectors.getParam(state, 'display_metadata'),
    displayCategories: selectors.getParam(state, 'display_categories'),
    displayKeywords: selectors.getParam(state, 'display_keywords'),
    isCategoryManager: selectors.isCategoryManager(state),
    entries: state.entries
  }
}

function mapDispatchToProps(dispatch) {
  return {
    downloadEntryPdf: entryId => dispatch(actions.downloadEntryPdf(entryId)),
    switchEntryStatus: entryId => dispatch(actions.switchEntryStatus(entryId)),
    deleteEntry: entryId => dispatch(actions.deleteEntry(entryId)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  }
}

const ConnectedEntries = withRouter(connect(mapStateToProps, mapDispatchToProps)(Entries))

export {ConnectedEntries as Entries}