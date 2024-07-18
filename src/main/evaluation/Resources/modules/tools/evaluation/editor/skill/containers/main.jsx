
import {withReducer} from '#/main/app/store/reducer'

import {EvaluationEditorSkill as EvaluationEditorSkillComponent} from '#/main/evaluation/tools/evaluation/editor/skill/components/main'
import {selectors, reducer} from '#/main/evaluation/tools/evaluation/editor/skill/store'

const EvaluationEditorSkill = withReducer(selectors.LIST_NAME, reducer)(
  EvaluationEditorSkillComponent
)

export {
  EvaluationEditorSkill
}
