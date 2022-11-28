import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {withRouter} from '#/main/app/router'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {route} from '#/main/community/user/routing'
import {UserAvatar} from '#/main/core/user/components/avatar'

import {selectors} from '#/plugin/blog/resources/blog/store/selectors'
import {updateQueryParameters} from '#/plugin/blog/resources/blog/utils'

const RedactorsComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading"><h2 className="panel-title">{trans('redactor', {}, 'icap_blog')}</h2></div>
    <div className="panel-body">
      {!isEmpty(props.authors) ? (props.authors.map((author, index) =>(
        <span key={index}>
          <LinkButton target={route(author)}>
            <UserAvatar className="user-picture" picture={author ? author.picture : undefined} alt={true} />
          </LinkButton>
          <CallbackButton className="redactor-name link" callback={() => {
            props.goHome(props.history, props.path)
            props.getPostsByAuthor(props.history, props.location.search, author.firstName + ' ' + author.lastName)
          }}>{author.firstName + ' ' + author.lastName}
          </CallbackButton>
        </span>
      ))) : (
        trans('no_authors', {}, 'icap_blog')
      )}
    </div>
  </div>

RedactorsComponent.propTypes = {
  path: T.string.isRequired,
  blogId: T.string,
  authors: T.array,
  getPostsByAuthor: T.func.isRequired,
  goHome: T.func.isRequired,
  history: T.object,
  location: T.object
}

const Redactors = withRouter(connect(
  state => ({
    path: resourceSelectors.path(state),
    blogId: selectors.blog(state).id,
    authors: selectors.blog(state).data.authors
  }),
  () => ({
    getPostsByAuthor: (history, querystring, authorName) => {
      history.push(updateQueryParameters(querystring, 'author', authorName))
    },
    goHome: (history, path) => {
      history.push(path)
    }
  })
)(RedactorsComponent))

export {Redactors}
