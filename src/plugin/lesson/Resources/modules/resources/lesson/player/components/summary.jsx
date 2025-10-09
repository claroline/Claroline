import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Tree} from '#/main/app/components/tree'
import {SearchMinimal} from '#/main/app/content/search/components/minimal'

import {actions, selectors} from '#/plugin/lesson/resources/lesson/store'
import {getNumbering, highlightSearch, matchSearch} from '#/plugin/lesson/resources/lesson/utils'
import {Html} from '#/main/app/components/html'

function isPageDisplayed(page, currentSearch) {
  if (matchSearch(page, currentSearch)) {
    return true
  }

  if (page.children) {
    for (let i = 0; i < page.children.length; i++) {
      if (isPageDisplayed(page.children[i], currentSearch)) {
        return true
      }
    }
  }

  return false
}

const PlayerSummary = (props) => {
  const dispatch = useDispatch()

  const currentSearch = useSelector(selectors.currentSearch)
  const lessonNumbering = useSelector(selectors.numbering)
  const tree = useSelector(selectors.tree)

  function getPageSummary(page) {
    const numbering = getNumbering(lessonNumbering, tree.children, page)

    return {
      id: page.id,
      type: LINK_BUTTON,
      label: (
        <Html>
          {numbering ?
            numbering + ' ' + highlightSearch(page.title, currentSearch) :
            highlightSearch(page.title, currentSearch)
          }
        </Html>
      ),
      target: `${props.path}/${page.slug}`,
      displayed: isPageDisplayed(page, currentSearch),
      children: page.children ? page.children.map(getPageSummary) : []
    }
  }

  const summary = get(tree, 'children', []).map(getPageSummary)

  return (
    <div className="d-flex flex-column h-100" role="presentation">
      <span className="h4 app-page-aside-title">
        {props.title}
      </span>

      <SearchMinimal
        className="mb-3"
        search={currentSearch}
        onSearch={(searchStr) => dispatch(actions.search(searchStr))}
      />

      {props.showOverview &&
        <Button
          type={LINK_BUTTON}
          icon="fa fa-fw fa-home fs-sm me-2"
          className="btn btn-text-body mx-n3 focus-ring text-start py-2 d-flex flex-row align-items-center gap-1"
          label={trans('resource_overview', {}, 'resource')}
          target={props.path}
          exact={true}
          onClick={props.autoClose}
        />
      }

      <Tree
        className="mx-n1"
        items={summary}
        onClick={props.autoClose}
      />

      {props.canAdd &&
        <Button
          className="btn btn-text-body mt-auto mx-n3 mb-n2 focus-ring text-start"
          type={LINK_BUTTON}
          icon="fa fa-fw fa-plus"
          label={trans('add_page', {}, 'actions')}
          target={`${props.path}/new`}
          onClick={props.autoClose}
        />
      }
    </div>
  )
}

PlayerSummary.propTypes = {
  path: T.string.isRequired,
  title: T.string.isRequired,
  showOverview: T.bool.isRequired,
  canAdd: T.bool.isRequired,
  // from aside
  autoClose: T.func
}

export {
  PlayerSummary
}
