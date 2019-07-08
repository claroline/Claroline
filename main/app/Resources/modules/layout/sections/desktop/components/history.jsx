import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {MenuSection} from '#/main/app/layout/menu/components/section'

import {WorkspaceCard} from '#/main/core/workspace/components/card'

const DesktopHistory = props =>
  <MenuSection
    className="history"
    icon="fa fa-fw fa-history"
    title={trans('history')}
    opened={props.opened}
    toggle={props.toggle}
  >
    {!props.loaded &&
      <span className="">
        loading
      </span>
    }

    {props.loaded && 0 === props.results.length &&
      <span className="">
        empty
      </span>
    }

    {props.loaded && 0 < props.results.length &&
      <ul>
        {props.results.map(result =>
          <li key={result.id}>
            <WorkspaceCard
              size="xs"
              direction="row"
              data={result}
              primaryAction={{
                type: LINK_BUTTON,
                label: trans('open', {}, 'actions'),
                target: `/desktop/workspaces/${result.id}`
              }}
            />
          </li>
        )}
      </ul>
    }
  </MenuSection>

DesktopHistory.propTypes = {
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,

  loaded: T.bool.isRequired,
  results: T.arrayOf(T.shape({

  })).isRequired
}

export {
  DesktopHistory
}
