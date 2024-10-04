import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {url} from '#/main/app/api'
import {withRouter} from '#/main/app/router'
import {displayDate} from '#/main/app/intl/date'
import {trans} from '#/main/app/intl/translation'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {ASYNC_BUTTON, CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {formatField} from '#/main/app/content/form/parameters/utils'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {UserCard} from '#/main/community/user/components/card'
import {MODAL_USERS} from '#/main/community/modals/users'
import {ContentHtml} from '#/main/app/content/components/html'
import {UserMicro} from '#/main/core/user/components/micro'

import {
  Field as FieldType,
  Entry as EntryType,
  EntryUser as EntryUserType
} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {generateFromTemplate} from '#/plugin/claco-form/resources/claco-form/template'
import {constants} from '#/plugin/claco-form/resources/claco-form/constants'
import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {actions, selectors as playerSelectors} from '#/plugin/claco-form/resources/claco-form/player/store'
import {EntryComments} from '#/plugin/claco-form/resources/claco-form/player/components/entry-comments'
import {EntryMenu} from '#/plugin/claco-form/resources/claco-form/player/components/entry-menu'
import isEmpty from 'lodash/isEmpty'
import {ResourcePage} from '#/main/core/resource'

// TODO : find a way to merge actions list with the one in entries list
const EntryActions = props =>
  <Toolbar
    className="entry-actions"
    buttonName="btn-link"
    tooltip="top"
    toolbar="more"
    size="sm"
    actions={[
      {
        name: 'edit',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        target: `${props.path}/entry/form/${props.entryId}`,
        displayed: !props.locked && props.canEdit,
        group: trans('management'),
        primary: true
      }, {
        name: 'export-pdf',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-file-pdf',
        label: trans('export-pdf', {}, 'actions'),
        callback: props.downloadPdf,
        displayed: props.canGeneratePdf,
        group: trans('transfer')
      }, {
        name: 'publish',
        type: CALLBACK_BUTTON,
        icon: classes('fa fa-fw', {
          'fa-eye-slash': 1 === props.status,
          'fa-eye': 1 !== props.status
        }),
        label: trans(props.status === 1 ? 'unpublish':'publish', {}, 'actions'),
        callback: props.toggleStatus,
        displayed: !props.locked && props.canAdministrate,
        group: trans('management')
      }, {
        name: 'change-owner',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-user-edit',
        label: trans('change_entry_owner', {}, 'clacoform'),
        modal: [MODAL_USERS, {
          selectAction: (users) => ({
            type: CALLBACK_BUTTON,
            label: trans('change_entry_owner', {}, 'clacoform'),
            callback: () => props.changeOwner(users[0])
          })
        }],
        displayed: props.canAdministrate,
        group: trans('management')
      }, {
        name: 'lock',
        type: CALLBACK_BUTTON,
        icon: classes('fa fa-fw', {
          'fa-lock': !props.locked,
          'fa-unlock': props.locked
        }),
        label: trans(props.locked ? 'unlock':'lock', {}, 'actions'),
        callback: props.toggleLock,
        displayed: props.canAdministrate,
        group: trans('management')
      }, {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash',
        label: trans('delete', {}, 'actions'),
        callback: props.delete,
        confirm: {
          title: trans('delete_entry', {}, 'clacoform'),
          message: trans('delete_entry_confirm_message', {title: props.entryTitle}, 'clacoform')
        },
        dangerous: true,
        displayed: !props.locked && props.canAdministrate,
        group: trans('management')
      }, {
        name: 'share',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-share-alt',
        label: trans('share', {}, 'actions'),
        modal: [MODAL_USERS, {
          selectAction: (users) => ({
            type: CALLBACK_BUTTON,
            label: trans('share', {}, 'actions'),
            callback: () => props.share(users.map(user => user.id))
          })
        }],
        displayed: props.canEdit,
        group: trans('community')
      }, {
        name: 'notify-edit',
        type: CALLBACK_BUTTON,
        icon: classes('fa fa-fw', {
          'fa-bell': !props.notifyEdition,
          'fa-bell-slash': props.notifyEdition
        }),
        label: trans(!props.notifyEdition ? 'enable_edition_notification':'disable_edition_notification', {}, 'clacoform'),
        callback: () => props.updateEntryUserProp('notifyEdition', !props.notifyEdition),
        group: trans('notifications')
      }, {
        name: 'notify-comments',
        type: CALLBACK_BUTTON,
        icon: classes('fa fa-fw', {
          'fa-bell': !props.notifyComment,
          'fa-bell-slash': props.notifyComment
        }),
        label: trans(!props.notifyComment ? 'enable_comments_notification':'disable_comments_notification', {}, 'clacoform'),
        callback: () => props.updateEntryUserProp('notifyComment', !props.notifyComment),
        displayed: props.displayComments,
        group: trans('notifications')
      }
    ]}
  />

EntryActions.propTypes = {
  path: T.string.isRequired,
  // data
  entryTitle: T.string.isRequired,
  entryId: T.string.isRequired,
  status: T.number.isRequired,
  locked: T.bool.isRequired,

  // current user rights
  canAdministrate: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,

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
  updateEntryUserProp: T.func.isRequired
}

class EntryComponent extends Component {
  canViewMetadata() {
    return this.props.canEditEntry ||
      this.props.displayMetadata === 'all' ||
      (this.props.displayMetadata === 'manager' && this.props.canAdministrate)
  }

  isFieldDisplayable(field) {
    return isEmpty(field.restrictions.confidentiality)
      || 'none' === field.restrictions.confidentiality
      || this.props.canAdministrate
      || ('owner' === field.restrictions.confidentiality && this.props.isOwner)
  }

  getSections(fields) {
    return [
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: fields
          .filter(f => this.isFieldDisplayable(f))
          .map(f => {
            const params = formatField(f, fields, 'values', true)

            switch (f.type) {
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

            return params
          })
      }
    ]
  }

  render() {
    if (!this.props.canViewEntry && !this.props.canEditEntry) {
      return (
        <div className="alert alert-danger">
          {trans('unauthorized')}
        </div>
      )
    }

    return (
      <ResourcePage>
        {['up', 'both'].indexOf(this.props.menuPosition) > -1 &&
          <EntryMenu />
        }

        {this.props.helpMessage &&
          <ContentHtml className="entry-help">
            {this.props.helpMessage}
          </ContentHtml>
        }

        <div className="card mb-3">
          <div className="card-body">
            <h2 className="entry-title">{this.props.entry.title}</h2>

            <div className="entry-meta">
              {this.canViewMetadata() &&
                <div className="entry-info">
                  <UserMicro {...this.props.entry.user} />

                  <div className="date">
                    {this.props.entry.publicationDate ?
                      trans('published_at', {date: displayDate(this.props.entry.publicationDate, false, true)}) + ', ' :
                      constants.ENTRY_STATUS_PUBLISHED !== this.props.entry.status ?
                        trans('not_published') + ', ' :
                        ''
                    }
                    {trans('last_modified_at', {date: displayDate(this.props.entry.editionDate || this.props.entry.creationDate, false, true)})}
                  </div>
                </div>
              }

              {this.props.entry.id && this.props.entryUser.id &&
                <EntryActions
                  path={this.props.path}
                  entryId={this.props.entry.id}
                  entryTitle={this.props.entry.title}
                  status={this.props.entry.status}
                  locked={this.props.entry.locked}
                  displayComments={this.props.displayComments}
                  notifyEdition={this.props.entryUser.notifyEdition}
                  notifyComment={this.props.entryUser.notifyComment}
                  canAdministrate={this.props.canAdministrate}
                  canEdit={this.props.canEditEntry}
                  canGeneratePdf={this.props.canGeneratePdf}

                  changeOwner={(user) => this.props.changeEntryOwner(this.props.entry.id, user.id)}
                  downloadPdf={() => this.props.downloadEntryPdf(this.props.entry.id)}
                  share={(users) => this.props.shareEntry(this.props.entryId, users)}
                  delete={() => this.props.deleteEntry(this.props.entry).then(() => this.props.history.push(`${this.props.path}/entries`))}
                  toggleStatus={() => this.props.switchEntryStatus(this.props.entry.id)}
                  toggleLock={() => this.props.switchEntryLock(this.props.entry.id)}
                  updateEntryUserProp={this.props.updateEntryUserProp}

                />
              }
            </div>

            {this.props.template && this.props.useTemplate ?
              <ContentHtml>
                {generateFromTemplate(this.props.template, this.props.fields, this.props.entry, this.props.isOwner, this.props.canAdministrate)}
              </ContentHtml> :
              <DetailsData
                name={selectors.STORE_NAME+'.entries.current'}
                sections={this.getSections(this.props.fields)}
              />
            }
          </div>
        </div>

        {((this.props.displayCategories && this.props.entry.categories && 0 < this.props.entry.categories.length) ||
        (this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length)) &&
          <div className="entry-footer card-footer">
            {this.props.displayCategories && this.props.entry.categories && 0 < this.props.entry.categories.length &&
              <span className="title">{trans('categories')}</span>
            }
            {this.props.displayCategories && this.props.entry.categories && this.props.entry.categories.map(c =>
              <span key={`category-${c.id}`} className="badge text-bg-primary">{c.name}</span>
            )}

            {this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length &&
              <hr/>
            }
            {this.props.displayKeywords && this.props.entry.keywords && 0 < this.props.entry.keywords.length &&
              <span className="title">{trans('keywords')}</span>
            }
            {this.props.displayKeywords && this.props.entry.keywords && this.props.entry.keywords.map(c =>
              <span key={`keyword-${c.id}`} className="badge text-bg-secondary">{c.name}</span>
            )}
          </div>
        }

        {['down', 'both'].indexOf(this.props.menuPosition) > -1 &&
          <EntryMenu />
        }

        {this.props.canEditEntry &&
          <FormSections level={3}>
            <FormSection
              id="shared-users"
              className="embedded-list-section"
              icon="fa fa-fw fa-share-alt"
              title={trans('shared_with', {}, 'clacoform')}
            >
              <ListData
                flush={true}
                name={`${selectors.STORE_NAME}.entries.sharedUsers`}
                fetch={{
                  url: ['claro_claco_form_entry_shared_users_list', {entry: this.props.entryId}],
                  autoload: true
                }}
                delete={{
                  url: ['claro_claco_form_entry_user_unshare', {entry: this.props.entryId}]
                }}
                definition={[
                  {
                    name: 'username',
                    type: 'username',
                    label: trans('username'),
                    displayed: true,
                    primary: true
                  }, {
                    name: 'lastName',
                    type: 'string',
                    label: trans('last_name'),
                    displayed: true
                  }, {
                    name: 'firstName',
                    type: 'string',
                    label: trans('first_name'),
                    displayed: true
                  }
                ]}
                card={UserCard}
              />
            </FormSection>
          </FormSections>
        }

        {(this.props.canViewComments || this.props.canComment) &&
          <EntryComments
            opened={this.props.openComments}
            canComment={this.props.canComment}
            canManage={this.props.canAdministrate}
            canViewComments={this.props.canViewComments}
          />
        }
      </ResourcePage>
    )
  }
}

EntryComponent.propTypes = {
  path: T.string.isRequired,
  clacoFormId: T.string.isRequired,
  slideshowQueryString: T.string,
  entryId: T.string,
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,
  canEditEntry: T.bool,
  canViewEntry: T.bool,
  canComment: T.bool,
  canViewComments: T.bool,

  isOwner: T.bool,

  showEntryNav: T.bool.isRequired,
  helpMessage: T.string,
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
  updateEntryUserProp: T.func.isRequired,
  saveEntryUser: T.func.isRequired,
  fetchEntryUsersShared: T.func.isRequired,
  showModal: T.func.isRequired,
  history: T.object.isRequired
}

const Entry = withRouter(connect(
  (state, ownProps) => ({
    path: resourceSelectors.path(state),
    clacoFormId: selectors.clacoForm(state).id,
    slideshowQueryString: playerSelectors.slideshowQueryString(state),
    entryId: ownProps.match.params.id || formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.entries.current')).id,
    entry: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    entryUser: selectors.entryUser(state),

    canEditEntry: selectors.canEditCurrentEntry(state),
    canViewEntry: selectors.canOpenCurrentEntry(state),
    canAdministrate: selectors.canManageCurrentEntry(state),
    canGeneratePdf: selectors.canGeneratePdf(state),
    canComment: selectors.canComment(state),
    canViewComments: selectors.canViewComments(state),

    fields: selectors.visibleFields(state),
    showEntryNav: selectors.showEntryNav(state),
    helpMessage: selectors.params(state).helpMessage,
    displayMetadata: selectors.params(state).display_metadata,
    displayKeywords: selectors.params(state).display_keywords,
    displayCategories: selectors.params(state).display_categories,
    displayComments: selectors.params(state).display_comments,
    openComments: selectors.params(state).open_comments,
    commentsEnabled: selectors.params(state).comments_enabled,
    anonymousCommentsEnabled: selectors.params(state).anonymous_comments_enabled,
    menuPosition: selectors.params(state).menu_position,
    isOwner: selectors.isCurrentEntryOwner(state),
    randomEnabled: selectors.clacoForm(state).random.enabled,
    useTemplate: selectors.useTemplate(state),
    template: selectors.template(state),
    titleLabel: selectors.params(state).title_field_label
  }),
  (dispatch) => ({
    deleteEntry(entry) {
      return dispatch(actions.deleteEntry(entry))
    },
    switchEntryStatus(entryId) {
      dispatch(actions.switchEntryStatus(entryId))
    },
    switchEntryLock(entryId) {
      dispatch(actions.switchEntryLock(entryId))
    },
    downloadEntryPdf(entryId) {
      return dispatch(actions.downloadEntryPdf(entryId))
    },
    changeEntryOwner(entryId, userId) {
      dispatch(actions.changeEntryOwner(entryId, userId))
    },
    shareEntry(entryId, users) {
      dispatch(actions.shareEntry(entryId, users))
    },
    updateEntryUserProp(property, value) {
      dispatch(actions.editAndSaveEntryUser(property, value))
    },
    saveEntryUser(entryUser) {
      dispatch(actions.saveEntryUser(entryUser))
    },
    fetchEntryUsersShared(entryId) {
      dispatch(actions.fetchEntryUsersShared(entryId))
    },
    showModal(type, props) {
      dispatch(modalActions.showModal(type, props))
    }
  })
)(EntryComponent))

export {
  Entry
}
