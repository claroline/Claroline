import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import isEmpty from 'lodash/isEmpty'

const EditorMenu = (props) => {
  const commonPages = props.pages.filter(page => page.standard)
  const otherPages = props.pages.filter(page => !page.standard && !page.advanced)
  const advancedPages = props.pages.filter(page => page.advanced)

  return (
    <nav className="app-editor-menu" aria-label={props.title}>
      {props.title &&
        <h1 className="app-editor-menu-header">{props.title}</h1>
      }

      <Toolbar
        role="presentation"
        className="nav nav-pills flex-column"
        buttonName="nav-link text-start"
        actions={commonPages.map(page => ({
          name: page.name,
          label: page.title,
          type: LINK_BUTTON,
          target: props.path + '/' + page.name
        }))}
      />

      {!isEmpty(otherPages) &&
        <>
          <hr className="app-editor-menu-separator my-2" />
          <Toolbar
            role="presentation"
            className="nav nav-pills flex-column"
            buttonName="nav-link text-start"
            actions={otherPages.map(page => ({
              name: page.name,
              label: page.title,
              type: LINK_BUTTON,
              target: props.path + '/' + page.name
            }))}
          />
        </>
      }

      {!isEmpty(advancedPages) &&
        <>
          <hr className="app-editor-menu-separator my-2" />
          <Toolbar
            role="presentation"
            className="nav nav-pills flex-column"
            buttonName="nav-link text-start"
            actions={advancedPages.map(page => ({
              name: page.name,
              label: page.title,
              type: LINK_BUTTON,
              target: props.path + '/' + page.name
            }))}
          />
        </>
      }
    </nav>
  )
}

EditorMenu.propTypes = {
  path: T.string.isRequired,
  title: T.string.isRequired,
  pages: T.arrayOf(T.shape({
    name: T.string.isRequired,
    title: T.string.isRequired,
    managerOnly: T.bool,
    standard: T.bool,
    advanced: T.bool
  })),
  actions: T.bool
}

export {
  EditorMenu
}