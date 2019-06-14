import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {currentUser} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'
import {UserMessage} from '#/main/core/user/message/components/user-message'

import {Comment as CommentType} from '#/plugin/drop-zone/resources/dropzone/prop-types'

const authenticatedUser = currentUser()

class Comments extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showForm: false
    }
  }

  render() {
    return (
      <div>
        {this.props.title &&
          <h3 className="dropzone-comments-title">{this.props.title}</h3>
        }

        {0 === this.props.comments.length &&
          <div className="alert alert-warning">
            {trans('no_comment')}
          </div>
        }

        {this.props.comments.map(comment =>
          <UserMessage
            key={`comment-${comment.id}`}
            user={comment.meta && comment.meta.user ? comment.meta.user : undefined}
            date={comment.meta ? comment.meta.creationDate : ''}
            content={comment.content}
            allowHtml={true}
            position={comment.meta && comment.meta.user && authenticatedUser && comment.meta.user.id === authenticatedUser.id ? 'left' : 'right'}
          />
        )}

        {this.state.showForm ?
          <UserMessageForm
            user={authenticatedUser}
            allowHtml={true}
            submitLabel={trans('add_comment')}
            submit={(content) => {
              const comment = {
                content: content,
                meta: {
                  user: authenticatedUser
                }
              }

              if (this.props.revisionId) {
                comment['meta']['revision'] = {
                  id: this.props.revisionId
                }
              }
              if (this.props.dropId) {
                comment['meta']['drop'] = {
                  id: this.props.dropId
                }
              }
              this.props.saveComment(comment)
              this.setState({showForm: false})
            }}
            cancel={() => this.setState({showForm: false})}
          /> :
          <CallbackButton
            className="btn pull-right"
            primary={true}
            callback={() => this.setState({showForm: true})}
          >
            {trans('add_comment')}
          </CallbackButton>
        }
      </div>
    )
  }
}

Comments.propTypes = {
  comments: T.arrayOf(T.shape(CommentType.propTypes)).isRequired,
  revisionId: T.string,
  dropId: T.string,
  title: T.string,
  saveComment: T.func.isRequired
}

Comments.defaultProps = {
  comments: [],
  revisionId: null,
  dropId: null,
  title: null
}

export {
  Comments
}
