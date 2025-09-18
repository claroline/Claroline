import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {ActionCard, ActionCardSkeleton} from '#/main/app/action/components/card'
import {constants, pickActionSet, ActionTypes, PromisedActionTypes} from '#/main/app/action'

const ActionMenu = ({
  set,
  manager = false,
  actions = []
}) => {
  const [loaded, setLoaded] = useState(false)
  const [loadedActions, setActions] = useState([])
  useEffect(() => {
    if (actions instanceof Promise) {
      pickActionSet(set, actions).then(editorActions => {
        setActions(editorActions)
        setLoaded(true)
      })
    } else {
      setActions(pickActionSet(set, actions))
      setLoaded(true)
    }
  }, [set, actions])

  const displayedActions = loadedActions
    .filter(action => (undefined === action.displayed || action.displayed) && !action.dangerous && (!action.managerOnly || manager))

  const dangerousActions = loadedActions
    .filter(action => (undefined === action.displayed || action.displayed)  && action.dangerous && (!action.managerOnly || manager))

  if (!loaded) {
    return (
      <div className="d-flex flex-column gap-5" role="presentation">
        <div className="d-flex flex-column gap-2" role="presentation">
          <ActionCardSkeleton />
          <ActionCardSkeleton />
          <ActionCardSkeleton />
        </div>
        <hr className="m-0" aria-hidden={true} />
        <div className="d-flex flex-column gap-2" role="presentation">
          <ActionCardSkeleton dangerous={true} />
        </div>
      </div>
    )
  }

  return (
    <div className="d-flex flex-column gap-5" role="presentation">
      {!isEmpty(displayedActions) &&
        <div className="d-flex flex-column gap-2" role="presentation">
          {displayedActions.map(action =>
            <ActionCard
              {...action}
              key={action.name}
            />
          )}
        </div>
      }

      {!isEmpty(displayedActions) && !isEmpty(dangerousActions) &&
        <hr className="m-0" aria-hidden={true} />
      }

      {!isEmpty(dangerousActions) &&
        <div className="d-flex flex-column gap-2" role="presentation">
          {dangerousActions.map((action) =>
            <ActionCard
              {...action}
              key={action.name}
            />
          )}
        </div>
      }
    </div>
  )
}

ActionMenu.propTypes = {
  set: T.oneOf(constants.ACTION_SETS),
  manager: T.bool,
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
  ActionMenu
}
