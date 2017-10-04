import React, {Component} from 'react'
import {connect} from 'react-redux'
import {withRouter} from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import {trans, t} from '#/main/core/translation'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {generateUrl} from '#/main/core/fos-js-router'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {getFieldType, getCountry} from '../../../utils'
import {selectors} from '../../../selectors'
import {actions} from '../actions'
import {EntryComments} from './entry-comments.jsx'
import {EntryMenu} from './entry-menu.jsx'

class EntryView extends Component {
  constructor(props) {
    super(props)
    this.state = {
      isKeywordsPanelOpen: props.openKeywords,
      isCategoriesPanelOpen: props.openCategories,
      isCommentsPanelOpen: props.openComments,
      entryUser: {}
    }
  }

  componentDidMount() {
    this.initializeEntry()
  }

  componentDidUpdate(prevProps) {
    if (prevProps.entryId !== this.props.entryId) {
      this.initializeEntry()
    }
  }

  initializeEntry() {
    if (this.props.entryId) {
      this.props.loadEntry(this.props.entryId)

      if (!this.props.isAnon) {
        fetch(generateUrl('claro_claco_form_entry_user_retrieve', {entry: this.props.entryId}), {
          method: 'GET' ,
          credentials: 'include'
        })
        .then(response => response.json())
        .then(entryUser => this.setState({entryUser: JSON.parse(entryUser)}))
      }
    } else if (this.props.randomEnabled) {
      this.goToRandomEntry()
    } else {
      this.props.history.push('/menu')
    }
  }

  goToRandomEntry() {
    fetch(generateUrl('claro_claco_form_entry_random', {clacoForm: this.props.resourceId}), {
      method: 'GET' ,
      credentials: 'include'
    })
    .then(response => response.json())
    .then(entryId => {
      if (entryId > 0) {
        this.props.history.push(`/entry/${entryId}/view`)
      } else {
        this.props.history.push('/menu')
      }
    })
  }

  updateState(property, value) {
    this.setState({[property]: value})
  }

  canViewMetadata() {
    return this.canShare() ||
      this.props.displayMetadata === 'all' ||
      (this.props.displayMetadata === 'manager' && this.props.isManager)
  }

  canComment() {
    return this.props.commentsEnabled && (!this.props.isAnon || this.props.anonymousCommentsEnabled)
  }

  canManageEntry() {
    return this.props.canEdit || this.props.isManager
  }

  canShare() {
    return this.props.canEdit || this.props.isOwner || this.state.entryUser.shared
  }

  isFieldDisplayable(field) {
    return this.canViewMetadata() || !field.isMetadata
  }

  displayFieldContent(field) {
    const fieldValue = this.props.entry.fieldValues ?
      this.props.entry.fieldValues.find(fv => fv.field.id === field.id) :
      null

    if (fieldValue && fieldValue.fieldFacetValue && fieldValue.fieldFacetValue.value !== undefined) {
      const value = fieldValue.fieldFacetValue.value

      switch (getFieldType(field.type).name) {
        case 'checkboxes':
          return value.join(', ')
        case 'select':
          return Array.isArray(value) ? value.join(', ') : value
        case 'country':
          return getCountry(value) ? getCountry(value).label : ''
        case 'date' :
          return value != undefined && value !== null && value.date ?
            moment(value.date).format('DD/MM/YYYY') :
            value ? moment(value).format('DD/MM/YYYY') : ''
        case 'rich_text':
          return (<div dangerouslySetInnerHTML={{ __html: value}}/>)
        default:
          return value
      }
    }
  }

  deleteEntry() {
    this.props.showModal(MODAL_DELETE_CONFIRM, {
      title: trans('delete_entry', {}, 'clacoform'),
      question: trans('delete_entry_confirm_message', {title: this.props.entry.title}, 'clacoform'),
      handleConfirm: () => {
        this.props.deleteEntry(this.props.entry.id)
        this.props.history.push('/entries')
      }
    })
  }

  showSharingForm() {
    fetch(generateUrl('claro_claco_form_entry_shared_users_list', {entry: this.props.entryId}), {
      method: 'GET' ,
      credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
      this.props.showModal(
        'MODAL_USER_PICKER',
        {
          title: trans('select_users_to_share', {}, 'clacoform'),
          help: trans('share_entry_msg', {}, 'clacoform'),
          handleRemove: (user) => this.props.unshareEntry(this.props.entryId, user.id),
          handleSelect: (user) => this.props.shareEntry(this.props.entryId, user.id),
          selected: JSON.parse(data.users)
        }
      )
    })
  }

  showOwnerForm() {
    this.props.showModal(
      'MODAL_USER_PICKER',
      {
        title: trans('change_entry_owner', {}, 'clacoform'),
        handleRemove: () => {},
        handleSelect: (user) => {
          this.props.changeEntryOwner(this.props.entryId, user.id)
          this.props.fadeModal()
        }
      }
    )
  }

  switchEntryNotification() {
    const enabled = !this.isNotificationsEnabled()
    const entryUser = Object.assign({}, this.state.entryUser, {notifyEdition: enabled, notifyComment: enabled})
    this.setState({entryUser: entryUser}, () => this.props.saveEntryUser(this.props.entryId, this.state.entryUser))
  }

  isNotificationsEnabled() {
    return this.state.entryUser.notifyEdition || (this.props.displayComments && this.state.entryUser.notifyComment)
  }

  updateNotification(property, value) {
    const entryUser = Object.assign({}, this.state.entryUser, {[property]: value})
    this.setState({entryUser: entryUser}, () => this.props.saveEntryUser(this.props.entryId, this.state.entryUser))
  }

  getFieldValue(fieldId) {
    let value = ''
    const fieldValue = this.props.entry.fieldValues &&  this.props.entry.fieldValues.find(fv => fv.field.id === fieldId)

    if (fieldValue && fieldValue.fieldFacetValue) {
      value = fieldValue.fieldFacetValue.value
    }

    return value
  }

  generateTemplate() {
    let template = this.props.template
    template = template.replace('%clacoform_entry_title%', this.props.entry.title)
    this.props.fields.forEach(f => {
      let replacedField = ''
      const fieldValue = this.getFieldValue(f.id)

      if (this.canViewMetadata() || !f.isMetadata) {
        switch (getFieldType(f.type).name) {
          case 'checkboxes':
            replacedField = fieldValue ? fieldValue.join(', ') : ''
            break
          case 'select':
            replacedField = fieldValue ?
              Array.isArray(fieldValue) ?
                fieldValue.join(', ') :
                fieldValue :
              ''
            break
          case 'date':
            replacedField = fieldValue && fieldValue.date ?
              moment(fieldValue.date).format('DD/MM/YYYY') :
              fieldValue ? moment(fieldValue).format('DD/MM/YYYY') : ''
            break
          case 'country':
            replacedField = fieldValue && getCountry(fieldValue) ? getCountry(fieldValue).label : ''
            break
          default:
            replacedField = fieldValue
        }
        if (replacedField === undefined) {
          replacedField = ''
        }
      }
      template = template.replace(`%field_${f.id}%`, replacedField)
    })
    template += '<br/>'

    return template
  }

  render() {
    return (
      this.props.canViewEntry || this.canShare() ?
        <div>
          {['up', 'both'].indexOf(this.props.menuPosition) > -1 &&
            <EntryMenu/>
          }
          <div className="panel panel-default">
            <div className="panel-heading">
              <h2 className="panel-title entry-view-title">
                <b>{this.props.entry.title}</b>
                {this.state.entryUser.id &&
                  <span className="entry-view-control">
                    <div className="btn-group margin-right-sm" role="group">
                      <TooltipButton
                        id="tooltip-button-notifications"
                        className="btn btn-default btn-sm"
                        title={this.isNotificationsEnabled() ?
                          trans('deactivate_notifications', {}, 'clacoform') :
                          trans('activate_notifications', {}, 'clacoform')
                        }
                        onClick={() => this.switchEntryNotification()}
                      >
                        <span className={`fa fa-w fa-${this.isNotificationsEnabled() ? 'bell-slash-o' : 'bell-o'}`}></span>
                      </TooltipButton>
                      {this.props.displayComments &&
                        <button type="button" className="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                          <span className="fa fa-caret-down"></span>
                        </button>
                      }
                      {this.props.displayComments &&
                        <ul className="dropdown-menu dropdown-menu-right notifications-buttons">
                          <li>
                            <CheckGroup
                              checkId="notify-edition-chk"
                              checked={this.state.entryUser.notifyEdition}
                              label={trans('editions', {}, 'clacoform')}
                              onChange={checked => this.updateNotification('notifyEdition', checked)}
                            />
                          </li>
                          <li>
                            <CheckGroup
                              checkId="notify-comment-chk"
                              checked={this.state.entryUser.notifyComment}
                              label={trans('comments', {}, 'clacoform')}
                              onChange={checked => this.updateNotification('notifyComment', checked)}
                            />
                          </li>
                        </ul>
                      }
                    </div>
                    {this.props.canAdministrate &&
                      <TooltipButton
                        id="tooltip-button-owner"
                        className="btn btn-default btn-sm margin-right-sm"
                        title={trans('change_entry_owner', {}, 'clacoform')}
                        onClick={() => this.showOwnerForm()}
                      >
                        <span className="fa fa-w fa-user"></span>
                      </TooltipButton>
                    }
                    {this.props.canGeneratePdf &&
                      <TooltipButton
                        id="tooltip-button-print"
                        className="btn btn-default btn-sm margin-right-sm"
                        title={trans('print_entry', {}, 'clacoform')}
                        onClick={() => this.props.downloadEntryPdf(this.props.entry.id)}
                      >
                        <span className="fa fa-w fa-print"></span>
                      </TooltipButton>
                    }
                    {this.canShare() &&
                      <TooltipButton
                        id="tooltip-button-share"
                        className="btn btn-default btn-sm margin-right-sm"
                        title={trans('share_entry', {}, 'clacoform')}
                        onClick={() => this.showSharingForm()}
                      >
                        <span className="fa fa-w fa-share-alt"></span>
                      </TooltipButton>
                    }
                    {this.canManageEntry &&
                      <TooltipButton
                        id="tooltip-button-status"
                        className="btn btn-default btn-sm margin-right-sm"
                        title={this.props.entry.status === 1 ? t('unpublish') : t('publish')}
                        onClick={() => this.props.switchEntryStatus(this.props.entry.id)}
                      >
                        <span className={`fa fa-w fa-${this.props.entry.status === 1 ? 'eye-slash' : 'eye'}`}></span>
                      </TooltipButton>
                    }
                    {this.props.canEditEntry &&
                      <a
                        className="btn btn-default btn-sm margin-right-sm"
                        href={`#/entry/${this.props.entry.id}/edit`}
                      >
                        <span className="fa fa-w fa-pencil"></span>
                      </a>
                    }
                    {this.canManageEntry &&
                      <button
                        className="btn btn-danger btn-sm margin-right-sm"
                        onClick={() => this.deleteEntry()}
                      >
                        <span className="fa fa-w fa-trash"></span>
                      </button>
                    }
                  </span>
                }
              </h2>
            </div>
            <div className="panel-body">
              {this.props.template && this.props.useTemplate ?
                <HtmlText>
                  {this.generateTemplate()}
                </HtmlText> :
                this.props.fields.map(f => this.isFieldDisplayable(f) ?
                  <div key={`field-${f.id}`}>
                    <div className="row">
                      <label className="col-md-3">
                        {f.name}
                      </label>
                      <div className="col-md-9">
                        {this.displayFieldContent(f)}
                      </div>
                    </div>
                    <hr/>
                  </div> :
                  ''
                )
              }
              {this.canViewMetadata() &&
                <div>
                  {this.props.entry.publicationDate &&
                    <span>{trans('publication_date', {}, 'clacoform')} : {moment(this.props.entry.publicationDate).format('DD/MM/YYYY')} - </span>
                  }
                  {this.props.entry.editionDate &&
                    <span>{trans('edition_date', {}, 'clacoform')} : {moment(this.props.entry.editionDate).format('DD/MM/YYYY')} - </span>
                  }
                  {t('author')} : {this.props.entry.user ? `${this.props.entry.user.firstName} ${this.props.entry.user.lastName}` : t('anonymous')}
                </div>
              }
            </div>
            {this.props.displayKeywords &&
              <div className="panel-heading">
                <div className="panel-title">
                  <span
                    className="pointer-hand"
                    onClick={() => this.updateState('isKeywordsPanelOpen', !this.state.isKeywordsPanelOpen)}
                  >
                    {trans('keywords', {}, 'clacoform')}
                    &nbsp;
                    <span className={`fa fa-w ${this.state.isKeywordsPanelOpen ? 'fa-chevron-circle-down' : 'fa-chevron-circle-right'}`}>
                    </span>
                  </span>
                </div>
              </div>
            }
            {this.props.displayKeywords &&
              <div className={`panel-body collapse ${this.state.isKeywordsPanelOpen ? 'in' : ''}`}>
                {this.props.entry.keywords && this.props.entry.keywords.map(k =>
                  <button
                    key={`keyword-${k.id}`}
                    className="btn btn-default margin-right-sm margin-bottom-sm"
                  >
                    {k.name}
                  </button>
                )}
              </div>
            }
            {this.props.displayCategories &&
              <div className="panel-heading">
                <div className="panel-title">
                  <span
                    className="pointer-hand"
                    onClick={() => this.updateState('isCategoriesPanelOpen', !this.state.isCategoriesPanelOpen)}
                  >
                    {t('categories')}
                    &nbsp;
                    <span className={`fa fa-w ${this.state.isCategoriesPanelOpen ? 'fa-chevron-circle-down' : 'fa-chevron-circle-right'}`}>
                    </span>
                  </span>
                </div>
              </div>
            }
            {this.props.displayCategories &&
              <div className={`panel-body collapse ${this.state.isCategoriesPanelOpen ? 'in' : ''}`}>
                {this.props.entry.categories && this.props.entry.categories.map(c =>
                  <button
                    key={`category-${c.id}`}
                    className="btn btn-default margin-right-sm margin-bottom-sm"
                  >
                    {c.name}
                  </button>
                )}
              </div>
            }
            {(this.props.displayComments || this.canComment()) &&
              <div className="panel-heading">
                <div className="panel-title">
                  <span
                    className="pointer-hand"
                    onClick={() => this.updateState('isCommentsPanelOpen', !this.state.isCommentsPanelOpen)}
                  >
                    {trans('comments', {}, 'clacoform')}
                    &nbsp;
                    <span className="badge">
                      {this.props.entry.comments &&
                        this.props.entry.comments.filter(c => {
                          return this.props.canEdit ||
                            this.props.isManager ||
                            c.status === 1 ||
                            (c.user && this.props.user && c.user.id === this.props.user.id)
                        }).length
                      }
                    </span>
                    &nbsp;
                    <span className={`fa fa-w ${this.state.isCommentsPanelOpen ? 'fa-chevron-circle-down' : 'fa-chevron-circle-right'}`}>
                    </span>
                  </span>
                </div>
              </div>
            }
            {(this.props.displayComments || this.canComment()) &&
              <div className={`panel-body collapse ${this.state.isCommentsPanelOpen ? 'in' : ''}`}>
                <EntryComments
                  entry={this.props.entry}
                  displayComments={this.props.displayComments}
                  canComment={this.canComment()}
                  canManage={this.canManageEntry()}
                />
              </div>
            }
          </div>
          {['down', 'both'].indexOf(this.props.menuPosition) > -1 &&
            <EntryMenu/>
          }
        </div> :
        <div className="alert alert-danger">
          {t('unauthorized')}
        </div>
    )
  }
}

EntryView.propTypes = {
  resourceId: T.number.isRequired,
  user: T.shape({
    id: T.number,
    firstName: T.string,
    lastName: T.string
  }),
  entryId: T.number,
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  isAnon: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,
  isOwner: T.bool,
  isManager: T.bool,
  canEditEntry: T.bool,
  canViewEntry: T.bool,
  displayMetadata: T.string.isRequired,
  displayCategories: T.bool.isRequired,
  openCategories: T.bool.isRequired,
  displayKeywords: T.bool.isRequired,
  openKeywords: T.bool.isRequired,
  commentsEnabled: T.bool.isRequired,
  anonymousCommentsEnabled: T.bool.isRequired,
  randomEnabled: T.bool.isRequired,
  displayComments: T.bool.isRequired,
  openComments: T.bool.isRequired,
  menuPosition: T.string.isRequired,
  template: T.string,
  useTemplate: T.bool.isRequired,
  entry: T.shape({
    id: T.number,
    title: T.string,
    status: T.number,
    creationDate: T.string,
    publicationDate: T.string,
    editionDate: T.string,
    keywords: T.arrayOf(T.shape({
      id: T.number.isRequired,
      name: T.string.isRequired
    })),
    categories: T.arrayOf(T.shape({
      id: T.number.isRequired,
      name: T.string.isRequired
    })),
    comments: T.arrayOf(T.shape({
      id: T.number.isRequired,
      content: T.string.isRequired,
      user: T.shape({
        id: T.number,
        firstName: T.string,
        lastName: T.string
      })
    })),
    user: T.shape({
      id: T.number,
      firstName: T.string,
      lastName: T.string
    }),
    fieldValues: T.arrayOf(T.shape({
      id: T.number.isRequired,
      field: T.shape({
        id: T.number.isRequired
      }).isRequired,
      fieldFacetValue: T.shape({
        id: T.number.isRequired,
        value: T.any
      }).isRequired
    }))
  }),
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    type: T.number.isRequired,
    name: T.string.isRequired,
    locked: T.bool.isRequired,
    lockedEditionOnly: T.bool.isRequired,
    required: T.bool,
    isMetadata: T.bool,
    hidden: T.bool,
    fieldFacet: T.shape({
      id: T.number.isRequired,
      name: T.string.isRequired,
      type: T.number.isRequired,
      field_facet_choices: T.arrayOf(T.shape({
        id: T.number.isRequired,
        label: T.string.isRequired,
        parent: T.shape({
          id: T.number.isRequired,
          label: T.string.isRequired
        })
      }))
    })
  })),
  loadEntry: T.func.isRequired,
  deleteEntry: T.func.isRequired,
  switchEntryStatus: T.func.isRequired,
  downloadEntryPdf: T.func.isRequired,
  saveEntryUser: T.func.isRequired,
  changeEntryOwner: T.func.isRequired,
  shareEntry: T.func.isRequired,
  unshareEntry: T.func.isRequired,
  showModal: T.func.isRequired,
  fadeModal: T.func.isRequired,
  history: T.object.isRequired
}

function mapStateToProps(state, ownProps) {
  return {
    resourceId: selectors.resource(state).id,
    user: state.user,
    entryId: parseInt(ownProps.match.params.id),
    canEdit: state.canEdit,
    isAnon: state.isAnon,
    canGeneratePdf: state.canGeneratePdf,
    entry: state.currentEntry,
    fields: selectors.visibleFields(state),
    displayMetadata: selectors.getParam(state, 'display_metadata'),
    displayCategories: selectors.getParam(state, 'display_categories'),
    openCategories: selectors.getParam(state, 'open_categories'),
    displayKeywords: selectors.getParam(state, 'display_keywords'),
    openKeywords: selectors.getParam(state, 'open_keywords'),
    commentsEnabled: selectors.getParam(state, 'comments_enabled'),
    anonymousCommentsEnabled: selectors.getParam(state, 'anonymous_comments_enabled'),
    displayComments: selectors.getParam(state, 'display_comments'),
    openComments: selectors.getParam(state, 'open_comments'),
    menuPosition: selectors.getParam(state, 'menu_position'),
    isOwner: selectors.isCurrentEntryOwner(state),
    isManager: selectors.isCurrentEntryManager(state),
    canEditEntry: selectors.canEditCurrentEntry(state),
    canViewEntry: selectors.canOpenCurrentEntry(state),
    canAdministrate: selectors.canAdministrate(state),
    randomEnabled: selectors.getParam(state, 'random_enabled'),
    useTemplate: selectors.getParam(state, 'use_template'),
    template: selectors.template(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    loadEntry: (entryId) => dispatch(actions.loadEntry(entryId)),
    deleteEntry: entryId => dispatch(actions.deleteEntry(entryId)),
    switchEntryStatus: entryId => dispatch(actions.switchEntryStatus(entryId)),
    downloadEntryPdf: entryId => dispatch(actions.downloadEntryPdf(entryId)),
    saveEntryUser: (entryId, entryUser) => dispatch(actions.saveEntryUser(entryId, entryUser)),
    changeEntryOwner: (entryId, userId) => dispatch(actions.changeEntryOwner(entryId, userId)),
    shareEntry: (entryId, userId) => dispatch(actions.shareEntry(entryId, userId)),
    unshareEntry: (entryId, userId) => dispatch(actions.unshareEntry(entryId, userId)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props)),
    fadeModal: () => dispatch(modalActions.fadeModal())
  }
}

const ConnectedEntryView = withRouter(connect(mapStateToProps, mapDispatchToProps)(EntryView))

export {ConnectedEntryView as EntryView}