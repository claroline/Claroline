import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {Routes} from '#/main/app/router/components/routes'
import {Route as RouteTypes, Redirect as RedirectTypes} from '#/main/app/router/prop-types'

import {Button} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'

const ContentNav = (props) =>
  <div className="row" role="presentation">
    <div className="col-md-3" role="presentation">
      <nav
        {...omit(props, 'path', 'redirect', 'sections')}
        className={classes('lateral-nav', props.className)}
      >
        {props.sections
          .filter(tab => undefined === tab.displayed || tab.displayed)
          .map((tab) =>
            <Button
              {...omit(tab, 'title', 'render', 'component')}
              type={LINK_BUTTON}
              label={tab.title}
              target={props.path+tab.path}
              className="lateral-link"
            />
          )
        }
      </nav>
    </div>

    <div className="col-md-9" role="presentation">
      <Routes
        path={props.path}
        redirect={props.redirect}
        routes={props.sections}
      />
    </div>
  </div>

ContentNav.propTypes= {
  className: T.string,
  type: T.oneOf(['vertical', 'horizontal']),
  path: T.string,
  redirect: T.arrayOf(T.shape(
    RedirectTypes.propTypes
  )),
  sections: T.arrayOf(T.shape(merge({}, RouteTypes.propTypes, {
    id: T.string,
    icon: T.string,
    title: T.node.isRequired,
    displayed: T.bool
  }))).isRequired
}


export {
  ContentNav
}
