import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Tree} from '#/main/app/components/tree'
import {SearchMinimal} from '#/main/app/content/search/components/minimal'

const PlayerSummary = (props) => {
  const [search, setSearch] = useState(null)

  return (
    <div className="d-flex flex-column h-100" role="presentation">
      <span className="h4 app-page-aside-title">
        {props.title}
      </span>

      <SearchMinimal
        className="mb-3"
        search={search}
        onSearch={(searchStr) => setSearch(searchStr)}
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
        items={props.summary}
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
  summary: T.array,
  showOverview: T.bool.isRequired,
  canAdd: T.bool.isRequired,
  // from aside
  autoClose: T.func
}

export {
  PlayerSummary
}
