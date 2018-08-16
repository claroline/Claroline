import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {withRouter} from '#/main/app/router'
import {trans, transChoice} from '#/main/core/translation'
import {currentUser} from '#/main/core/user/current'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {UserMessage} from '#/main/core/user/message/components/user-message'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'
import {withModal} from '#/main/app/overlay/modal/withModal'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_ALERT} from '#/main/app/modals/alert'
import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'
import {selectors as formSelect} from '#/main/app/content/form/store'

import {Subject as SubjectType} from '#/plugin/forum/resources/forum/player/prop-types'
import {select} from '#/plugin/forum/resources/forum/store/selectors'
import {actions} from '#/plugin/forum/resources/forum/player/store/actions'
import {MessageComments} from '#/plugin/forum/resources/forum/player/components/message-comments'
import {SubjectForm} from '#/plugin/forum/resources/forum/player/components/subject-form'
import {MessagesSort} from '#/plugin/forum/resources/forum/player/components/messages-sort'

const authenticatedUser = currentUser()

class SubjectComponent extends Component {
  constructor(props) {
    super(props)

    if (this.props.invalidated || !this.props.loaded) {
      this.props.reload(this.props.subject.id)
    }
    this.state = {
      showMessageForm: null
    }
  }

  componentDidUpdate(prevProps) {
    if ((prevProps.invalidated !== this.props.invalidated && this.props.invalidated)
    || (prevProps.loaded !== this.props.loaded && !this.props.loaded)) {
      this.props.reload(this.props.subject.id)
    }
  }

  editSubject(subjectId) {
    this.props.subjectEdition()
    this.props.history.push(`/subjects/form/${subjectId}`)
  }

  createMessage(subjectId, content) {
    this.props.createMessage(subjectId, content, this.props.forum.moderation)
    if (this.props.forum.moderation === 'PRIOR_ALL' ||
    this.props.forum.moderation === 'PRIOR_ONCE' ) {
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
      icon: 'fa fa-fw fa-trash-o',
      title: trans('delete_subject', {}, 'forum'),
      question: trans('remove_subject_confirm_message', {}, 'forum'),
      handleConfirm: () => this.props.deleteSubject([subjectId], this.props.history.push)
    })
  }

  deleteMessage(messageId) {
    this.props.showModal(MODAL_CONFIRM, {
      dangerous: true,
      icon: 'fa fa-fw fa-trash-o',
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
            target="/subjects"
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
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit'),
                displayed: get(this.props.subject, 'meta.creator.id', false) === authenticatedUser.id,
                action: () => this.editSubject(this.props.subject.id)
              }, {
                icon: 'fa fa-fw fa-thumb-tack',
                label: trans('stick', {}, 'forum'),
                displayed: !(get(this.props.subject, 'meta.sticky', true)) && this.props.moderator,
                action: () => this.props.stickSubject(this.props.subject)
              }, {
                icon: 'fa fa-fw fa-thumb-tack',
                label: trans('unstick', {}, 'forum'),
                displayed: get(this.props.subject, 'meta.sticky', false) && this.props.moderator,
                action: () => this.props.unStickSubject(this.props.subject)
              }, {
                icon: 'fa fa-fw fa-times-circle',
                label: trans('close_subject', {}, 'forum'),
                displayed: !(get(this.props.subject, 'meta.closed', true)) && (get(this.props.subject, 'meta.creator.id', false) === authenticatedUser.id || this.props.moderator),
                action: () => this.props.closeSubject(this.props.subject)
              }, {
                icon: 'fa fa-fw fa-check-circle',
                label: trans('open_subject', {}, 'forum'),
                displayed: (get(this.props.subject, 'meta.closed', false)) && (get(this.props.subject, 'meta.creator.id', false) === authenticatedUser.id || this.props.moderator),
                action: () => this.props.unCloseSubject(this.props.subject)
              }, {
                icon: 'fa fa-fw fa-flag-o',
                label: trans('flag', {}, 'forum'),
                displayed: (get(this.props.subject, 'meta.creator.id') !== authenticatedUser.id) && !(get(this.props.subject, 'meta.flagged', true)),
                action: () => this.props.flagSubject(this.props.subject)
              }, {
                icon: 'fa fa-fw fa-flag',
                label: trans('unflag', {}, 'forum'),
                displayed: (get(this.props.subject, 'meta.creator.id') !== authenticatedUser.id) && (get(this.props.subject, 'meta.flagged', false)),
                action: () => this.props.unFlagSubject(this.props.subject)
              }, {
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete'),
                displayed: get(this.props.subject, 'meta.creator.id') === authenticatedUser.id || this.props.moderator,
                action: () => this.deleteSubject(this.props.subject.id),
                dangerous: true
              }
            ]}
          />
        }
        {(!isEmpty(this.props.messages) && !this.props.showSubjectForm) &&
          <div>
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
                            icon: 'fa fa-fw fa-pencil',
                            label: trans('edit'),
                            displayed: message.meta.creator.id === authenticatedUser.id  && !(get(this.props.subject, 'meta.closed', true)),
                            action: () => this.setState({showMessageForm: message.id})
                          }, {
                            icon: 'fa fa-fw fa-flag-o',
                            label: trans('flag', {}, 'forum'),
                            displayed: (message.meta.creator.id !== authenticatedUser.id) && !message.meta.flagged,
                            action: () => this.props.flag(message, this.props.subject.id)
                          }, {
                            icon: 'fa fa-fw fa-flag',
                            label: trans('unflag', {}, 'forum'),
                            displayed: (message.meta.creator.id !== authenticatedUser.id) && message.meta.flagged,
                            action: () => this.props.unFlag(message, this.props.subject.id)
                          }, {
                            icon: 'fa fa-fw fa-trash-o',
                            label: trans('delete'),
                            displayed:  message.meta.creator.id === authenticatedUser.id || this.props.moderator,
                            action: () => this.deleteMessage(message.id),
                            dangerous: true
                          }
                        ]}
                      />
                    }
                    {this.state.showMessageForm === message.id &&
                        <UserMessageForm
                          user={currentUser()}
                          allowHtml={true}
                          submitLabel={trans('save')}
                          content={message.content}
                          submit={(content) => this.updateMessage(message, content)}
                          cancel={() => this.setState({showMessageForm: null})}
                        />
                    }
                    <MessageComments
                      message={message}
                    />
                  </li>
                )}
              </ul>
            </MessagesSort>
          </div>
        }
        {!this.props.bannedUser && this.props.showSubjectForm || !get(this.props.subject, 'meta.closed') &&
          <UserMessageForm
            user={currentUser()}
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
  subject: T.shape(SubjectType.propTypes).isRequired,
  subjectForm: T.shape({
    title: T.string
  }),
  forum: T.shape({
    moderation: T.string.isRequired
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
  moderator: T.bool.isRequired
}

SubjectComponent.defaultProps = {
  moderatedMessages: []
}
const Subject =  withRouter(withModal(connect(
  state => ({
    forum: select.forum(state),
    subject: select.subject(state),
    subjectForm: formSelect.data(formSelect.form(state, `${select.STORE_NAME}.subjects.form`)),
    editingSubject: select.editingSubject(state),
    sortOrder: listSelect.sortBy(listSelect.list(state, `${select.STORE_NAME}.subjects.messages`)).direction,
    showSubjectForm: select.showSubjectForm(state),
    messages: select.visibleMessages(state),
    moderatedMessages: select.moderatedMessages(state),
    totalResults: listSelect.totalResults(listSelect.list(state, `${select.STORE_NAME}.subjects.messages`)),
    invalidated: listSelect.invalidated(listSelect.list(state, `${select.STORE_NAME}.subjects.messages`)),
    loaded: listSelect.loaded(listSelect.list(state, `${select.STORE_NAME}.subjects.messages`)),
    pages: listSelect.pages(listSelect.list(state, `${select.STORE_NAME}.subjects.messages`)),
    currentPage: listSelect.currentPage(listSelect.list(state, `${select.STORE_NAME}.subjects.messages`)),
    bannedUser: select.bannedUser(state),
    moderator: select.moderator(state)
  }),
  dispatch => ({
    createMessage(subjectId, content, moderation) {
      dispatch(actions.createMessage(subjectId, content, moderation))
    },
    deleteSubject(id, push) {
      dispatch(actions.deleteSubject(id, push))
    },
    deleteMessage(id) {
      dispatch(listActions.deleteData(`${select.STORE_NAME}.subjects.messages`, ['apiv2_forum_message_delete_bulk'], [{id: id}]))
    },
    reload(id) {
      dispatch(listActions.fetchData(`${select.STORE_NAME}.subjects.messages`, ['claroline_forum_api_subject_getmessages', {id}]))
    },
    toggleSort(sortOrder) {
      dispatch(listActions.updateSortDirection(`${select.STORE_NAME}.subjects.messages`, -sortOrder))
      dispatch(listActions.invalidateData(`${select.STORE_NAME}.subjects.messages`))
    },
    changePage(page) {
      dispatch(listActions.changePage(`${select.STORE_NAME}.subjects.messages`, page))
      dispatch(listActions.invalidateData(`${select.STORE_NAME}.subjects.messages`))
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
