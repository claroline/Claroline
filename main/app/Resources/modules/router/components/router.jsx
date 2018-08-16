import React from 'react'
import {PropTypes as T} from 'prop-types'
import {
  HashRouter,
  MemoryRouter
} from 'react-router-dom'

const Router = props => !props.embedded ?
  <HashRouter>
    {props.children}
  </HashRouter> :
  <MemoryRouter>
    {props.children}
  </MemoryRouter>

Router.propTypes = {
  children: T.node.isRequired,
  embedded: T.bool
}

Router.defaultProps = {
  embedded: false
}

export {
  Router
}
