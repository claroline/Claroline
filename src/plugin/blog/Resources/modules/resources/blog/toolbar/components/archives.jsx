import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

// todo : use claroline abstraction instead
import moment from 'moment'

import {getApiFormat} from '#/main/app/intl/date'
import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {actions as listActions} from '#/main/app/content/list/store'
import {Section, Sections} from '#/main/app/content/components/sections'
import {withRouter} from '#/main/app/router'

import {actions as postActions} from '#/plugin/blog/resources/blog/post/store'
import {selectors} from '#/plugin/blog/resources/blog/store/selectors'

const ArchivesComponent = props =>
  <div key='archives' className="card mb-3">
    <div className="card-header">
      <h2 className="card-title">{trans('archives', {}, 'icap_blog')}</h2>
    </div>
    <div className="card-body">
      {!isEmpty(props.archives) ? (
        <Sections accordion level={4}>
          {Object.keys(props.archives).reverse().map((year) => (
            <Section id={year} title={year} key={year} className="archives-year">
              <ul>
                {props.archives[year] && Object.keys(props.archives[year]).map((month) => (
                  <li className="list-unstyled" key={month}>
                    <CallbackButton callback={() => {
                      props.goHome(props.history, props.path)
                      props.searchByRange(props.archives[year][month]['monthValue'] - 1, year)
                    }}>
                      {props.archives[year][month]['month']} ({props.archives[year][month]['count']})
                    </CallbackButton>
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
  path: T.string.isRequired,
  archives: T.oneOfType([T.object, T.array]),
  searchByRange: T.func.isRequired,
  goHome: T.func.isRequired,
  history: T.object
}

const Archives = withRouter(connect(
  state => ({
    archives: selectors.blog(state).data.archives
  }),
  dispatch => ({
    searchByRange: (month, year) => {
      let from = moment([year, month])
      let format = getApiFormat()
      dispatch(listActions.addFilter(selectors.STORE_NAME+'.posts', 'fromDate', from.format(format)))
      dispatch(listActions.addFilter(selectors.STORE_NAME+'.posts', 'toDate', from.endOf('month').format(format)))
      dispatch(postActions.initDataList())
    },
    goHome: (history, path) => {
      history.push(path)
    }
  })
)(ArchivesComponent))

export {Archives}
