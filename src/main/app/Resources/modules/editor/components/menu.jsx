import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import isEmpty from 'lodash/isEmpty'

const EditorMenu = (props) =>
  <div className="app-editor-menu" role="presentation">
    {props.title &&
      <h1 className="app-editor-menu-header">{props.title}</h1>
    }

    <Toolbar
      className="nav nav-pills flex-column"
      buttonName="nav-link text-start"
      actions={props.pages.map(page => ({
        name: page.name,
        label: page.title,
        type: LINK_BUTTON,
        target: props.path + '/' + page.name
      }))}
    />

    {!isEmpty(props.actions) &&
      <hr className="app-editor-menu-separator" />
    }

    {!isEmpty(props.actions) &&
      <Toolbar
        className="nav nav-pills flex-column"
        buttonName="nav-link text-start"
        actions={props.actions}
      />
    }
  </div>

EditorMenu.propTypes = {
  path: T.string.isRequired,
  title: T.string.isRequired,
  pages: T.arrayOf(T.shape({
    name: T.string.isRequired,
    title: T.string.isRequired
  })),
  actions: T.array
}

export {
  EditorMenu
}