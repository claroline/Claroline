import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {PostCard} from '#/plugin/blog/resources/blog/post/components/card'
import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {selectors} from '#/plugin/blog/resources/blog/store'
import {withRouter} from '#/main/app/router'
import {buildQueryParameters, initDatalistFilters} from '#/plugin/blog/resources/blog/utils'

class PostsComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      ignoreUpdate: false
    }
  }

  componentDidUpdate(prevProps) {
    if (!this.state.ignoreUpdate) {
      if (prevProps.filters !== this.props.filters) {
        this.setState({ignoreUpdate: true})
        this.props.history.push(buildQueryParameters(this.props.filters))
      }

      if (this.props.location.search !== prevProps.location.search) {
        this.setState({ignoreUpdate: true})
        this.props.initDataListFilters(this.props.location.search)
      }
    } else {
      this.setState({ignoreUpdate: false})
    }
  }

  render(){
    return (
      <ListData
        name={selectors.STORE_NAME + '.posts'}
        fetch={{
          url: ['apiv2_blog_post_list', {blogId: this.props.blogId}],
          autoload: true
        }}
        open={(row) => ({
          type: LINK_BUTTON,
          target: `/${row.slug}`
        })}
        definition={[
          {
            name: 'title',
            label: trans('title', {}, 'platform'),
            type: 'string',
            primary: true,
            displayed: true
          },{
            name: 'publicationDate',
            label: trans('icap_blog_post_form_publicationDate', {}, 'icap_blog'),
            type: 'date',
            displayed: true
          },{
            name: 'fromDate',
            label: trans('icap_blog_post_form_publicationDateFrom', {}, 'icap_blog'),
            type: 'date',
            sortable: false
          },{
            name: 'toDate',
            label: trans('icap_blog_post_form_publicationDateTo', {}, 'icap_blog'),
            type: 'date',
            sortable: false
          },{
            name: 'content',
            label: trans('content', {}, 'platform'),
            type: 'string',
            sortable: false,
            displayed: false
          },{
            name: 'authorName',
            label: trans('author', {}, 'platform'),
            type: 'string'
          },{
            name: 'tag',
            label: trans('tags', {}, 'platform'),
            type: 'string',
            sortable: false,
            displayed: false
          }
        ]}

        card={PostCard}

        display={{
          available : [listConst.DISPLAY_LIST],
          current: listConst.DISPLAY_LIST
        }}
        selectable={false}
      />
    )
  }
}

PostsComponent.propTypes ={
  blogId: T.string.isRequired,
  posts: T.array,
  canEdit: T.bool,
  canPost: T.bool,
  filters: T.array,
  history: T.object,
  location: T.object,
  initDataListFilters: T.func
}

const Posts = withRouter(
  connect(
    (state) => ({
      filters: selectors.posts(state).filters,
      posts: selectors.posts(state).data,
      blogId: selectors.blog(state).data.id,
      canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
      canPost: hasPermission('post', resourceSelect.resourceNode(state))
    }),
    (dispatch) => ({
      initDataListFilters: (query) => {
        initDatalistFilters(dispatch, query)
      }
    })
  )(PostsComponent)
)

export {
  Posts
}
