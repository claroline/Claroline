import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'
import {displayDate} from '#/main/core/scaffolding/date'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {UserMicro} from '#/main/core/user/components/micro'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {
  Field as FieldType,
  Entry as EntryType,
  EntryUser as EntryUserType
} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {getCountry} from '#/plugin/claco-form/resources/claco-form/utils'
import {select} from '#/plugin/claco-form/resources/claco-form/selectors'
import {actions} from '#/plugin/claco-form/resources/claco-form/player/entry/actions'
import {EntryComments} from '#/plugin/claco-form/resources/claco-form/player/entry/components/entry-comments'
import {EntryMenu} from '#/plugin/claco-form/resources/claco-form/player/entry/components/entry-menu'

const EntryActions = props =>
  <div className="entry-actions">
    <div className="btn-group margin-right-sm" role="group">
      <Button
        id="tooltip-button-notifications"
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon={`fa fa-w fa-${props.notificationsEnabled ? 'bell-slash-o' : 'bell-o'}`}
        label={props.notificationsEnabled ?
          trans('deactivate_notifications', {}, 'clacoform') :
          trans('activate_notifications', {}, 'clacoform')
        }
        tooltip="top"
        callback={() => props.updateNotification({
          notifyEdition: !props.notificationsEnabled,
          notifyComment: !props.notificationsEnabled
        })}
      />

      {props.displayComments &&
        <button type="button" className="btn btn-link dropdown-toggle" data-toggle="dropdown">
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
              onChange={checked => props.updateEntryUserProp('notifyEdition', checked)}
            />
          </li>
          <li>
            <CheckGroup
              id="notify-comment-chk"
              value={props.notifyComment}
              label={trans('comments', {}, 'clacoform')}
              onChange={checked => props.updateEntryUserProp('notifyComment', checked)}
            />
          </li>
        </ul>
      }
    </div>

    {props.canAdministrate &&
      <Button
        id="tooltip-button-owner"
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-user"
        label={trans('change_entry_owner', {}, 'clacoform')}
        tooltip="top"
        callback={props.changeOwner}
      />
    }

    {props.canGeneratePdf &&
      <Button
        id="tooltip-button-print"
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-print"
        label={trans('print_entry', {}, 'clacoform')}
        tooltip="top"
        callback={props.downloadPdf}
      />
    }

    {props.canShare &&
      <Button
        id="tooltip-button-share"
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-share-alt"
        label={trans('share_entry', {}, 'clacoform')}
        tooltip="top"
        callback={props.share}
      />
    }

    {!props.locked && props.canEdit &&
      <Button
        id="entry-edit"
        className="btn-link"
        type={LINK_BUTTON}
        icon="fa fa-fw fa-pencil"
        label={trans('edit')}
        tooltip="top"
        target={`#/entry/form/${props.entryId}`}
      />
    }

    {!props.locked && props.canManage &&
      <Button
        id="tooltip-button-status"
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon={`fa fa-fw fa-${props.status === 1 ? 'eye-slash' : 'eye'}`}
        label={props.status === 1 ? trans('unpublish') : trans('publish')}
        tooltip="top"
        callback={props.toggleStatus}
      />
    }

    {!props.locked && props.canAdministrate &&
      <Button
        id="tooltip-button-lock"
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-lock"
        label={trans('lock_entry', {}, 'clacoform')}
        tooltip="top"
        callback={props.toggleLock}
      />
    }

    {props.locked && props.canAdministrate &&
      <Button
        id="tooltip-button-lock"
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-unlock"
        label={trans('unlock_entry', {}, 'clacoform')}
        tooltip="top"
        callback={props.toggleLock}
      />
    }

    {!props.locked && props.canManage &&
      <Button
        id="entry-delete"
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash-o"
        label={trans('delete')}
        tooltip="top"
        callback={props.delete}
        dangerous={true}
      />
    }
  </div>

EntryActions.propTypes = {
  // data
  entryId: T.string.isRequired,
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
  updateNotification: T.func.isRequired,
  updateEntryUserProp: T.func.isRequired
}

class EntryComponent extends Component {
  constructor(props) {
    super(props)
    this.updateNotification = this.updateNotification.bind(this)
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
    return this.props.canEdit || this.props.isOwner || this.props.entryUser.shared
  }

  isFieldDisplayable(field) {
    return this.canViewMetadata() || !field.restrictions.isMetadata
  }

  showSharingForm() {
    fetch(url(['claro_claco_form_entry_shared_users_list', {entry: this.props.entryId}]), {
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
            selected: data.users
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
    return this.props.entryUser.notifyEdition || (this.props.displayComments && this.props.entryUser.notifyComment)
  }

  updateNotification(notifications) {
    const entryUser = Object.assign({}, this.props.entryUser, notifications)
    this.props.saveEntryUser(entryUser)
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
      const fieldValue = this.props.entry.values ? this.props.entry.values[f.id] : ''

      if (this.canViewMetadata() || !f.isMetadata) {
        switch (f.type) {
          case 'cascade':
            replacedField = fieldValue ? fieldValue.join(', ') : ''
            break
          case 'choice':
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
            replacedField = fieldValue && fieldValue['name'] ? `
              <a href="${url(['claro_claco_form_field_value_file_download', {entry: this.props.entry.id, field: f.id}])}">
                ${fieldValue['name']}
              </a>` :
              ''
            break
          default:
            replacedField = fieldValue
        }
        if (replacedField === undefined) {
          replacedField = ''
        }
      }
      template = template.replace(`%field_${f.autoId}%`, replacedField)
    })
    template += '<br/>'

    return template
  }

  getSections(fields, titleLabel) {
    const sectionFields = [
      {
        name: 'title',
        type: 'string',
        label: titleLabel ? titleLabel : trans('title'),
        required: true
      }
    ]
    fields.forEach(f => {
      const params = {
        name: `values.${f.id}`,
        type: f.type,
        label: f.label,
        required: f.required,
        help: f.help,
        displayed: this.isFieldDisplayable(f)
      }

      switch (f.type) {
        case 'choice':
          params['options'] = {
            multiple: f.options.multiple !== undefined ? f.options.multiple : false,
            condensed: f.options.condensed !== undefined ? f.options.condensed : false,
            choices: f.options.choices ?
              f.options.choices.reduce((acc, choice) => {
                acc[choice.value] = choice.value

                return acc
              }, {}) :
              {}
          }
          break
        case 'file':
          if (this.props.entry && this.props.entry.values && this.props.entry.values[f.id]) {
            params['calculated'] = (data) => Object.assign(
              {},
              data.values[f.id],
              {url: url(['claro_claco_form_field_value_file_download', {entry: data.id, field: f.id}])}
            )
          }
          break
      }
      sectionFields.push(params)
    })
    const sections = [
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: sectionFields
      }
    ]

    return sections
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
                        trans('published_at', {date: displayDate(this.props.entry.publicationDate, false, true)}) : trans('not_published')
                      }

                      , {trans('last_modified_at', {date: displayDate(this.props.entry.editionDate, false, true)})}
                    </div>
                  </div>
                }

                {this.props.entry.id && this.props.entryUser.id &&
                  <EntryActions
                    entryId={this.props.entry.id}
                    status={this.props.entry.status}
                    locked={this.props.entry.locked}
                    notificationsEnabled={this.isNotificationsEnabled()}
                    displayComments={this.props.displayComments}
                    notifyEdition={this.props.entryUser.notifyEdition}
                    notifyComment={this.props.entryUser.notifyComment}
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
                    updateEntryUserProp={this.props.updateEntryUserProp}

                  />
                }
              </div>

              {this.props.template && this.props.useTemplate ?
                <HtmlText>
                  {this.generateTemplate()}
                </HtmlText> :
                <DetailsData
                  name="entries.current"
                  sections={this.getSections(this.props.fields, this.props.titleLabel)}
                />
              }
            </div>

            {((this.props.displayCategories && this.props.entry.categories && 0 < this.props.entry.categories.length) ||
            (this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length)) &&
              <div className="entry-footer panel-footer">
                {this.props.displayCategories && this.props.entry.categories && 0 < this.props.entry.categories.length &&
                  <span className="title">{trans('categories')}</span>
                }
                {this.props.displayCategories && this.props.entry.categories && this.props.entry.categories.map(c =>
                  <span key={`category-${c.id}`} className="label label-primary">{c.name}</span>
                )}

                {this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length &&
                  <hr/>
                }
                {this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length &&
                  <span className="title">{trans('keywords')}</span>
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
          {trans('unauthorized')}
        </div>
    )
  }
}

EntryComponent.propTypes = {
  clacoFormId: T.string.isRequired,
  entryId: T.string,
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,
  canEditEntry: T.bool,
  canViewEntry: T.bool,
  canComment: T.bool,
  canViewComments: T.bool,

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
  titleLabel: T.string,

  entry: T.shape(EntryType.propTypes),
  entryUser: T.shape(EntryUserType.propTypes),
  fields: T.arrayOf(T.shape(FieldType.propTypes)),
  deleteEntry: T.func.isRequired,
  switchEntryStatus: T.func.isRequired,
  switchEntryLock: T.func.isRequired,
  downloadEntryPdf: T.func.isRequired,
  changeEntryOwner: T.func.isRequired,
  shareEntry: T.func.isRequired,
  unshareEntry: T.func.isRequired,
  updateEntryUserProp: T.func.isRequired,
  saveEntryUser: T.func.isRequired,
  showModal: T.func.isRequired,
  fadeModal: T.func.isRequired,
  history: T.object.isRequired
}

const Entry = withRouter(connect(
  (state, ownProps) => ({
    clacoFormId: select.clacoForm(state).id,
    entryId: ownProps.match.params.id,
    entry: formSelect.data(formSelect.form(state, 'entries.current')),
    entryUser: select.entryUser(state),

    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canEditEntry: select.canEditCurrentEntry(state),
    canViewEntry: select.canOpenCurrentEntry(state),
    canAdministrate: select.canAdministrate(state),
    canGeneratePdf: state.canGeneratePdf,
    canComment: select.canComment(state),
    canViewComments: select.canViewComments(state),
    fields: select.visibleFields(state),
    displayMetadata: select.getParam(state, 'display_metadata'),
    displayKeywords: select.getParam(state, 'display_keywords'),
    displayCategories: select.getParam(state, 'display_categories'),
    displayComments: select.getParam(state, 'display_comments'),
    openComments: select.getParam(state, 'open_comments'),
    commentsEnabled: select.getParam(state, 'comments_enabled'),
    anonymousCommentsEnabled: select.getParam(state, 'anonymous_comments_enabled'),
    menuPosition: select.getParam(state, 'menu_position'),
    isOwner: select.isCurrentEntryOwner(state),
    isManager: select.isCurrentEntryManager(state),
    randomEnabled: select.getParam(state, 'random_enabled'),
    useTemplate: select.getParam(state, 'use_template'),
    template: select.template(state),
    titleLabel: select.getParam(state, 'title_field_label')
  }),
  (dispatch, ownProps) => ({
    deleteEntry(entry) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-trash-o',
          title: trans('delete_entry', {}, 'clacoform'),
          question: trans('delete_entry_confirm_message', {title: entry.title}, 'clacoform'),
          dangerous: true,
          handleConfirm: () => {
            dispatch(actions.deleteEntries([entry]))
            ownProps.history.push('/entries')
          }
        })
      )
    },
    switchEntryStatus(entryId) {
      dispatch(actions.switchEntryStatus(entryId))
    },
    switchEntryLock(entryId) {
      dispatch(actions.switchEntryLock(entryId))
    },
    downloadEntryPdf(entryId) {
      dispatch(actions.downloadEntryPdf(entryId))
    },
    changeEntryOwner(entryId, userId) {
      dispatch(actions.changeEntryOwner(entryId, userId))
    },
    shareEntry(entryId, userId) {
      dispatch(actions.shareEntry(entryId, userId))
    },
    unshareEntry(entryId, userId) {
      dispatch(actions.unshareEntry(entryId, userId))
    },
    updateEntryUserProp(property, value) {
      dispatch(actions.editAndSaveEntryUser(property, value))
    },
    saveEntryUser(entryUser) {
      dispatch(actions.saveEntryUser(entryUser))
    },
    showModal(type, props) {
      dispatch(modalActions.showModal(type, props))
    },
    fadeModal() {
      dispatch(modalActions.fadeModal())
    }
  })
)(EntryComponent))

export {
  Entry
}