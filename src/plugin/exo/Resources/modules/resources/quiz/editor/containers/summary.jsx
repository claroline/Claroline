import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as editorSelectors} from '#/main/core/resource/editor'
import {QuizEditorSummary as QuizEditorSummaryComponent} from '#/plugin/exo/resources/quiz/editor/components/summary'
import {selectors} from '#/plugin/exo/resources/quiz/editor/store'

const QuizEditorSummary = connect(
  (state) => ({
    path: editorSelectors.path(state),
    steps: selectors.steps(state),
    numberingType: selectors.numberingType(state),
    questionNumberingType: selectors.questionNumberingType(state),
    errors: formSelectors.errors(formSelectors.form(state, editorSelectors.STORE_NAME))
  })
)(QuizEditorSummaryComponent)

export {
  QuizEditorSummary
}
