import React from 'react'
import {PropTypes as T} from 'prop-types'
import {
  HashRouter,
  MemoryRouter
} from 'react-router-dom'

const Router = props => {
  if (!props.embedded) {
    return (
      <HashRouter basename={props.basename}>
        {props.children}
      </HashRouter>
    )
  }

  return (
    <MemoryRouter
      initialEntries={props.basename ? [
        props.basename
      ] : undefined}
      initialIndex={props.basename ? 0 : undefined}
    >
      {props.children}
    </MemoryRouter>
  )
}

Router.propTypes = {
  basename: T.string,
  children: T.node.isRequired,
  embedded: T.bool
}

Router.defaultProps = {
  embedded: false
}

export {
  Router
}
