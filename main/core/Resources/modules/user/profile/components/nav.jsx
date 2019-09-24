import React from 'react'
import {PropTypes as T} from 'prop-types'

import {NavLink} from '#/main/app/router'
import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action/components/button'

const ProfileNav = props =>
  <nav className="lateral-nav">
    {props.facets.map(facet => {
      let actions = []
      if (props.actions) {
        actions = props.actions(facet)
          .filter(action => !action.displayed || action.displayed(facet))
      }

      return (
        <NavLink
          key={facet.id}
          to={`${props.prefix}/${facet.id}`}
          className="lateral-link"
        >
          {facet.icon &&
            <span className={facet.icon} />
          }

          {facet.title}

          {0 !== actions.length &&
            <div className="lateral-nav-actions">
              {actions
                .map((action) =>
                  <Button
                    {...action}
                    key={`${facet.id}-${toKey(action.label)}`}
                    className="btn btn-link"
                    displayed={true}
                    tooltip="left"
                  />
                )
              }
            </div>
          }
        </NavLink>
      )
    })}
  </nav>

ProfileNav.propTypes = {
  prefix: T.string,
  facets: T.arrayOf(T.shape({
    id: T.string.isRequired,
    icon: T.string,
    title: T.string.isRequired
  })).isRequired,
  actions: T.func // action generator for each facet
}

ProfileNav.defaultProps = {
  prefix: ''
}

export {
  ProfileNav
}
