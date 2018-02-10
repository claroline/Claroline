import React, {Component} from 'react'
import {connect} from 'react-redux'
import {withRouter} from 'react-router-dom'
import {PropTypes as T} from 'prop-types'

import {trans, t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'
import {displayDate} from '#/main/core/scaffolding/date'

import {select as resourceSelect} from '#/main/core/resource/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {TooltipLink} from '#/main/core/layout/button/components/tooltip-link.jsx'

import {UserMicro} from '#/main/core/user/components/micro.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

import {FileThumbnail} from '#/main/core/layout/form/components/field/file-thumbnail.jsx'
import {getFieldType, getCountry, getFileType} from '#/plugin/claco-form/resources/claco-form/utils'

import {selectors} from '../../../selectors'
import {actions} from '../actions'
import {EntryComments} from './entry-comments.jsx'
import {EntryMenu} from './entry-menu.jsx'

const FilesThumbnails = props =>
  <div className="file-thumbnails">
    {props.files.map((f, idx) =>
      <FileThumbnail
        key={`file-thumbnail-${idx}`}
        type={!f.mimeType ? 'file' : getFileType(f.mimeType)}
        data={f}
        canEdit={false}
        canExpand={false}
        canDelete={false}
      />
    )}
  </div>

FilesThumbnails.propTypes = {
  files: T.arrayOf(T.shape({
    name: T.string,
    mimeType: T.string,
    url: T.string
  }))
}

const EntryActions = props =>
  <div className="entry-actions">
    <div className="btn-group margin-right-sm" role="group">
      <TooltipButton
        id="tooltip-button-notifications"
        className="btn-link-default"
        title={props.notificationsEnabled ?
          trans('deactivate_notifications', {}, 'clacoform') :
          trans('activate_notifications', {}, 'clacoform')
        }
        onClick={() => props.updateNotification({
          notifyEdition: !props.notificationsEnabled,
          notifyComment: !props.notificationsEnabled
        })}
      >
        <span className={`fa fa-w fa-${props.notificationsEnabled ? 'bell-slash-o' : 'bell-o'}`} />
      </TooltipButton>

      {props.displayComments &&
        <button type="button" className="btn btn-link-default dropdown-toggle" data-toggle="dropdown">
          <span className="fa fa-caret-down" />
        </button>
      }

      {props.displayComments &&
        <ul className="dropdown-menu dropdown-menu-right notifications-buttons">
          <li>
            <CheckGroup
              id="notify-edition-chk"
              value={props.notifyEdition}
              label={trans('editions', {}, 'clacoform')}
              onChange={checked => props.updateNotification({notifyEdition: checked})}
            />
          </li>
          <li>
            <CheckGroup
              id="notify-comment-chk"
              value={props.notifyComment}
              label={trans('comments', {}, 'clacoform')}
              onChange={checked => props.updateNotification({notifyComment: checked})}
            />
          </li>
        </ul>
      }
    </div>

    {props.canAdministrate &&
      <TooltipButton
        id="tooltip-button-owner"
        className="btn-link-default"
        title={trans('change_entry_owner', {}, 'clacoform')}
        onClick={props.changeOwner}
      >
        <span className="fa fa-fw fa-user" />
      </TooltipButton>
    }

    {props.canGeneratePdf &&
      <TooltipButton
        id="tooltip-button-print"
        className="btn-link-default"
        title={trans('print_entry', {}, 'clacoform')}
        onClick={props.downloadPdf}
      >
        <span className="fa fa-fw fa-print" />
      </TooltipButton>
    }

    {props.canShare &&
      <TooltipButton
        id="tooltip-button-share"
        className="btn-link-default"
        title={trans('share_entry', {}, 'clacoform')}
        onClick={props.share}
      >
        <span className="fa fa-fw fa-share-alt" />
      </TooltipButton>
    }

    {!props.locked && props.canEdit &&
      <TooltipLink
        id="entry-edit"
        className="btn-link-default"
        title={t('edit')}
        target={`#/entry/${props.entryId}/edit`}
      >
        <span className="fa fa-fw fa-pencil" />
      </TooltipLink>
    }

    {!props.locked && props.canManage &&
      <TooltipButton
        id="tooltip-button-status"
        className="btn-link-default"
        title={props.status === 1 ? t('unpublish') : t('publish')}
        onClick={props.toggleStatus}
      >
        <span className={`fa fa-fw fa-${props.status === 1 ? 'eye-slash' : 'eye'}`} />
      </TooltipButton>
    }

    {!props.locked && props.canAdministrate &&
      <TooltipButton
        id="tooltip-button-lock"
        className="btn-link-default"
        title={trans('lock_entry', {}, 'clacoform')}
        onClick={props.toggleLock}
      >
        <span className="fa fa-fw fa-lock" />
      </TooltipButton>
    }

    {props.locked && props.canAdministrate &&
      <TooltipButton
        id="tooltip-button-lock"
        className="btn-link-default"
        title={trans('unlock_entry', {}, 'clacoform')}
        onClick={props.toggleLock}
      >
        <span className="fa fa-fw fa-unlock" />
      </TooltipButton>
    }

    {!props.locked && props.canManage &&
      <TooltipButton
        id="entry-delete"
        className="btn-link-danger"
        title={t('delete')}
        onClick={props.delete}
      >
        <span className="fa fa-fw fa-trash-o" />
      </TooltipButton>
    }
  </div>

EntryActions.propTypes = {
  // data
  entryId: T.number.isRequired,
  status: T.number.isRequired,
  locked: T.bool.isRequired,
  notificationsEnabled: T.bool.isRequired,

  // current user rights
  canAdministrate: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,
  canManage: T.bool.isRequired,
  canShare: T.bool.isRequired,

  notifyEdition: T.bool,
  notifyComment: T.bool,
  displayComments: T.bool.isRequired,

  // actions functions
  changeOwner: T.func.isRequired,
  downloadPdf: T.func.isRequired,
  share: T.func.isRequired,
  delete: T.func.isRequired,
  toggleStatus: T.func.isRequired,
  toggleLock: T.func.isRequired,
  updateNotification: T.func.isRequired
}

class EntryView extends Component {
  constructor(props) {
    super(props)

    this.state = {
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
          return getCountry(value) || ''
        case 'date' :
          return value != undefined && value !== null && value.date ?
            displayDate(value.date) :
            value ? displayDate(value) : ''
        case 'rich_text':
          return (<div dangerouslySetInnerHTML={{ __html: value}}/>)
        case 'file':
          return (<FilesThumbnails files={value}/>)
        default:
          return value
      }
    }
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

  isNotificationsEnabled() {
    return this.state.entryUser.notifyEdition || (this.props.displayComments && this.state.entryUser.notifyComment)
  }

  updateNotification(notifications) {
    const entryUser = Object.assign({}, this.state.entryUser, notifications)
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
    this.props.fields.filter(f => f.type !== 11).forEach(f => {
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
              displayDate(fieldValue.date) :
              fieldValue ? displayDate(fieldValue) : ''
            break
          case 'country':
            replacedField = getCountry(fieldValue) || ''
            break
          case 'file':
            replacedField = fieldValue && Array.isArray(fieldValue) ?
              React.createElement(FilesThumbnails, {files: fieldValue}) :
              ''
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
            <EntryMenu />
          }

          <div className="entry panel panel-default">
            <div className="panel-body">
              <h2 className="entry-title">{this.props.entry.title}</h2>

              <div className="entry-meta">
                {this.canViewMetadata() &&
                  <div className="entry-info">
                    <UserMicro {...this.props.entry.user} />

                    <div className="date">
                      {this.props.entry.publicationDate ?
                        t('published_at', {date: displayDate(this.props.entry.publicationDate, false, true)}) : t('not_published')
                      }

                      , {t('last_modified_at', {date: displayDate(this.props.entry.editionDate, false, true)})}
                    </div>
                  </div>
                }

                {this.state.entryUser.id &&
                  <EntryActions
                    entryId={this.props.entry.id}
                    status={this.props.entry.status}
                    locked={this.props.entry.locked}
                    notificationsEnabled={this.isNotificationsEnabled()}
                    displayComments={this.props.displayComments}
                    notifyEdition={this.state.entryUser.notifyEdition}
                    notifyComment={this.state.entryUser.notifyComment}
                    canAdministrate={this.props.canAdministrate}
                    canEdit={this.props.canEditEntry}
                    canGeneratePdf={this.props.canGeneratePdf}
                    canManage={this.canManageEntry()}
                    canShare={this.canShare()}

                    changeOwner={() => this.showOwnerForm()}
                    downloadPdf={() => this.props.downloadEntryPdf(this.props.entry.id)}
                    share={() => this.showSharingForm()}
                    delete={() => this.props.deleteEntry(this.props.entry)}
                    toggleStatus={() => this.props.switchEntryStatus(this.props.entry.id)}
                    toggleLock={() => this.props.switchEntryLock(this.props.entry.id)}
                    updateNotification={this.updateNotification}
                  />
                }
              </div>

              {this.props.template && this.props.useTemplate ?
                <HtmlText>
                  {this.generateTemplate()}
                </HtmlText> :
                this.props.fields.filter(f => this.isFieldDisplayable(f)).map(f =>
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
                  </div>
                )
              }
            </div>

            {((this.props.displayCategories && this.props.entry.categories && 0 < this.props.entry.categories.length) ||
            (this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length)) &&
              <div className="entry-footer panel-footer">
                {this.props.displayCategories && this.props.entry.categories && 0 < this.props.entry.categories.length &&
                  <span className="title">{t('categories')}</span>
                }
                {this.props.displayCategories && this.props.entry.categories && this.props.entry.categories.map(c =>
                  <span key={`category-${c.id}`} className="label label-primary">{c.name}</span>
                )}

                {this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length &&
                  <hr/>
                }
                {this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length &&
                  <span className="title">{t('keywords')}</span>
                }
                {this.props.displayKeywords && this.props.entry.keywords && this.props.entry.keywords.map(c =>
                  <span key={`keyword-${c.id}`} className="label label-default">{c.name}</span>
                )}
              </div>
            }
          </div>

          {['down', 'both'].indexOf(this.props.menuPosition) > -1 &&
            <EntryMenu />
          }

          {(this.props.canViewComments || this.props.canComment) &&
            <EntryComments
              opened={this.props.openComments}
              canComment={this.props.canComment}
              canManage={this.canManageEntry()}
              canViewComments={this.props.canViewComments}
            />
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
  entryId: T.number,
  user: T.shape({
    id: T.string,
    firstName: T.string,
    lastName: T.string
  }),
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,
  canEditEntry: T.bool,
  canViewEntry: T.bool,
  canComment: T.bool,
  canViewComments: T.bool,

  isAnon: T.bool.isRequired,
  isOwner: T.bool,
  isManager: T.bool,

  displayMetadata: T.string.isRequired,
  displayCategories: T.bool.isRequired,
  displayKeywords: T.bool.isRequired,
  displayComments: T.bool.isRequired,

  openComments: T.bool.isRequired,

  commentsEnabled: T.bool.isRequired,
  anonymousCommentsEnabled: T.bool.isRequired,
  randomEnabled: T.bool.isRequired,

  menuPosition: T.string.isRequired,
  template: T.string,
  useTemplate: T.bool.isRequired,
  entry: T.shape({
    id: T.number,
    title: T.string,
    status: T.number,
    locked: T.bool,
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
        id: T.string,
        firstName: T.string,
        lastName: T.string
      })
    })),
    user: T.shape({
      id: T.string,
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
  switchEntryLock: T.func.isRequired,
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

    canEdit: resourceSelect.editable(state),
    canEditEntry: selectors.canEditCurrentEntry(state),
    canViewEntry: selectors.canOpenCurrentEntry(state),
    canAdministrate: selectors.canAdministrate(state),
    canGeneratePdf: state.canGeneratePdf,
    canComment: selectors.canComment(state),
    canViewComments: selectors.canViewComments(state),

    isAnon: state.isAnon,
    entry: state.currentEntry,
    fields: selectors.visibleFields(state),

    displayMetadata: selectors.getParam(state, 'display_metadata'),
    displayKeywords: selectors.getParam(state, 'display_keywords'),
    displayCategories: selectors.getParam(state, 'display_categories'),
    displayComments: selectors.getParam(state, 'display_comments'),

    openComments: selectors.getParam(state, 'open_comments'),

    commentsEnabled: selectors.getParam(state, 'comments_enabled'),
    anonymousCommentsEnabled: selectors.getParam(state, 'anonymous_comments_enabled'),

    menuPosition: selectors.getParam(state, 'menu_position'),
    isOwner: selectors.isCurrentEntryOwner(state),
    isManager: selectors.isCurrentEntryManager(state),
    randomEnabled: selectors.getParam(state, 'random_enabled'),
    useTemplate: selectors.getParam(state, 'use_template'),
    template: selectors.template(state)
  }
}

function mapDispatchToProps(dispatch, ownProps) {
  return {
    loadEntry: (entryId) => dispatch(actions.loadEntry(entryId)),
    deleteEntry: entry => {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: trans('delete_entry', {}, 'clacoform'),
          question: trans('delete_entry_confirm_message', {title: entry.title}, 'clacoform'),
          handleConfirm: () => {
            dispatch(actions.deleteEntry(entry.id))
            ownProps.history.push('/entries')
          }
        })
      )
    },
    switchEntryStatus: entryId => dispatch(actions.switchEntryStatus(entryId)),
    switchEntryLock: entryId => dispatch(actions.switchEntryLock(entryId)),
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