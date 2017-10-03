import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import moment from 'moment'
import {trans, t} from '#/main/core/translation'
import {selectors} from '../../../selectors'
import {actions} from '../actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

class EntryComments extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showNewCommentForm: false,
      newComment: ''
    }
  }

  filterComment(comment) {
    return this.props.canManage || comment.status === 1 || (this.props.user && comment.user && this.props.user.id === comment.user.id)
  }

  canEditComment(comment) {
    return this.props.canManage || (this.props.user && comment.user && this.props.user.id === comment.user.id)
  }

  deleteComment(commentId) {
    this.props.showModal(MODAL_DELETE_CONFIRM, {
      title: trans('delete_comment', {}, 'clacoform'),
      question: trans('delete_comment_confirm_message', {}, 'clacoform'),
      handleConfirm: () => this.props.deleteComment(this.props.entry.id, commentId)
    })
  }

  createNewComment() {
    if (this.state.newComment) {
      this.props.createComment(this.props.entry.id, this.state.newComment)
    }
    this.setState({showNewCommentForm: false, newComment: ''})
  }

  editComment(commentId) {
    if (this.state[commentId] && this.state[commentId].comment) {
      this.props.editComment(this.props.entry.id, commentId, this.state[commentId].comment)
    }
    this.setState({[commentId]: {showCommentForm: false, comment: ''}})
  }

  showCommentForm(comment) {
    this.setState({[comment.id]: {showCommentForm: true, comment: comment.content}})
  }

  updateComment(commentId, value) {
    this.setState({[commentId]: {showCommentForm: true, comment: value}})
  }

  cancelCommentEdition(commentId) {
    this.setState({[commentId]: {showCommentForm: false, comment: ''}})
  }

  render() {
    return (
      <div>
        {this.props.canComment &&
          <div className="margin-bottom-sm">
            {this.state.showNewCommentForm ?
              <div>
                <Textarea
                  id="new-comment-form"
                  content={this.state.newComment}
                  minRows={2}
                  onChange={value => this.setState({newComment: value})}
                  onClick={() => {}}
                  onSelect={() => {}}
                  onChangeMode={() => {}}
                />
                <br/>
                <div>
                  <button
                    className="btn btn-primary margin-right-sm"
                    disabled={!this.state.newComment}
                    onClick={() => this.createNewComment()}
                  >
                    {t('ok')}
                  </button>
                  <button
                    className="btn btn-default margin-right-sm"
                    onClick={() => this.setState({showNewCommentForm: false, newComment: ''})}
                  >
                    {t('cancel')}
                  </button>
                </div>
              </div> :
              <button
                className="btn btn-default"
                onClick={() => this.setState({showNewCommentForm: true, newComment: ''})}
              >
                <span className="fa fa-w fa-plus-circle margin-right-sm"></span>
                {trans('add_comment', {}, 'clacoform')}
              </button>
            }
          </div>
        }
        {this.props.displayComments &&
          <div>
            {this.props.entry.comments && this.props.entry.comments.length > 0 ?
              <table className="table">
                <tbody>
                  {this.props.entry.comments.filter(c => this.filterComment(c)).map(c =>
                    <tr key={`comment-row-${c.id}`}>
                      <td className={classes({'inactive-comment-row': c.status !== 1})}>
                        {c.status === 0 &&
                          <span className="close">
                            {trans('comment_pending_info', {}, 'clacoform')}
                          </span>
                        }
                        {c.status === 2 &&
                          <span className="close">
                            {trans('comment_blocked_info', {}, 'clacoform')}
                          </span>
                        }
                        {(this.props.displayCommentAuthor || this.props.displayCommentDate) &&
                          <div>
                            <b>
                              {this.props.displayCommentAuthor &&
                                <span>
                                  {c.user ? `${c.user.firstName} ${c.user.lastName}` : t('anonymous')}
                                </span>
                              }
                              &nbsp;
                              {this.props.displayCommentDate &&
                                <span>
                                  [{moment(c.creationDate).format('DD/MM/YYYY HH:mm')}]
                                </span>
                              }
                            </b>
                          </div>
                        }
                        {this.canEditComment(c) &&
                          <TooltipButton
                            id={`comment-edit-button-${c.id}`}
                            className="btn btn-default btn-sm margin-right-sm"
                            title={t('edit')}
                            onClick={() => this.showCommentForm(c)}
                          >
                            <span className="fa fa-w fa-pencil"></span>
                          </TooltipButton>
                        }
                        {this.props.canManage &&
                          <span>
                            {c.status === 0 &&
                              <TooltipButton
                                id={`comment-validate-button-${c.id}`}
                                className="btn btn-success btn-sm margin-right-sm"
                                title={trans('validate', {}, 'clacoform')}
                                onClick={() => this.props.activateComment(this.props.entry.id, c.id)}
                              >
                                <span className="fa fa-w fa-check"></span>
                              </TooltipButton>
                            }
                            {c.status === 2 ?
                              <TooltipButton
                                id={`comment-activate-button-${c.id}`}
                                className="btn btn-default btn-sm margin-right-sm"
                                title={t('activate')}
                                onClick={() => this.props.activateComment(this.props.entry.id, c.id)}
                              >
                                <span className="fa fa-w fa-eye"></span>
                              </TooltipButton> :
                              <TooltipButton
                                id={`comment-block-button-${c.id}`}
                                className="btn btn-danger btn-sm margin-right-sm"
                                title={trans('block', {}, 'clacoform')}
                                onClick={() => this.props.blockComment(this.props.entry.id, c.id)}
                              >
                                <span className="fa fa-w fa-ban"></span>
                              </TooltipButton>
                            }
                            <TooltipButton
                              id={`comment-delete-button-${c.id}`}
                              className="btn btn-danger btn-sm margin-right-sm"
                              title={t('delete')}
                              onClick={() => this.deleteComment(c.id)}
                            >
                              <span className="fa fa-w fa-trash"></span>
                            </TooltipButton>
                          </span>
                        }
                        <br/>
                        <br/>
                        {this.state[c.id] && this.state[c.id].showCommentForm ?
                          <div>
                            <Textarea
                              id={`comment-form-${c.id}`}
                              content={this.state[c.id].comment}
                              minRows={2}
                              onChange={value => this.updateComment(c.id, value)}
                              onClick={() => {}}
                              onSelect={() => {}}
                              onChangeMode={() => {}}
                            />
                            <br/>
                            <div>
                              <button
                                className="btn btn-primary margin-right-sm"
                                disabled={!this.state[c.id].comment}
                                onClick={() => this.editComment(c.id)}
                              >
                                {t('ok')}
                              </button>
                              <button
                                className="btn btn-default margin-right-sm"
                                onClick={() => this.cancelCommentEdition(c.id)}
                              >
                                {t('cancel')}
                              </button>
                            </div>
                          </div> :
                          <HtmlText>
                            {c.content}
                          </HtmlText>
                        }
                      </td>
                    </tr>
                  )}
                </tbody>
              </table> :
              <div className="alert alert-warning">
                {trans('no_comment', {}, 'clacoform')}
              </div>
            }
          </div>
        }
      </div>
    )
  }
}

EntryComments.propTypes = {
  user: T.shape({
    id: T.number,
    firstName: T.string,
    lastName: T.string
  }),
  entry: T.object.isRequired,
  displayComments: T.bool.isRequired,
  canComment: T.bool.isRequired,
  canManage: T.bool.isRequired,
  displayCommentAuthor: T.bool.isRequired,
  displayCommentDate: T.bool.isRequired,
  createComment: T.func.isRequired,
  editComment: T.func.isRequired,
  deleteComment: T.func.isRequired,
  activateComment: T.func.isRequired,
  blockComment: T.func.isRequired,
  showModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    user: state.user,
    displayCommentAuthor: selectors.getParam(state, 'display_comment_author'),
    displayCommentDate: selectors.getParam(state, 'display_comment_date')
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createComment: (entryId, content) => dispatch(actions.createComment(entryId, content)),
    editComment: (entryId, commentId, content) => dispatch(actions.editComment(entryId, commentId, content)),
    deleteComment: (entryId, commentId) => dispatch(actions.deleteComment(entryId, commentId)),
    activateComment: (entryId, commentId) => dispatch(actions.activateComment(entryId, commentId)),
    blockComment: (entryId, commentId) => dispatch(actions.blockComment(entryId, commentId)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  }
}

const ConnectedEntryComments = connect(mapStateToProps, mapDispatchToProps)(EntryComments)

export {ConnectedEntryComments as EntryComments}