import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {actions as listActions} from '#/main/core/data/list/actions'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store/actions'
import {UrlButton} from '#/main/app/button/components/url'
import {UserAvatar} from '#/main/core/user/components/avatar.jsx'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

const RedactorsComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading"><h2 className="panel-title">{trans('redactor', {}, 'icap_blog')}</h2></div>
    <div className="panel-body">
      {!isEmpty(props.authors) ? (props.authors.map((author, index) =>(
        <span key={index}>
          <UrlButton target={['claro_user_profile', {publicUrl: get(author, 'meta.publicUrl')}]}>
            <UserAvatar className="user-picture" picture={author ? author.picture : undefined} alt={true} />
          </UrlButton>
          <a className="redactor-name" href="#" onClick={() => {
            props.getPostsByAuthor(props.blogId, author.firstName + ' ' + author.lastName)
          }}>{author.firstName + ' ' + author.lastName}
          </a>
        </span>
      ))) : (
        trans('no_authors', {}, 'icap_blog')
      )}
    </div>
  </div>
    
RedactorsComponent.propTypes = {
  authors: T.array,
  getPostsByAuthor: T.func.isRequired
}

const Redactors = connect(
  state => ({
    authors: state.blog.data.authors
  }),
  dispatch => ({
    getPostsByAuthor: (blogId, authorName) => {
      dispatch(listActions.addFilter('posts', 'authorName', authorName))
      dispatch(postActions.initDataList())
    }
  })
)(RedactorsComponent)

export {Redactors}