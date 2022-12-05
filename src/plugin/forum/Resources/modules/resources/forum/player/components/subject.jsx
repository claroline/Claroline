import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {withRouter} from '#/main/app/router'
import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {withModal} from '#/main/app/overlays/modal/withModal'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_ALERT} from '#/main/app/modals/alert'
import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'
import {selectors as formSelect} from '#/main/app/content/form/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {UserMessage} from '#/main/core/user/message/components/user-message'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'

import {Subject as SubjectType} from '#/plugin/forum/resources/forum/player/prop-types'
import {selectors} from '#/plugin/forum/resources/forum/store'
import {actions} from '#/plugin/forum/resources/forum/player/store'
import {MessageComments} from '#/plugin/forum/resources/forum/player/components/message-comments'
import {SubjectForm} from '#/plugin/forum/resources/forum/player/components/subject-form'
import {MessagesSort} from '#/plugin/forum/resources/forum/player/components/messages-sort'

class SubjectComponent extends Component {
  constructor(props) {
    super(props)

    if (this.props.invalidated || !this.props.loaded) {
      this.props.reload(this.props.subject.id, this.props.forum.id)
    }
    this.state = {
      showMessageForm: null
    }
  }

  componentDidUpdate(prevProps) {
    if ((prevProps.invalidated !== this.props.invalidated && this.props.invalidated)
    || (prevProps.loaded !== this.props.loaded && !this.props.loaded)) {
      this.props.reload(this.props.subject.id, this.props.forum.id)
    }
  }

  editSubject(subjectId) {
    this.props.subjectEdition()
    this.props.history.push(`${this.props.path}/subjects/form/${subjectId}`)
  }

  createMessage(subjectId, content) {
    this.props.createMessage(subjectId, content, this.props.forum.moderation)

    if (!this.props.moderator &&
      (this.props.forum.moderation === 'PRIOR_ALL' || (this.props.forum.moderation === 'PRIOR_ONCE' && !this.props.isValidatedUser))) {
      this.props.showModal(MODAL_ALERT, {
        title: trans('moderated_posts', {}, 'forum'),
        message: trans('moderated_posts_explanation', {}, 'forum'),
        type: 'info'
      })
    }
  }

  updateMessage(message, content) {
    this.props.editContent(message, this.props.subject.id, content)
    this.setState({showMessageForm: null})
  }


  deleteSubject(subjectId) {
    this.props.showModal(MODAL_CONFIRM, {
      dangerous: true,
      icon: 'fa fa-fw fa-trash',
      title: trans('delete_subject', {}, 'forum'),
      question: trans('remove_subject_confirm_message', {}, 'forum'),
      handleConfirm: () => this.props.deleteSubject([subjectId], this.props.history.push, this.props.path)
    })
  }

  deleteMessage(messageId) {
    this.props.showModal(MODAL_CONFIRM, {
      dangerous: true,
      icon: 'fa fa-fw fa-trash',
      title: trans('delete_message', {}, 'forum'),
      question: trans('remove_post_confirm_message', {}, 'forum'),
      handleConfirm: () => this.props.deleteMessage(messageId)
    })
  }

  render() {
    if (isEmpty(this.props.subject) && !this.props.showSubjectForm) {
      return(
        <span>Loading</span>
      )
    }
    return (
      <section>
        <header className="subject-info">
          <Button
            label={trans('forum_back_to_subjects', {}, 'forum')}
            type={LINK_BUTTON}
            target={`${this.props.path}/subjects`}
            className="btn-link"
            primary={true}
          />

          <div>
            {(!this.props.showSubjectForm && !this.props.editingSubject) &&
              <h3 className="h2">
                {get(this.props.subject, 'meta.closed') &&
                  <span>[{trans('closed_subject', {}, 'forum')}] </span>
                }
                {get(this.props.subject, 'meta.sticky') &&
                  <span>[{trans('stuck', {}, 'forum')}] </span>
                }
                {this.props.subject.title}
                <small> {transChoice('replies', this.props.messages.length, {count: this.props.messages.length}, 'forum')}
                  {0 !== this.props.moderatedMessages.length &&
                    <span> {transChoice('moderated_posts_count', this.props.moderatedMessages.length, {count: this.props.moderatedMessages.length}, 'forum')}</span>
                  }
                </small>
              </h3>
            }
            {(this.props.showSubjectForm && this.props.editingSubject) &&
              <h3 className="h2">{this.props.subjectForm.title}<small> {transChoice('replies', this.props.messages.length, {count: this.props.messages.length}, 'forum')}</small></h3>
            }
            {(this.props.showSubjectForm && !this.props.editingSubject) &&
              <h3 className="h2">{trans('new_subject', {}, 'forum')}</h3>
            }
            {!isEmpty(this.props.subject.tags)&&
              <div className="tag">
                {this.props.subject.tags.map(tag =>
                  <span key={tag} className="label label-primary"><span className="fa fa-fw fa-tag" />{tag}</span>
                )}
              </div>
            }
          </div>
        </header>

        {this.props.showSubjectForm &&
          <SubjectForm />
        }

        {!this.props.showSubjectForm &&
          <UserMessage
            user={get(this.props.subject, 'meta.creator')}
            date={get(this.props.subject, 'meta.created') || ''}
            content={get(this.props.subject, 'content') || ''}
            allowHtml={true}
            actions={[
              {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit'),
                displayed: this.props.currentUser && get(this.props.subject, 'meta.creator.id', false) === this.props.currentUser.id,
                callback: () => this.editSubject(this.props.subject.id)
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-thumb-tack',
                label: trans('stick', {}, 'forum'),
                displayed: !(get(this.props.subject, 'meta.sticky', true)) && this.props.moderator,
                callback: () => this.props.stickSubject(this.props.subject)
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-thumb-tack',
                label: trans('unstick', {}, 'forum'),
                displayed: get(this.props.subject, 'meta.sticky', false) && this.props.moderator,
                callback: () => this.props.unStickSubject(this.props.subject)
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-circle-xmark',
                label: trans('close_subject', {}, 'forum'),
                displayed: !(get(this.props.subject, 'meta.closed', true)) && this.props.currentUser && (get(this.props.subject, 'meta.creator.id', false) === this.props.currentUser.id || this.props.moderator),
                callback: () => this.props.closeSubject(this.props.subject)
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-circle-check',
                label: trans('open_subject', {}, 'forum'),
                displayed: (get(this.props.subject, 'meta.closed', false)) && this.props.currentUser && (get(this.props.subject, 'meta.creator.id', false) === this.props.currentUser.id || this.props.moderator),
                callback: () => this.props.unCloseSubject(this.props.subject)
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-flag',
                label: trans('flag', {}, 'forum'),
                displayed: this.props.currentUser && (get(this.props.subject, 'meta.creator.id') !== this.props.currentUser.id) && !(get(this.props.subject, 'meta.flagged', true)),
                callback: () => this.props.flagSubject(this.props.subject)
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-flag',
                label: trans('unflag', {}, 'forum'),
                displayed: this.props.currentUser && (get(this.props.subject, 'meta.creator.id') !== this.props.currentUser.id) && (get(this.props.subject, 'meta.flagged', false)),
                callback: () => this.props.unFlagSubject(this.props.subject)
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash',
                label: trans('delete'),
                displayed: this.props.currentUser && get(this.props.subject, 'meta.creator.id') === this.props.currentUser.id || this.props.moderator,
                callback: () => this.deleteSubject(this.props.subject.id),
                dangerous: true
              }
            ]}
          />
        }
        {(!this.props.showSubjectForm && !isEmpty(this.props.messages)) &&
          <MessagesSort
            sortOrder={this.props.sortOrder}
            messages={this.props.messages}
            totalResults={this.props.totalResults}
            pages={this.props.pages}
            toggleSort={() => this.props.toggleSort(this.props.sortOrder)}
            changePage={() => this.props.changePage(this.props.currentPage + 1)}
            changePagePrev={() => this.props.changePage(this.props.currentPage - 1)}
          >
            <ul className="posts">
              {this.props.messages.map(message =>
                <li key={message.id} className="post">
                  {this.state.showMessageForm !== message.id &&
                    <UserMessage
                      user={get(message, 'meta.creator')}
                      date={message.meta.created}
                      content={message.content}
                      allowHtml={true}
                      actions={[
                        {
                          type: CALLBACK_BUTTON,
                          icon: 'fa fa-fw fa-pencil',
                          label: trans('edit', {}, 'actions'),
                          displayed: this.props.currentUser && (message.meta.creator.id === this.props.currentUser.id)  && !(get(this.props.subject, 'meta.closed', true)),
                          callback: () => this.setState({showMessageForm: message.id})
                        }, {
                          type: CALLBACK_BUTTON,
                          icon: 'fa fa-fw fa-flag',
                          label: trans('flag', {}, 'forum'),
                          displayed: this.props.currentUser && (message.meta.creator.id !== this.props.currentUser.id) && !message.meta.flagged,
                          callback: () => this.props.flag(message, this.props.subject.id)
                        }, {
                          type: CALLBACK_BUTTON,
                          icon: 'fa fa-fw fa-flag',
                          label: trans('unflag', {}, 'forum'),
                          displayed: this.props.currentUser && (message.meta.creator.id !== this.props.currentUser.id) && message.meta.flagged,
                          callback: () => this.props.unFlag(message, this.props.subject.id)
                        }, {
                          type: CALLBACK_BUTTON,
                          icon: 'fa fa-fw fa-trash',
                          label: trans('delete', {}, 'actions'),
                          displayed:  this.props.currentUser && (message.meta.creator.id === this.props.currentUser.id || this.props.moderator),
                          callback: () => this.deleteMessage(message.id),
                          dangerous: true
                        }
                      ]}
                    />
                  }
                  {this.state.showMessageForm === message.id &&
                      <UserMessageForm
                        user={this.props.currentUser}
                        allowHtml={true}
                        submitLabel={trans('save')}
                        content={message.content}
                        submit={(content) => this.updateMessage(message, content)}
                        cancel={() => this.setState({showMessageForm: null})}
                      />
                  }
                  <MessageComments
                    message={message}
                    opened={get(this.props.forum, 'display.expandComments', false)}
                  />
                </li>
              )}
            </ul>
          </MessagesSort>
        }

        {!this.props.bannedUser && !this.props.showSubjectForm && !get(this.props.subject, 'meta.closed') &&
          <UserMessageForm
            user={this.props.currentUser}
            allowHtml={true}
            submitLabel={trans('reply', {}, 'actions')}
            submit={(message) => this.createMessage(this.props.subject.id, message)}
          />
        }
      </section>
    )
  }
}

SubjectComponent.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  subject: T.shape(SubjectType.propTypes).isRequired,
  subjectForm: T.shape({
    title: T.string
  }),
  forum: T.shape({
    moderation: T.string.isRequired,
    id: T.string.isRequired
  }).isRequired,
  createMessage: T.func.isRequired,
  editContent: T.func.isRequired,
  flag: T.func.isRequired,
  stickSubject: T.func.isRequired,
  unStickSubject: T.func.isRequired,
  closeSubject: T.func.isRequired,
  unCloseSubject: T.func.isRequired,
  unFlag: T.func.isRequired,
  flagSubject: T.func.isRequired,
  unFlagSubject: T.func.isRequired,
  deleteMessage: T.func.isRequired,
  deleteSubject: T.func.isRequired,
  subjectEdition: T.func.isRequired,
  invalidated: T.bool.isRequired,
  loaded: T.bool.isRequired,
  reload: T.func.isRequired,
  showModal: T.func,
  showSubjectForm: T.bool.isRequired,
  editingSubject: T.bool.isRequired,
  messages: T.arrayOf(T.shape({})).isRequired,
  moderatedMessages: T.arrayOf(T.shape({
    length: T.number
  })),
  totalResults: T.number.isRequired,
  sortOrder: T.number.isRequired,
  pages: T.number,
  currentPage: T.number,
  changePage: T.func,
  changePagePrev: T.func,
  toggleSort: T.func.isRequired,
  history: T.object.isRequired,
  bannedUser: T.bool.isRequired,
  moderator: T.bool.isRequired,
  isValidatedUser: T.bool.isRequired
}

SubjectComponent.defaultProps = {
  bannedUser: false,
  isValidatedUser: false,
  moderatedMessages: []
}

const Subject =  withRouter(withModal(connect(
  state => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    forum: selectors.forum(state),
    isValidatedUser: selectors.isValidatedUser(state),
    subject: selectors.subject(state),
    subjectForm: formSelect.data(formSelect.form(state, `${selectors.STORE_NAME}.subjects.form`)),
    editingSubject: selectors.editingSubject(state),
    sortOrder: listSelect.sortBy(listSelect.list(state, `${selectors.STORE_NAME}.subjects.messages`)).direction,
    showSubjectForm: selectors.showSubjectForm(state),
    messages: selectors.visibleMessages(state),
    moderatedMessages: selectors.moderatedMessages(state),
    totalResults: listSelect.totalResults(listSelect.list(state, `${selectors.STORE_NAME}.subjects.messages`)),
    invalidated: listSelect.invalidated(listSelect.list(state, `${selectors.STORE_NAME}.subjects.messages`)),
    loaded: listSelect.loaded(listSelect.list(state, `${selectors.STORE_NAME}.subjects.messages`)),
    pages: listSelect.pages(listSelect.list(state, `${selectors.STORE_NAME}.subjects.messages`)),
    currentPage: listSelect.currentPage(listSelect.list(state, `${selectors.STORE_NAME}.subjects.messages`)),
    bannedUser: selectors.bannedUser(state),
    moderator: selectors.moderator(state)
  }),
  dispatch => ({
    createMessage(subjectId, content, moderation) {
      dispatch(actions.createMessage(subjectId, content, moderation))
    },
    deleteSubject(id, push, path) {
      dispatch(actions.deleteSubject(id, push, path))
    },
    deleteMessage(id) {
      dispatch(listActions.deleteData(`${selectors.STORE_NAME}.subjects.messages`, ['apiv2_forum_message_delete_bulk'], [{id: id}]))
    },
    reload(id, forumId) {
      dispatch(listActions.fetchData(`${selectors.STORE_NAME}.subjects.messages`, ['apiv2_forum_subject_get_message', {id, forumId}]))
    },
    toggleSort(sortOrder) {
      dispatch(listActions.updateSort(`${selectors.STORE_NAME}.subjects.messages`, 'creationDate', -sortOrder))
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.subjects.messages`))
    },
    changePage(page) {
      dispatch(listActions.changePage(`${selectors.STORE_NAME}.subjects.messages`, page))
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.subjects.messages`))
    },
    subjectEdition() {
      dispatch(actions.subjectEdition())
    },
    stickSubject(subject) {
      dispatch(actions.stickSubject(subject))
    },
    unStickSubject(subject) {
      dispatch(actions.unStickSubject(subject))
    },
    closeSubject(subject) {
      dispatch(actions.closeSubject(subject))
    },
    unCloseSubject(subject) {
      dispatch(actions.unCloseSubject(subject))
    },
    flagSubject(subject) {
      dispatch(actions.flagSubject(subject))
    },
    unFlagSubject(subject) {
      dispatch(actions.unFlagSubject(subject))
    },
    editContent(message, subjectId, content) {
      dispatch(actions.editContent(message, subjectId, content))
    },
    flag(message, subjectId) {
      dispatch(actions.flag(message, subjectId))
    },
    unFlag(message, subjectId) {
      dispatch(actions.unFlag(message, subjectId))
    }
  })
)(SubjectComponent)))

export {
  Subject
}
