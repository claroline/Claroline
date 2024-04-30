import React from 'react'
import {PropTypes as T} from 'prop-types'

const EditorPage = (props) =>
  <section className="app-editor-page">
    <h1 className="h4">
      {props.title}
    </h1>

    {props.help &&
      <p className="text-body-secondary">{props.help}</p>
    }

    {props.children}
  </section>

EditorPage.propTypes = {
  title: T.string.isRequired,
  help: T.string,
  children: T.any
}

export {
  EditorPage
}