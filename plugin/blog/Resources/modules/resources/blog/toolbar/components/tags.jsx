import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {TagCloud} from '#/main/app/content/meta/components/tag-cloud'
import {selectors} from '#/plugin/blog/resources/blog/store'
import {constants} from '#/plugin/blog/resources/blog/constants'
import {cleanTag} from '#/plugin/blog/resources/blog/utils'
import isEmpty from 'lodash/isEmpty'
import {withRouter} from '#/main/app/router'
import {updateQueryParameters} from '#/plugin/blog/resources/blog/utils'

const TagsComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading">
      <h2 className="panel-title">{trans('tagcloud', {}, 'icap_blog')}</h2>
    </div>
    <div className="panel-body">
      {!isEmpty(props.tags) ?
        <div>
          {props.tagMode !== constants.TAGCLOUD_TYPE_LIST ?
            (<TagCloud
              tags={props.tags}
              minSize={12}
              maxSize={22}
              onClick={(tag) => {
                props.goHome(props.history)
                props.searchByTag(props.history, props.location.search, cleanTag(props.tagMode, tag))
              }}
            />)
            : (
              <ul>
                {props.tags && Object.keys(props.tags).sort().map((tag, index) =>(
                  <li key={index} className={'list-unstyled'}>
                    <a className='link' onClick={() => {
                      props.goHome(props.history)
                      props.searchByTag(props.history, props.location.search, tag)
                    }} >{tag}</a>
                  </li>
                ))}
              </ul>
            )
          }
        </div>
        : (
          trans('no_tags', {}, 'icap_blog')
        )
      }
    </div>
  </div>

TagsComponent.propTypes = {
  searchByTag: T.func.isRequired,
  tags: T.object,
  tagMode: T.string,
  history: T.object,
  goHome: T.func.isRequired,
  maxSize: T.number,
  location: T.object
}

const Tags = withRouter(connect(
  state => ({
    tags: selectors.blog(state).data.options.data.tagCloud === constants.TAGCLOUD_TYPE_CLASSIC_NUM ? selectors.displayTagsFrequency(state) : selectors.blog(state).data.tags,
    tagMode: selectors.blog(state).data.options.data.tagCloud,
    maxSize: selectors.blog(state).data.options.data.maxTag
  }),
  () => ({
    searchByTag: (history, querystring, tag) => {
      history.push(updateQueryParameters(querystring, 'tags', tag))
    },
    goHome: (history) => {
      history.push('/')
    }
  })
)(TagsComponent))

export {Tags}
