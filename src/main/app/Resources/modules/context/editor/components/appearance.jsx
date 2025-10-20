import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {EditorPage} from '#/main/app/editor'

const ContextEditorAppearance = (props) =>
  <EditorPage
    title={trans('appearance')}
    help={trans('Personnalisez les paramètres d\'affichage avancés de votre espace de travail.')}
    definition={[

    ].concat(props.definition)}
  >
    {props.children}
  </EditorPage>

ContextEditorAppearance.propTypes = {
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )),
  children: T.any
}

ContextEditorAppearance.defaultProps = {
  definition: []
}

export {
  ContextEditorAppearance
}
