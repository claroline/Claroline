import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import {NavLink} from '#/main/core/router'

import {Action as ActionTypes} from '#/main/core/layout/action/prop-types'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action.jsx'

const ProfileNav = props =>
  <nav className="user-profile-nav">
    {props.facets.map(facet =>
      <NavLink
        key={facet.id}
        to={`${props.prefix}/${facet.id}`}
        className="user-profile-link"
      >
        {facet.icon &&
          <span className={facet.icon} />
        }

        {facet.title || t('profile_facet')}

        {0 !== props.actions.length &&
          <div className="user-profile-nav-actions">
            {props.actions.filter(action => !action.displayed || action.displayed(facet)).map((action, actionIndex) =>
              <TooltipAction
                {...action}
                action={typeof action.action === 'function' ? () => action.action(facet) : action.action}
                key={`${facet.id}-action-${actionIndex}`}
                id={`${facet.id}-action-${actionIndex}`}
                className={classes({
                  'btn-link-default': !action.primary && !action.dangerous,
                  'btn-link-danger': action.dangerous,
                  'btn-link-primary': action.primary
                })}
              />
            )}
          </div>
        }
      </NavLink>
    )}
  </nav>

ProfileNav.propTypes = {
  prefix: T.string,
  facets: T.arrayOf(T.shape({
    id: T.string.isRequired,
    icon: T.string,
    title: T.string.isRequired
  })).isRequired,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  ))
}

ProfileNav.defaultProps = {
  prefix: '',
  actions: []
}

export {
  ProfileNav
}
