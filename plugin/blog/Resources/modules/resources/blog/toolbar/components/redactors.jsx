import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {UrlButton} from '#/main/app/buttons/url/components/button'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {selectors} from '#/plugin/blog/resources/blog/store'
import {updateQueryParameters} from '#/plugin/blog/resources/blog/utils'
import {withRouter} from '#/main/app/router'

const RedactorsComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading"><h2 className="panel-title">{trans('redactor', {}, 'icap_blog')}</h2></div>
    <div className="panel-body">
      {!isEmpty(props.authors) ? (props.authors.map((author, index) =>(
        <span key={index}>
          <UrlButton target={['claro_user_profile', {publicUrl: get(author, 'meta.publicUrl')}]}>
            <UserAvatar className="user-picture" picture={author ? author.picture : undefined} alt={true} />
          </UrlButton>
          <a className="redactor-name link" onClick={() => {
            props.goHome(props.history)
            props.getPostsByAuthor(props.history, props.location.search, author.firstName + ' ' + author.lastName)
          }}>{author.firstName + ' ' + author.lastName}
          </a>
        </span>
      ))) : (
        trans('no_authors', {}, 'icap_blog')
      )}
    </div>
  </div>

RedactorsComponent.propTypes = {
  blogId: T.string,
  authors: T.array,
  getPostsByAuthor: T.func.isRequired,
  goHome: T.func.isRequired,
  history: T.object,
  location: T.object
}

const Redactors = withRouter(connect(
  state => ({
    blogId: selectors.blog(state).id,
    authors: selectors.blog(state).data.authors
  }),
  () => ({
    getPostsByAuthor: (history, querystring, authorName) => {
      history.push(updateQueryParameters(querystring, 'author', authorName))
    },
    goHome: (history) => {
      history.push('/')
    }
  })
)(RedactorsComponent))

export {Redactors}
