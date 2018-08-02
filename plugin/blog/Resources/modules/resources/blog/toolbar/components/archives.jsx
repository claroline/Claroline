import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

// todo : use claroline abstraction instead
import moment from 'moment'

import {getApiFormat} from '#/main/core/scaffolding/date'
import {trans} from '#/main/core/translation'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store'
import {Section, Sections} from '#/main/core/layout/components/sections'

const ArchivesComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading"><h2 className="panel-title">{trans('archives', {}, 'icap_blog')}</h2></div>
    <div className="panel-body">
      {!isEmpty(props.archives) ? (
        <Sections accordion level={4}>
          {Object.keys(props.archives).reverse().map((year) => (
            <Section id={year} title={year} key={year} className="archives-year">
              <ul>
                {props.archives[year] && Object.keys(props.archives[year]).map((month) => (
                  <li className="list-unstyled" key={month}>
                    <a href="#" onClick={() => {
                      props.searchByRange(props.archives[year][month]['monthValue'] - 1, year)
                    }}>
                      {props.archives[year][month]['month']} ({props.archives[year][month]['count']})
                    </a>
                  </li>
                ))}
              </ul>
            </Section>
          ))
          }
        </Sections>)
        : (
          trans('no_archives', {}, 'icap_blog')
        )}
    </div>
  </div>

ArchivesComponent.propTypes = {
  archives: T.oneOfType([T.object, T.array]),
  searchByRange: T.func.isRequired
}

const Archives = connect(
  state => ({
    archives: state.blog.data.archives
  }),
  dispatch => ({
    searchByRange: (month, year) => {
      let from = moment([year, month])
      let format = getApiFormat()
      dispatch(listActions.addFilter('posts', 'fromDate', from.format(format)))
      dispatch(listActions.addFilter('posts', 'toDate', from.endOf('month').format(format)))
      dispatch(postActions.initDataList())
    }
  })
)(ArchivesComponent)

export {Archives}