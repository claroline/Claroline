import React from 'react'
import {PropTypes as T} from 'prop-types'

import {PageContent, PageSection} from '#/main/app/page'
import {ActionTypes, constants, PromisedActionTypes} from '#/main/app/action'
import {ActionMenu} from '#/main/app/action/components/menu'

const DashboardActions = ({
  actions = [],
  canAdministrate = false
}) => {
  return (
    <PageContent className="py-4">
      <PageSection>
        <ActionMenu
          set={constants.ACTION_SET_DASHBOARD}
          manager={canAdministrate}
          actions={actions}
        />
      </PageSection>
    </PageContent>
  )
}

DashboardActions.propTypes = {
  canAdministrate: T.bool,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ])
}

export {
  DashboardActions
}
